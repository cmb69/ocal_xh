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

class Month
{
    /**
     * @var int
     */
    protected $month;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @param int $year
     * @param int $month
     * @param int count
     * @return Month[]
     */
    public static function createRange($year, $month, $count)
    {
        $months = [];
        $month = new Month($month, $year);
        while ($count) {
            $months[] = $month;
            $month = $month->getNextMonth();
            $count--;
        }
        return $months;
    }

    /**
     * @param int $month
     * @param int $year
     */
    public function __construct($month, $year)
    {
        $this->month = (int) $month;
        $this->year = (int) $year;
        $this->timestamp = mktime(0, 0, 0, $month, 1, $year);
    }

    /**
     * @return int
     */
    public function getLastDay()
    {
        return (int) date('t', $this->timestamp);
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }

    /**
     * @return Month
     */
    public function getNextMonth()
    {
        $month = $this->month + 1;
        $year = $this->year;
        if ($month > 12) {
            $month = 1;
            $year += 1;
        }
        return new Month($month, $year);
    }

    /**
     * @return ?int[][]
     */
    public function getDaysOfWeeks()
    {
        $mondays = range($this->getDayOffset(), $this->getLastDay(), 7);
        $getWeekDays = function ($monday) {
            $lastDay = $this->getLastDay();
            $result = array();
            for ($day = $monday; $day < $monday + 7; $day++) {
                $result[] = $day >= 1 && $day <= $lastDay ? $day : null;
            }
            return $result;
        };
        return array_map($getWeekDays, $mondays);
    }

    /**
     * @return int
     */
    private function getDayOffset()
    {
        $weekday = date('w', $this->timestamp);
        return $weekday ? 2 - $weekday : 2 - 7;
    }
}
