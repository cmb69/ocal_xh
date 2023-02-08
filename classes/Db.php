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
    private $foldername;

    /** @var resource */
    protected $lockFile;

    public function __construct(string $foldername)
    {
        $this->foldername = $foldername;
    }

    /** @return void */
    public function lock(bool $exclusive)
    {
        $lockFile = fopen($this->getLockfilename(), 'a');
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

    public function findOccupancy(string $name, bool $hourly = false): Occupancy
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

    private function migrateContents(string $name, bool $hourly): ?string
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

    private function getLockfilename(): string
    {
        return $this->getFoldername() . '.lock';
    }

    protected function getFoldername(): string
    {
        if (!file_exists($this->foldername)) {
            mkdir($this->foldername, 0777, true);
            chmod($this->foldername, 0777);
        }
        return $this->foldername;
    }
}
