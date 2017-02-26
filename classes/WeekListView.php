<?php

/**
 * Copyright 2017 Christoph M. Becker
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

class WeekListView extends ListView
{
    /**
     * @param int $weekCount
     * @return string
     */
    public function render($weekCount)
    {
        $this->emitScriptElements();
        $html = '<div data-name="' . $this->occupancy->getName() . '">' . $this->renderModeLink()
            . $this->renderLoaderbar() . $this->renderStatusbar()
            . '<dl class="ocal_list">';
        $week = new Week($this->week, $this->year);
        $i = $weekCount;
        while ($i) {
            $calendar = new WeekList($week, $this->occupancy);
            $html .= $calendar->render();
            $i--;
            $week = $week->getNextWeek();
        }
        $html .= '</dl>'
            . $this->renderWeekPagination() . '</div>';
        return $html;
    }
}
