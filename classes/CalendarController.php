<?php

/**
 * Copyright 2014-2017 Christoph M. Becker
 *
 * This file is part of Ocal_XH.
 *
 * Ocal_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ocal_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ocal_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ocal;

use DateTime;
use XH\CSRFProtection as CsrfProtector;

abstract class CalendarController
{
    /** @var bool */
    protected static $isJavaScriptEmitted = false;

    /** @var string */
    protected $name;

    /** @var int */
    protected $count;

    /** @var CsrfProtector */
    protected $csrfProtector;

    /** @var string[] */
    protected $config;

    /** @var string[] */
    protected $lang;

    /** @var string */
    protected $mode;

    /** @var string */
    private $scriptName;

    /** @var string */
    protected $pluginFolder;

    /** @var DateTime */
    protected $now;

    /** @var ListService */
    protected $listService;

    /** @var Db */
    protected $db;

    /** @var bool */
    protected $isAdmin;

    /**
     * @param array<string,string> $config
     * @param array<string,string> $lang
     */
    public function __construct(
        string $scriptName,
        string $pluginFolder,
        CsrfProtector $csrfProtector,
        array $config,
        array $lang,
        DateTime $now,
        ListService $listService,
        Db $db,
        bool $isAdmin,
        string $name,
        int $count
    ) {
        $this->name = (string) $name;
        $this->count = (int) $count;
        $this->scriptName = $scriptName;
        $this->pluginFolder = $pluginFolder;
        $this->csrfProtector = $csrfProtector;
        $this->config = $config;
        $this->lang = $lang;
        $this->now = $now;
        $this->listService = $listService;
        $this->db = $db;
        $this->isAdmin = $isAdmin;
    }

    public function defaultAction(): Response
    {
        $this->mode = 'calendar';
        $occupancy = $this->findOccupancy();
        $html = $this->renderCalendarView($occupancy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                return new Response($html, "text/html");
            }
            return new Response("");
        } else {
            return new Response($html);
        }
    }

    public function listAction(): Response
    {
        $this->mode = 'list';
        $occupancy = $this->findOccupancy();
        $html = $this->renderListView($occupancy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                return new Response($html, "text/html");
            }
            return new Response("");
        } else {
            return new Response($html);
        }
    }

    abstract protected function findOccupancy(): Occupancy;

    abstract protected function renderCalendarView(Occupancy $occupancy): HtmlString;

    abstract protected function renderListView(Occupancy $occupancy): HtmlString;

    public function saveAction(): Response
    {
        $this->mode = 'calendar';
        if ($this->isAdmin && isset($_GET['ocal_name']) && $_GET['ocal_name'] == $this->name) {
            $this->csrfProtector->check();
            return new Response($this->saveStates(), "text/html");
        }
        return new Response("");
    }

    /** @return string|never */
    abstract protected function saveStates();

    /** @return void */
    protected function emitScriptElements()
    {
        global $bjs;

        if (self::$isJavaScriptEmitted) {
            return;
        }
        $config = array(
            'message_unsaved_changes' => $this->lang['message_unsaved_changes'],
            'isAdmin' => $this->isAdmin
        );
        $bjs .= '<script type="text/javascript">/* <![CDATA[ */'
            . 'var OCAL = ' . json_encode($config) . ';'
            . '/* ]]> */</script>'
            . '<script type="text/javascript" src="'
            . $this->pluginFolder . 'ocal.js"></script>';
        self::$isJavaScriptEmitted = true;
    }

    protected function renderModeLinkView(): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('mode-link', [
            'mode' => $mode = $this->mode == 'calendar' ? 'list' : 'calendar',
            'url' => $this->modifyUrl(array('ocal_action' => $mode)),
        ]));
    }

    protected function renderStatusbarView(): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('statusbar', [
            'image' => "{$this->pluginFolder}images/ajax-loader-bar.gif",
        ]));
    }

    protected function renderToolbarView(): HtmlString
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang);
        return new HtmlString($view->render('toolbar', [
            'states' => range(0, $this->config['state_max']),
        ]));
    }

    /** @param array<string,string> $newParams */
    protected function modifyUrl(array $newParams): string
    {
        parse_str($_SERVER['QUERY_STRING'], $params);
        $params = array_merge($params, $newParams);
        $query = str_replace('=&', '&', http_build_query($params));
        return "{$this->scriptName}?$query";
    }
}
