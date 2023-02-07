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

class Week
{
    /**
     * @var int
     */
    protected $week;

    /**
     * @var int
     */
    protected $year;

    /**
     * @param int $year
     * @param int $startWeek
     * @param int $count
     * @return Week[]
     */
    public static function createRange($year, $startWeek, $count)
    {
        $weeks = [];
        $week = new Week($startWeek, $year);
        while ($count) {
            $weeks[] = $week;
            $week = $week->getNextWeek();
            $count--;
        }
        return $weeks;
    }

    /**
     * @param int $week
     * @param int $year
     */
    public function __construct($week, $year)
    {
        $this->week = (int) $week;
        $this->year = (int) $year;
    }

    /**
     * @return int
     */
    public function getWeek()
    {
        return $this->week;
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
        return sprintf('%04d-%02d', $this->year, $this->week);
    }

    /**
     * @return DateTime[]
     */
    public function getDatesOfWeek()
    {
        $dates = [];
        for ($i = 1; $i <= 7; $i++) {
            $date = new DateTime();
            $date->setISODate($this->year, $this->week, $i);
            $date->setTime(0, 0, 0);
            $dates[$i] = $date;
        }
        return $dates;
    }

    /**
     * @param int $offset
     * @return Week
     */
    public function getNextWeek($offset = 1)
    {
        $date = new DateTime();
        $date->setISODate($this->year, $this->week);
        $date->modify(sprintf('+%-d week', $offset));
        $week = (int) $date->format('W');
        $year = (int) $date->format('o');
        return new self($week, $year);
    }

    /**
     * @return int
     */
    public function compare(Week $other)
    {
        if ($this->year < $other->year || $this->year === $other->year && $this->week < $other->week) {
            return -1;
        } elseif ($this->year > $other->year || $this->year === $other->year && $this->week > $other->week) {
            return 1;
        } else {
            return 0;
        }
    }
}
