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

class ListService
{
    /**
     * @var array<string,string>
     */
    private $config;

    /**
     * @var array<string,string>
     */
    private $lang;

    public function __construct()
    {
        global $plugin_cf, $plugin_tx;

        $this->config = $plugin_cf['ocal'];
        $this->lang = $plugin_tx['ocal'];
    }

    /**
     * @return object[]
     */
    public function getDailyList(Occupancy $occupancy, Month $month)
    {
        $list = array();
        $currentRange = array();
        $currentState = -1;
        for ($day = 1; $day <= $month->getLastDay(); $day++) {
            $state = $occupancy->getDailyState($month->getYear(), $month->getMonth(), $day);
            if ($currentState == -1 || $state == $currentState) {
                $currentRange[] = $day;
            } else {
                $list[$this->formatDailyRange($currentRange)] = $currentState;
                $currentRange = array($day);
            }
            $currentState = $state;
        }
        $list[$this->formatDailyRange($currentRange)] = $currentState;
        return $this->mapFilteredList($list);
    }

    /**
     * @param list<int> $range
     * @return string
     */
    private function formatDailyRange(array $range)
    {
        $string = $range[0] . '.';
        if (count($range) > 1) {
            $string .= '–' . $range[count($range) - 1] . '.';
        }
        return $string;
    }

    /**
     * @return object[]
     */
    public function getHourlyList(Occupancy $occupancy, Week $week)
    {
        $result = [];
        foreach ($week->getDatesOfWeek() as $weekday => $date) {
            $list = $this->getHourlyListForDay($occupancy, $week, $weekday);
            if (!empty($list)) {
                $result[] = (object) ['date' => $date, 'list' => $list];
            }
        }
        return $result;
    }

    /**
     * @param int $weekday
     * @return object[]
     */
    private function getHourlyListForDay(Occupancy $occupancy, Week $week, $weekday)
    {
        $list = array();
        $currentRange = array();
        $currentState = -1;
        $hours = range(
            (int) $this->config['hour_first'],
            (int) $this->config['hour_last'],
            (int) $this->config['hour_interval']
        );
        foreach ($hours as $hour) {
            $state = $occupancy->getHourlyState($week->getYear(), $week->getWeek(), $weekday, $hour);
            if ($currentState == -1 || $state == $currentState) {
                $currentRange[] = $hour;
            } else {
                $list[$this->formatHourlyRange($currentRange)] = $currentState;
                $currentRange = array($hour);
            }
            $currentState = $state;
        }
        $list[$this->formatHourlyRange($currentRange)] = $currentState;
        return $this->mapFilteredList($list);
    }

    /**
     * @param list<int> $range
     * @return string
     */
    private function formatHourlyRange(array $range)
    {
        $start = $range[0];
        if (count($range) > 1) {
            $end = $range[count($range) - 1];
        } else {
            $end = $range[0];
        }
        $end += (int) $this->config['hour_interval'] - 1;
        return sprintf('%02d:00–%02d:59', $start, $end);
    }

    /**
     * @param array<string,int> $list
     * @return object[]
     */
    private function mapFilteredList(array $list)
    {
        $result = [];
        foreach ($list as $range => $state) {
            if ($state) {
                $label = $this->lang["label_state_$state"];
                $result[] = (object) compact('range', 'state', 'label');
            }
        }
        return $result;
    }
}
