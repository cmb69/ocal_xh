<?php

/**
 * Copyright 2014-2023 Christoph M. Becker
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

namespace Ocal\Model;

use LogicException;

class HourlyOccupancy extends Occupancy
{
    public function getHourlyState(int $year, int $week, int $day, int $hour): int
    {
        $date = sprintf('%04d-%02d-%02d-%02d', $year, $week, $day, $hour);
        return $this->getState($date);
    }

    public function getDailyState(int $year, int $month, int $day): int
    {
        throw new LogicException("not implemented in subclass");
    }

    public function toJson(): string
    {
        return (string) json_encode(['type' => 'hourly', 'states' => $this->states]);
    }
}
