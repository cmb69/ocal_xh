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

abstract class Occupancy
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<string,int>
     */
    protected $states;

    /**
     * @var int
     */
    private $maxState;

    /**
     * @param string $name
     * @param string $json
     * @return ?self
     */
    public static function createFromJson($name, $json)
    {
        $array = json_decode($json, true);
        if (!($result = self::instantiateType($array['type'], $name))) {
            return null;
        }
        foreach ($array['states'] as $date => $state) {
            $result->setState($date, $state);
        }
        return $result;
    }

    /**
     * @param string $type
     * @param string $name
     * @return self|null
     */
    private static function instantiateType($type, $name)
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

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        global $plugin_cf;

        $this->name = (string) $name;
        $this->states = array();
        $this->maxState = (int) $plugin_cf['ocal']['state_max'];
    }

    /**
     * @param int $year
     * @param int $month
     * @param int $day
     * @return int
     */
    abstract public function getDailyState($year, $month, $day);

    /**
     * @param int $year
     * @param int $week
     * @param int $day
     * @param int $hour
     * @return int
     */
    abstract public function getHourlyState($year, $week, $day, $hour);

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     * @return void
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @param string $date
     * @return int
     */
    protected function getState($date)
    {
        if (!isset($this->states[$date])) {
            return 0;
        }
        return $this->states[$date];
    }

    /**
     * @param string $date
     * @param int $state
     * @return void
     */
    public function setState($date, $state)
    {
        if ($state > 0 && $state <= $this->maxState) {
            $this->states[$date] = $state;
        } else {
            unset($this->states[$date]);
        }
    }

    /**
     * @return string
     */
    abstract public function toJson();
}
