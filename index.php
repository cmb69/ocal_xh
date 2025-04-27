<?php

/**
 * Copyright (c) Christoph M. Becker
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

use Ocal\Dic;
use Plib\Request;

const OCAL_VERSION = "1.4";

function ocal(string $name, int $monthCount = 1): string
{
    return Dic::makeDailyCalendarController()(Request::current(), $name, $monthCount)();
}

function ocal_hourly(string $name, int $weekCount = 1): string
{
    return Dic::makeHourlyCalendarController()(Request::current(), $name, $weekCount)();
}

if (isset($_GET['ocal_week']) || isset($_GET['ocal_month']) || isset($_GET['ocal_year'])) {
    XH_afterPluginLoading(function () {
        global $cf;

        $cf['meta']['robots'] = 'noindex, nofollow';
    });
}
