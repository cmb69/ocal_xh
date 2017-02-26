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

class MonthList extends MonthView
{
    /**
     * @return string
     */
    public function render()
    {
        global $plugin_tx;

        $html = $this->renderHeading() . '<dd><dl>';
        foreach ($this->getList() as $range => $state) {
            $label = $plugin_tx['ocal']['label_state_' . $state];
            if ($label != '') {
                $html .= '<dt>' . $range . '</dt>'
                    . '<dd>' . $label . '</dd>';
            }
        }
        $html .= '</dl></dd>';
        return $html;
    }

    /**
     * @return string
     */
    protected function renderHeading()
    {
        global $plugin_tx;

        $monthnames = explode(',', $plugin_tx['ocal']['date_months']);
        return  '<dt>' . $monthnames[$this->month->getMonth() - 1]
            . ' ' . $this->month->getYear() . '</dt>';
    }

    /**
     * @return array
     */
    protected function getList()
    {
        $list = array();
        $currentRange = array();
        $currentState = -1;
        for ($day = 1; $day <= $this->month->getLastDay(); $day++) {
            $date = $this->formatDate($day);
            $state = $this->occupancy->getState($date);
            if ($currentState == -1 || $state == $currentState) {
                $currentRange[] = $day;
            } else {
                $list[$this->formatRange($currentRange)] = $currentState;
                $currentRange = array($day);
            }
            $currentState = $state;
        }
        $list[$this->formatRange($currentRange)] = $currentState;
        return $list;
    }

    /**
     * @return string
     */
    protected function formatRange(array $range)
    {
        $string = $range[0] . '.';
        if (count($range) > 1) {
            $string .= '&ndash;' . $range[count($range) - 1] . '.';
        }
        return $string;
    }
}
