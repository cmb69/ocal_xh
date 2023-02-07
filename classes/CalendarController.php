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

abstract class CalendarController implements Controller
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
     * @var object
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
    private $pluginFolder;

    /**
     * @param string $name
     * @param int $count
     */
    public function __construct($name, $count)
    {
        global $sn, $pth, $_XH_csrfProtection, $plugin_cf, $plugin_tx;

        $this->name = (string) $name;
        $this->count = (int) $count;
        $this->scriptName = $sn;
        $this->pluginFolder = $pth['folder']['plugins'];
        $this->csrfProtector = $_XH_csrfProtection;
        $this->config = $plugin_cf['ocal'];
        $this->lang = $plugin_tx['ocal'];
    }

    public function defaultAction()
    {
        $this->mode = 'calendar';
        $occupancy = $this->findOccupancy();
        $view = $this->getCalendarView($occupancy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                header('Content-Type: text/html; charset=UTF-8');
                $this->purgeOutputBuffers();
                $view->render();
                exit;
            }
        } else {
            $view->render();
        }
    }

    public function listAction()
    {
        $this->mode = 'list';
        $occupancy = $this->findOccupancy();
        $view = $this->getListView($occupancy);
        if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest') {
            if ($_GET['ocal_name'] == $this->name) {
                header('Content-Type: text/html; charset=UTF-8');
                $this->purgeOutputBuffers();
                $view->render();
                exit;
            }
        } else {
            $view->render();
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

    public function saveAction()
    {
        $this->mode = 'calendar';
        if (defined('XH_ADM') && XH_ADM && isset($_GET['ocal_name']) && $_GET['ocal_name'] == $this->name) {
            $this->csrfProtector->check();
            $this->purgeOutputBuffers();
            echo $this->saveStates();
            exit;
        }
    }

    private function purgeOutputBuffers()
    {
        while (ob_get_level()) {
            ob_end_clean();
        }
    }

    abstract protected function saveStates();

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
            . $this->pluginFolder . 'ocal/ocal.js"></script>';
        self::$isJavaScriptEmitted = true;
    }

    /**
     * @return View
     */
    protected function prepareModeLinkView()
    {
        $view = new View('mode-link');
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
        $view = new View('statusbar');
        $view->setData([
            'image' => "{$this->pluginFolder}ocal/images/ajax-loader-bar.gif",
        ]);
        return $view;
    }

    /**
     * @return View
     */
    protected function prepareToolbarView()
    {
        $view = new View('toolbar');
        $view->setData([
            'states' => range(0, $this->config['state_max']),
        ]);
        return $view;
    }

    /**
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
