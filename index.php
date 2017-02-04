<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
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

define('OCAL_VERSION', '@OCAL_VERSION@');

/**
 * @param string $name
 * @param int $monthCount
 * @return string
 */
function ocal($name, $monthCount = 1)
{
    global $_Ocal_controller;

    return $_Ocal_controller->renderCalendar($name, $monthCount);
}

/**
 * @param string $name
 * @param int $weekCount
 * @return string
 */
function Ocal_hourly($name, $weekCount = 1)
{
    global $_Ocal_controller;

    return $_Ocal_controller->renderWeekCalendar($name, $weekCount);
}

$_Ocal_controller = new Ocal\Controller();
$_Ocal_controller->dispatch();
