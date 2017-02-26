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

abstract class MonthView
{
    /**
     * @var Month
     */
    protected $month;

    /**
     * @var Occupancy
     */
    protected $occupancy;

    public function __construct(Month $month, Occupancy $occupancy)
    {
        $this->month = $month;
        $this->occupancy = $occupancy;
    }

    /**
     * @param int $day
     * @return string
     */
    protected function formatDate($day)
    {
        return sprintf('%04d-%02d-%02d', $this->month->getYear(), $this->month->getMonth(), $day);
    }
}
