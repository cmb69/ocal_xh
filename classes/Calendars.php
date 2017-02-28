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

class Calendars extends Controller
{
    public function __construct(Occupancy $occupancy)
    {
        parent::__construct($occupancy);
        $this->mode = 'calendar';
    }

    /**
     * @param int $monthCount
     * @return string
     */
    public function render($monthCount)
    {
        global $_XH_csrfProtection;

        $this->emitScriptElements();
        $html = '<div class="ocal_calendars" data-name="'
            . $this->occupancy->getName() . '">'
            . $this->renderModeLink();
        if (XH_ADM) {
            $html .= $_XH_csrfProtection->tokenInput()
                . $this->renderToolbar();
        }
        $html .= $this->renderStatusbar();
        $month = new Month($this->month, $this->year);
        while ($monthCount) {
            $calendar = new MonthCalendar($month, $this->occupancy);
            $html .= $calendar->render();
            $monthCount--;
            $month = $month->getNextMonth();
        }
        $html .= $this->renderMonthPagination()
            . '</div>';
        return $html;
    }

    /**
     * @return string
     */
    protected function renderToolbar()
    {
        $view = new View('toolbar');
        $view->states = range(0, 3);
        return $view->render();
    }
}
