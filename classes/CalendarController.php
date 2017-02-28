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

abstract class CalendarController
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var int
     */
    protected $count;

    /**
     * @param string $name
     * @param int $count
     */
    public function __construct($name, $count)
    {
        $this->name = (string) $name;
        $this->count = (int) $count;
    }

    /**
     * @return bool
     */
    protected function validateName()
    {
        global $plugin_tx;

        if (preg_match('/^[a-z0-9-]+$/', $this->name)) {
            return true;
        } else {
            echo XH_message('fail', $plugin_tx['ocal']['error_occupancy_name']);
            return false;
        }
    }

    /**
     * @return View
     */
    protected function getView(Occupancy $occupancy)
    {
        $mode = isset($_GET['ocal_mode']) ? $_GET['ocal_mode'] : 'calendar';
        switch ($mode) {
            case 'list':
                if ($occupancy instanceof HourlyOccupancy) {
                    return new WeekListView($occupancy);
                } else {
                    return new ListView($occupancy);
                }
                break;
            default:
                if ($occupancy instanceof HourlyOccupancy) {
                    return new WeekCalendars($occupancy);
                } else {
                    return new Calendars($occupancy);
                }
        }
    }
}
