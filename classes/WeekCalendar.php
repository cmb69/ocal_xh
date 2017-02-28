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

class WeekCalendar extends WeekView
{
    /**
     * @return string
     */
    public function render()
    {
        global $plugin_cf, $plugin_tx;

        $pcf = $plugin_cf['ocal'];
        $view = new View('hourly-calendar');
        $view->date = $this->week->getIso();
        $date = new DateTime();
        $date->setISODate($this->week->getYear(), $this->week->getWeek(), 1);
        $view->from = $date->format($plugin_tx['ocal']['date_format']);
        $date->setISODate($this->week->getYear(), $this->week->getWeek(), 7);
        $view->to = $date->format($plugin_tx['ocal']['date_format']);
        $view->daynames = array_map('trim', explode(',', $plugin_tx['ocal']['date_days']));
        $view->hours = range($pcf['hour_first'], $pcf['hour_last'], $pcf['hour_interval']);
        $view->days = range(1, 7);
        $view->state = function ($day, $hour) {
            return $this->occupancy->getState($this->formatDate($day, $hour));
        };
        return $view->render();
    }
}
