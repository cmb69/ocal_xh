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

use LogicException;

class DailyOccupancy extends Occupancy
{
    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int
     */
    public function getDailyState($year, $month, $day)
    {
        $date = sprintf('%04d-%02d-%02d', $year, $month, $day);
        return $this->getState($date);
    }

    /**
     * @param int $year
     * @param int $week
     * @param int $day
     * @param int $hour
     * @return int
     */
    public function getHourlyState($year, $week, $day, $hour)
    {
        throw new LogicException("not implemented in subclass");
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return (string) json_encode(['type' => 'daily', 'states' => $this->states], JSON_PRETTY_PRINT);
    }
}
