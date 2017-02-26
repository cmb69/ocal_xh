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

class WeekList extends WeekView
{
    /**
     * @return string
     */
    public function render()
    {
        global $plugin_tx;

        $html = '';
        $date = new DateTime();
        for ($i = 1; $i <= 7; $i++) {
            $date->setISODate($this->week->getYear(), $this->week->getWeek(), $i);
            $dayLabel = $date->format($plugin_tx['ocal']['date_format']);
            $outerDl = '<dt>' . $dayLabel . '</dt><dd><dl>';
            $innerDl = '';
            foreach ($this->getListOfDay($i) as $range => $state) {
                $label = $plugin_tx['ocal']['label_state_' . $state];
                if ($label != '') {
                    $innerDl .= '<dt>' . $range . '</dt>'
                        . '<dd>' . $label . '</dd>';
                }
            }
            if ($innerDl) {
                $html .= $outerDl . $innerDl . '</dl></dd>';
            }
        }
        return $html;
    }

    /**
     * @param int $weekday
     * @return array
     */
    protected function getListOfDay($weekday)
    {
        global $plugin_cf;

        $pcf = $plugin_cf['ocal'];
        $list = array();
        $currentRange = array();
        $currentState = -1;
        for ($hour = $pcf['hour_first']; $hour <= $pcf['hour_last']; $hour += $pcf['hour_interval']) {
            $date = $this->formatDate($weekday, $hour);
            $state = $this->occupancy->getState($date);
            if ($currentState == -1 || $state == $currentState) {
                $currentRange[] = $hour;
            } else {
                $list[$this->formatRange($currentRange)] = $currentState;
                $currentRange = array($hour);
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
        global $plugin_cf;

        $start = $range[0];
        if (count($range) > 1) {
            $end = $range[count($range) - 1];
        } else {
            $end = $range[0];
        }
        $end += $plugin_cf['ocal']['hour_interval'] - 1;
        return sprintf('%02d:00&ndash;%02d:59', $start, $end);
    }
}
