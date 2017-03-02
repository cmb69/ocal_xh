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

use Serializable;

class Occupancy
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<date,state>
     */
    protected $states;

    /**
     * @param string $name
     * @param string $json
     * @return ?Occupancy
     */
    public static function createFromJson($name, $json)
    {
        $array = json_decode($json, true);
        switch ($array['type']) {
            case 'daily':
                $result = new Occupancy($name);
                break;
            case 'hourly':
                $result = new HourlyOccupancy($name);
                break;
            default:
                return null;
        }
        $result->states = $array['states'];
        return $result;
    }

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
        $this->states = array();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

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
     * @param string $date
     * @return int
     */
    protected function getState($date)
    {
        if (isset($this->states[$date])) {
            return $this->states[$date];
        } else {
            return 0;
        }
    }

    /**
     * @param string $date
     * @param int $state
     */
    public function setState($date, $state)
    {
        if ($state) {
            $this->states[$date] = $state;
        } else {
            unset($this->states[$date]);
        }
    }

    /**
     * @return string
     */
    public function toJson()
    {
        return json_encode(['type' => 'daily', 'states' => $this->states], JSON_PRETTY_PRINT);
    }
}
