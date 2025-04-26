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

    /** @var array<string,int> */
    protected $states;

    public static function createFromJson(string $name, string $json): ?self
    {
        $array = json_decode($json, true);
        assert(is_array($array)); // TODO: proper validation
        if (!($result = self::instantiateType($array['type'], $name))) {
            return null;
        }
        foreach ($array['states'] as $date => $state) {
            $result->setState($date, $state, PHP_INT_MAX);
        }
        return $result;
    }

    private static function instantiateType(string $type, string $name): ?self
    {
        switch ($type) {
            case 'daily':
                return new DailyOccupancy($name);
            case 'hourly':
                return new HourlyOccupancy($name);
            default:
                return null;
        }
    }

    public function __construct(string $name)
    {
        $this->name = (string) $name;
        $this->states = array();
    }

    abstract public function getDailyState(int $year, int $month, int $day): int;

    abstract public function getHourlyState(int $year, int $week, int $day, int $hour): int;

    public function getName(): string
    {
        return $this->name;
    }

    /** @return void */
    public function setName(string $name)
    {
        $this->name = (string) $name;
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

    abstract public function toJson(): string;
}
