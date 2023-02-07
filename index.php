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

const OCAL_VERSION = '1.0beta4';

/**
 * @param string $name
 * @param int $monthCount
 * @return string
 */
function ocal($name, $monthCount = 1)
{
    global $plugin_tx;

    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        return XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
    }
    $controller = new Ocal\DailyCalendarController($name, $monthCount);
    $action = filter_input(
        INPUT_GET,
        'ocal_action',
        FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-z]+$/', 'default' => 'default']]
    );
    if (!method_exists($controller, "{$action}Action")) {
        $action = 'default';
    }
    ob_start();
    $controller->{"{$action}Action"}();
    return ob_get_clean();
}

/**
 * @param string $name
 * @param int $weekCount
 * @return string
 */
function Ocal_hourly($name, $weekCount = 1)
{
    global $plugin_tx;

    if (!preg_match('/^[a-z0-9-]+$/', $name)) {
        return XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
    }
    $controller = new Ocal\HourlyCalendarController($name, $weekCount);
    $action = filter_input(
        INPUT_GET,
        'ocal_action',
        FILTER_VALIDATE_REGEXP,
        ['options' => ['regexp' => '/^[a-z]+$/', 'default' => 'default']]
    );
    if (!method_exists($controller, "{$action}Action")) {
        $action = 'default';
    }
    ob_start();
    $controller->{"{$action}Action"}();
    return ob_get_clean();
}

(new Ocal\Plugin())->dispatch();
