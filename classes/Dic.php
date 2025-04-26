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

namespace Ocal;

use Plib\DocumentStore;
use Plib\SystemChecker;
use Plib\View;

class Dic
{
    public static function makeDefaultAdminController(): DefaultAdminController
    {
        global $pth;

        return new DefaultAdminController(
            "{$pth['folder']['plugins']}ocal/",
            "{$pth['folder']['base']}content/ocal/",
            new SystemChecker(),
            self::view()
        );
    }

    public static function makeDailyCalendarController(): DailyCalendarController
    {
        global $pth, $plugin_cf, $plugin_tx, $_XH_csrfProtection;

        return new DailyCalendarController(
            "{$pth['folder']['plugins']}ocal/",
            $_XH_csrfProtection,
            $plugin_cf['ocal'],
            new ListService($plugin_cf['ocal'], $plugin_tx['ocal']),
            new DocumentStore($pth["folder"]["base"] . "content/ocal/"),
            self::view()
        );
    }

    public static function makeHourlyCalendarController(): HourlyCalendarController
    {
        global $pth, $plugin_cf, $plugin_tx, $_XH_csrfProtection;

        return new HourlyCalendarController(
            "{$pth['folder']['plugins']}ocal/",
            $_XH_csrfProtection,
            $plugin_cf['ocal'],
            new ListService($plugin_cf['ocal'], $plugin_tx['ocal']),
            new DocumentStore($pth["folder"]["base"] . "content/ocal/"),
            self::view()
        );
    }

    private static function view(): View
    {
        global $pth, $plugin_tx;

        return new View($pth["folder"]["plugins"] . "ocal/views/", $plugin_tx["ocal"]);
    }
}
