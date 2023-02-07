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
    /**
     * @var bool
     */
    protected static $isJavaScriptEmitted = false;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $count;

    /**
     * @var CsrfProtector
     */
    protected $csrfProtector;

    /**
     * @var string[]
     */
    protected $config;

    /**
     * @var string[]
     */
    protected $lang;

    /**
     * @var string
     */
    protected $mode;

    /**
     * @var string
     */
    private $scriptName;

    /**
     * @var string
     */
    protected $pluginFolder;

    /** @var DateTime */
    protected $now;

    /** @var ListService */
    protected $listService;

    /**
     * @param array<string,string> $config
     * @param array<string,string> $lang
     * @param string $name
     * @param int $count
     */
    public function __construct(
        string $scriptName,
        string $pluginFolder,
        CsrfProtector $csrfProtector,
        array $config,
        array $lang,
        DateTime $now,
        ListService $listService,
        $name,
        $count
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
    }

    public function defaultAction(): Response
    {
        $this->mode = 'calendar';
        $occupancy = $this->findOccupancy();
        $view = $this->getCalendarView($occupancy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                return new Response($view->render(), "text/html");
            }
            return new Response("");
        } else {
            return new Response($view->render());
        }
    }

    public function listAction(): Response
    {
        $this->mode = 'list';
        $occupancy = $this->findOccupancy();
        $view = $this->getListView($occupancy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                return new Response($view->render(), "text/html");
            }
            return new Response("");
        } else {
            return new Response($view->render());
        }
    }

    /**
     * @return Occupancy
     */
    abstract protected function findOccupancy();

    /**
     * @return View
     */
    abstract protected function getCalendarView(Occupancy $occupancy);

    /**
     * @return View
     */
    abstract protected function getListView(Occupancy $occupancy);

    public function saveAction(): Response
    {
        $this->mode = 'calendar';
        if (defined('XH_ADM') && XH_ADM && isset($_GET['ocal_name']) && $_GET['ocal_name'] == $this->name) {
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
            'isAdmin' => defined('XH_ADM') && XH_ADM
        );
        $bjs .= '<script type="text/javascript">/* <![CDATA[ */'
            . 'var OCAL = ' . json_encode($config) . ';'
            . '/* ]]> */</script>'
            . '<script type="text/javascript" src="'
            . $this->pluginFolder . 'ocal.js"></script>';
        self::$isJavaScriptEmitted = true;
    }

    /**
     * @return View
     */
    protected function prepareModeLinkView()
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'mode-link');
        $view->setData([
            'mode' => $mode = $this->mode == 'calendar' ? 'list' : 'calendar',
            'url' => $this->modifyUrl(array('ocal_action' => $mode)),
        ]);
        return $view;
    }

    /**
     * @return View
     */
    protected function prepareStatusbarView()
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'statusbar');
        $view->setData([
            'image' => "{$this->pluginFolder}images/ajax-loader-bar.gif",
        ]);
        return $view;
    }

    /**
     * @return View
     */
    protected function prepareToolbarView()
    {
        $view = new View("{$this->pluginFolder}views/", $this->lang, 'toolbar');
        $view->setData([
            'states' => range(0, $this->config['state_max']),
        ]);
        return $view;
    }

    /**
     * @param array<string,string> $newParams
     * @return string
     */
    protected function modifyUrl(array $newParams)
    {
        parse_str($_SERVER['QUERY_STRING'], $params);
        $params = array_merge($params, $newParams);
        $query = str_replace('=&', '&', http_build_query($params));
        return "{$this->scriptName}?$query";
    }
}
