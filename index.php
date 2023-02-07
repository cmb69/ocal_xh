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

use Ocal\Db;
use Ocal\ListService;

const OCAL_VERSION = '1.0beta4';

/**
 * @param string $name
 * @param int $monthCount
 * @return string
 */
function ocal($name, $monthCount = 1)
{
    global $sn, $pth, $plugin_cf, $plugin_tx, $_XH_csrfProtection;

    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        return XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
    }
    $controller = new Ocal\DailyCalendarController(
        $sn,
        "{$pth['folder']['plugins']}ocal/",
        $_XH_csrfProtection,
        $plugin_cf['ocal'],
        $plugin_tx['ocal'],
        new DateTime(),
        new ListService(),
        new Db(),
        $name,
        $monthCount
    );
    $action = filter_input(
        INPUT_GET,
        'ocal_action',
        FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-z]+$/', 'default' => 'default']]
    );
    if (!method_exists($controller, "{$action}Action")) {
        $action = 'default';
    }
    return $controller->{"{$action}Action"}()->trigger();
}

/**
 * @param string $name
 * @param int $weekCount
 * @return string
 */
function Ocal_hourly($name, $weekCount = 1)
{
    global $sn, $pth, $plugin_cf, $plugin_tx, $_XH_csrfProtection;

    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        return XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
    }
    $controller = new Ocal\HourlyCalendarController(
        $sn,
        "{$pth['folder']['plugins']}ocal/",
        $_XH_csrfProtection,
        $plugin_cf['ocal'],
        $plugin_tx['ocal'],
        new DateTime(),
        new ListService(),
        new Db(),
        $name,
        $weekCount
    );
    $action = filter_input(
        INPUT_GET,
        'ocal_action',
        FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-z]+$/', 'default' => 'default']]
    );
    if (!method_exists($controller, "{$action}Action")) {
        $action = 'default';
    }
    return $controller->{"{$action}Action"}()->trigger();
}

if (isset($_GET['ocal_week']) || isset($_GET['ocal_month']) || isset($_GET['ocal_year'])) {
    XH_afterPluginLoading(function () {
        global $cf;

        $cf['meta']['robots'] = 'noindex, nofollow';
    });
}
