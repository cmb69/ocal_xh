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

abstract class WeekView
{
    /**
     * @var Week
     */
    protected $week;

    /**
     * @var Occupancy
     */
    protected $occupancy;

    public function __construct(Week $week, Occupancy $occupancy)
    {
        $this->week = $week;
        $this->occupancy = $occupancy;
    }

    /**
     * @param int $day
     * @param int $hour
     * @return string
     */
    protected function formatDate($day, $hour)
    {
        return sprintf('%04d-%02d-%02d-%02d', $this->week->getYear(), $this->week->getWeek(), $day, $hour);
    }
}
