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

use LogicException;
use Plib\Document;
use Plib\DocumentStore;

final class HourlyOccupancy extends Occupancy implements Document
{
    public static function fromString(string $contents, string $key): ?self
    {
        if (preg_match('/\.dat$/', $key)) {
            if (!preg_match('/{(.+)}$/s', $contents, $matches)) {
                return null;
            }
            $states = @unserialize($matches[1]);
            if (!is_array($states)) {
                return null;
            }
            $that = new self(basename($key, ".dat"));
            foreach ($states as $date => $state) {
                if (is_string($date) && is_numeric($state)) {
                    $that->setState($date, $state, PHP_INT_MAX);
                }
            }
            return $that;
        }
        if ($contents === "") {
            return new self(basename($key, ".json"));
        }
        $array = json_decode($contents, true);
        if (
            !is_array($array)
            || !array_key_exists("type", $array)
            || $array["type"] !== "hourly"
            || !array_key_exists("states", $array)
            || !is_array($array["states"])
        ) {
            return null;
        }
        $result = new self(basename($key, ".json"));
        foreach ($array["states"] as $date => $state) {
            $result->setState($date, $state, PHP_INT_MAX);
        }
        return $result;
    }

    public static function retrieve(string $name, DocumentStore $store): ?self
    {
        $keys = $store->find("/$name\\.(?:json|dat)$/");
        if (!in_array("$name.json", $keys, true) && in_array("$name.dat", $keys, true)) {
            return $store->retrieve("$name.dat", self::class);
        }
        return $store->retrieve($name . ".json", self::class);
    }

    public static function update(string $name, DocumentStore $store): ?self
    {
        $keys = $store->find("/$name\\.(?:json|dat)$/");
        if (!in_array("$name.json", $keys, true) && in_array("$name.dat", $keys, true)) {
            return $store->update("$name.dat", self::class);
        }
        return $store->update($name . ".json", self::class);
    }

    public function getHourlyState(int $year, int $week, int $day, int $hour): int
    {
        $date = sprintf('%04d-%02d-%02d-%02d', $year, $week, $day, $hour);
        return $this->getState($date);
    }

    public function getDailyState(int $year, int $month, int $day): int
    {
        throw new LogicException("not implemented in subclass");
    }

    public function toString(): string
    {
        return (string) json_encode(["type" => "hourly", "states" => $this->states]);
    }
}
