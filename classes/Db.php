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

class Db
{
    /**
     * @var resource
     */
    protected $lockFile;

    /**
     * @param int $lockMode
     */
    public function __construct($lockMode)
    {
        $lockFilename = $this->getFoldername() . '.lock';
        $this->lockFile = fopen($lockFilename, 'a');
        flock($this->lockFile, (int) $lockMode);
    }

    public function __destruct()
    {
        flock($this->lockFile, LOCK_UN);
    }

    /**
     * @param string $name
     * @param bool $hourly
     * @return Occupancy
     */
    public function findOccupancy($name, $hourly = false)
    {
        $filename = $this->getFoldername() . $name . '.dat';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
        } else {
            $contents = false;
        }
        if ($contents && ($occupancy = unserialize($contents))) {
            $occupancy->setName($name);
        } else {
            if ($hourly) {
                $occupancy = new HourlyOccupancy($name);
            } else {
                $occupancy = new Occupancy($name);
            }
        }
        return $occupancy;
    }

    public function saveOccupancy(Occupancy $occupancy)
    {
        $filename = $this->getFoldername() . $occupancy->getName() . '.dat';
        file_put_contents($filename, serialize($occupancy));
    }

    /**
     * @return string
     */
    protected function getFoldername()
    {
        global $pth;

        $foldername = $pth['folder']['base'] . 'content/ocal/';
        if (!file_exists($foldername)) {
            mkdir($foldername, 0777, true);
            chmod($foldername, 0777);
        }
        return $foldername;
    }
}
