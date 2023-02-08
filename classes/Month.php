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

class Month
{
    /** @var int */
    private $month;

    /** @var int */
    private $year;

    /** @var int */
    private $timestamp;

    /** @return list<Month> */
    public static function createRange(int $year, int $month, int $count): array
    {
        $months = [];
        $month = new Month($month, $year);
        while ($count) {
            $months[] = $month;
            $month = $month->getNextMonth();
            $count--;
        }
        return $months;
    }

    public function __construct(int $month, int $year)
    {
        $this->month = (int) $month;
        $this->year = (int) $year;
        $this->timestamp = (int) mktime(0, 0, 0, $month, 1, $year);
    }

    public function getLastDay(): int
    {
        return (int) date('t', $this->timestamp);
    }

    public function getMonth(): int
    {
        return $this->month;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function getIso(): string
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }

    public function getNextMonth(): self
    {
        $month = $this->month + 1;
        $year = $this->year;
        if ($month > 12) {
            $month = 1;
            $year += 1;
        }
        return new Month($month, $year);
    }

    /** @return list<list<int|null>> */
    public function getDaysOfWeeks(): array
    {
        $mondays = range($this->getDayOffset(), $this->getLastDay(), 7);
        $getWeekDays = function ($monday) {
            $lastDay = $this->getLastDay();
            $result = array();
            for ($day = $monday; $day < $monday + 7; $day++) {
                $result[] = $day >= 1 && $day <= $lastDay ? $day : null;
            }
            return $result;
        };
        return array_map($getWeekDays, $mondays);
    }

    private function getDayOffset(): int
    {
        $weekday = date('w', $this->timestamp);
        return $weekday ? 2 - $weekday : 2 - 7;
    }
}
