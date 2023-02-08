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

use DateTimeImmutable;
use XH\CSRFProtection as CsrfProtector;

abstract class CalendarController
{
    /** @var bool */
    protected static $isJavaScriptEmitted = false;

    /** @var CsrfProtector */
    protected $csrfProtector;

    /** @var array<string,string> */
    protected $config;

    /** @var array<string,string> */
    protected $lang;

    /** @var string */
    protected $mode;

    /** @var string */
    private $scriptName;

    /** @var string */
    protected $pluginFolder;

    /** @var DateTimeImmutable */
    protected $now;

    /** @var ListService */
    protected $listService;

    /** @var Db */
    protected $db;

    /** @var bool */
    protected $isAdmin;

    /** @var View */
    protected $view;

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
        DateTimeImmutable $now,
        ListService $listService,
        Db $db,
        bool $isAdmin
    ) {
        $this->scriptName = $scriptName;
        $this->pluginFolder = $pluginFolder;
        $this->csrfProtector = $csrfProtector;
        $this->config = $config;
        $this->lang = $lang;
        $this->now = $now;
        $this->listService = $listService;
        $this->db = $db;
        $this->isAdmin = $isAdmin;
        $this->view = new View("{$this->pluginFolder}views/", $this->lang);
    }

    public function defaultAction(string $name, int $count): Response
    {
        $this->mode = 'calendar';
        $occupancy = $this->findOccupancy($name);
        $html = $this->renderCalendarView($occupancy, $count);
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) !== 'XMLHttpRequest') {
            return new Response($html);
        }
        if (($_GET['ocal_name'] ?? null) === $name) {
            return new Response($html, "text/html");
        }
        return new Response("");
    }

    public function listAction(string $name, int $count): Response
    {
        $this->mode = 'list';
        $occupancy = $this->findOccupancy($name);
        $html = $this->renderListView($occupancy, $count);
        if (($_SERVER['HTTP_X_REQUESTED_WITH'] ?? null) !== 'XMLHttpRequest') {
            return new Response($html);
        }
        if (($_GET['ocal_name'] ?? null) === $name) {
            return new Response($html, "text/html");
        }
        return new Response("");
    }

    abstract protected function findOccupancy(string $name): Occupancy;

    abstract protected function renderCalendarView(Occupancy $occupancy, int $count): HtmlString;

    abstract protected function renderListView(Occupancy $occupancy, int $count): HtmlString;

    public function saveAction(string $name, int $count): Response
    {
        $this->mode = 'calendar';
        if ($this->isAdmin && isset($_GET['ocal_name']) && $_GET['ocal_name'] === $name) {
            $this->csrfProtector->check();
            return new Response($this->saveStates($name), "text/html");
        }
        return new Response("");
    }

    /** @return string|never */
    abstract protected function saveStates(string $name);

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
        $bjs .= '<script>'
            . 'var OCAL = ' . json_encode($config) . ';'
            . '</script>'
            . '<script src="'
            . $this->pluginFolder . 'ocal.min.js"></script>';
        self::$isJavaScriptEmitted = true;
    }

    protected function renderModeLinkView(): HtmlString
    {
        return new HtmlString($this->view->render('mode-link', [
            'mode' => $mode = $this->mode === 'calendar' ? 'list' : 'calendar',
            'url' => $this->modifyUrl(array('ocal_action' => $mode)),
        ]));
    }

    protected function renderStatusbarView(): HtmlString
    {
        return new HtmlString($this->view->render('statusbar', [
            'image' => "{$this->pluginFolder}images/ajax-loader-bar.gif",
        ]));
    }

    protected function renderToolbarView(): HtmlString
    {
        return new HtmlString($this->view->render('toolbar', [
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
