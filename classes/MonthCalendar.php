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

class MonthCalendar extends MonthView
{
    /**
     * @return string
     */
    public function render()
    {
        global $plugin_tx;

        $view = new View('month-calendar');
        $view->isoDate = $this->month->getIso();
        $view->year = $this->month->getYear();
        $monthnames = array_map('trim', explode(',', $plugin_tx['ocal']['date_months']));
        $view->monthname = $monthnames[$this->month->getMonth() - 1];
        $view->daynames = array_map('trim', explode(',', $plugin_tx['ocal']['date_days']));
        $view->weeks = $this->month->getDaysOfWeeks();
        $view->state = function ($day) {
            return $this->occupancy->getState($this->formatDate($day));
        };
        $view->todayClass = function ($day) {
            return $this->formatDate($day) === date('Y-m-d') ? ' ocal_today' : '';
        };
        $view->titleKey = function ($day) {
            $state = $this->occupancy->getState($this->formatDate($day));
            return "label_state_$state";
        };
        return $view->render();
    }
}
