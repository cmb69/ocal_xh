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

/*
 * Prevent direct access and usage from unsupported CMSimple_XH versions.
 */
if (!defined('CMSIMPLE_XH_VERSION')
    || strpos(CMSIMPLE_XH_VERSION, 'CMSimple_XH') !== 0
    || version_compare(CMSIMPLE_XH_VERSION, 'CMSimple_XH 1.6', 'lt')
) {
    header('HTTP/1.1 403 Forbidden');
    header('Content-Type: text/plain; charset=UTF-8');
    die(
        <<<EOT
Ocal_XH detected an unsupported CMSimple_XH version.
Uninstall Ocal_XH or upgrade to a supported CMSimple_XH version!
EOT
    );
}

class_alias('Ocal\Occupancy', 'Ocal_Occupancy');
class_alias('Ocal\HourlyOccupancy', 'Ocal_HourlyOccupancy');

const OCAL_VERSION = '@OCAL_VERSION@';

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
