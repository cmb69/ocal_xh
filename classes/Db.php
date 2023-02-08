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
    /** @var string */
    private $lockFilename;

    /**
     * @var resource
     */
    protected $lockFile;

    public function __construct()
    {
        $this->lockFilename = $this->getFoldername() . '.lock';
        // $this->lockFile = fopen($lockFilename, 'a');
        // flock($this->lockFile, (int) $lockMode);
    }

    public function __destruct()
    {
        // flock($this->lockFile, LOCK_UN);
    }

    /** @return void */
    public function lock(bool $exclusive)
    {
        $lockFile = fopen($this->lockFilename, 'a');
        assert($lockFile !== false);
        $this->lockFile = $lockFile;
        flock($this->lockFile, $exclusive ? LOCK_EX : LOCK_SH);
    }

    /** @return void */
    public function unlock()
    {
        flock($this->lockFile, LOCK_UN);
        fclose($this->lockFile);
    }

    /**
     * @param string $name
     * @param bool $hourly
     * @return Occupancy
     */
    public function findOccupancy($name, $hourly = false)
    {
        $filename = $this->getFoldername() . $name . '.json';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
        } else {
            $contents = $this->migrateContents($name, $hourly);
        }
        if ($contents && ($occupancy = Occupancy::createFromJson($name, $contents))) {
            return $occupancy;
        }
        if ($hourly) {
            return new HourlyOccupancy($name);
        }
        return new DailyOccupancy($name);
    }

    /**
     * @param string $name
     * @param bool $hourly
     * @return ?string
     */
    private function migrateContents($name, $hourly)
    {
        $filename = $this->getFoldername() . $name . '.dat';
        if (!is_readable($filename)) {
            return null;
        }
        $contents = file_get_contents($filename);
        if (!$contents) {
            return null;
        }
        if (!preg_match('/{(.+)}$/s', $contents, $matches)) {
            return null;
        }
        $states = unserialize($matches[1]);
        return (string) json_encode(['type' => $hourly ? 'hourly' : 'daily', 'states' => $states]);
    }

    /** @return void */
    public function saveOccupancy(Occupancy $occupancy)
    {
        $filename = $this->getFoldername() . $occupancy->getName() . '.json';
        file_put_contents($filename, $occupancy->toJson());
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
