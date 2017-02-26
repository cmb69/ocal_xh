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

class Occupancy implements Serializable
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
     * @param string $date
     * @return int
     */
    public function getState($date)
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
    public function serialize()
    {
        return serialize($this->states);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->states = unserialize($data);
    }
}
