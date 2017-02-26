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

class WeekCalendars extends Calendars
{
    /**
     * @param int $weekCount
     * @return string
     */
    public function render($weekCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_week_calendars" data-name="'
            . $this->occupancy->getName() . '">'
            . $this->renderModeLink();
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar();
        }
        $html .= $this->renderLoaderbar() . $this->renderStatusbar();
        $week = new Week($this->week, $this->year);
        $i = $weekCount;
        while ($i) {
            $calendar = new WeekCalendar($week, $this->occupancy);
            $html .= $calendar->render();
            $i--;
            $week = $week->getNextWeek();
        }
        $html .= $this->renderWeekPagination($weekCount) . '</div>';
        return $html;
    }
}
