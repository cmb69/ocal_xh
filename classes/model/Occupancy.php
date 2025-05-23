<?php

/**
 * Copyright (c) Christoph M. Becker
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

abstract class Occupancy
{
    /** @var string */
    protected $name;

    /** @var string */
    protected $checksum;

    /** @var array<string,int> */
    protected $states;

    public function __construct(string $name, string $checksum)
    {
        $this->name = (string) $name;
        $this->checksum = $checksum;
        $this->states = array();
    }

    abstract public function getDailyState(int $year, int $month, int $day): int;

    abstract public function getHourlyState(int $year, int $week, int $day, int $hour): int;

    public function getName(): string
    {
        return $this->name;
    }

    public function checksum(): string
    {
        return $this->checksum;
    }

    /** for testing */
    public function setChecksum(string $checksum): void
    {
        $this->checksum = $checksum;
    }

    protected function getState(string $date): int
    {
        if (!isset($this->states[$date])) {
            return 0;
        }
        return $this->states[$date];
    }

    /** @return void */
    public function setState(string $date, int $state, int $max)
    {
        if ($state > 0 && $state <= $max) {
            $this->states[$date] = $state;
        } else {
            unset($this->states[$date]);
        }
    }
}
