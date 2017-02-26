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
     * @param int $offset
     * @return Week
     */
    public function getNextWeek($offset = 1)
    {
        $date = new DateTime();
        $date->setISODate($this->year, $this->week);
        $date->modify(sprintf('+%-d week', $offset));
        $week = $date->format('W');
        $year = $date->format('o');
        return new self($week, $year);
    }
}
