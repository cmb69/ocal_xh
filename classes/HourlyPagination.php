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

use stdClass;
use DateTime;

class HourlyPagination extends Pagination
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $week;

    /**
     * @param int $year
     * @param int $month
     */
    public function __construct($year, $week, DateTime $now)
    {
        parent::__construct($now);
        $this->year = (int) $year;
        $this->week = (int) $week;
    }

    /**
     * @param int $weekCount
     * @return stdClass[]
     */
    public function getItems($weekCount)
    {
        return $this->filterAndSortItems(
            $this->getItem(false, 'today'),
            $this->getItem(-$weekCount, 'prev_interval'),
            $this->getItem($weekCount, 'next_interval')
        );
    }

    /**
     * @param int $offset
     * @param string $label
     * @return ?stdClass[]
     */
    private function getItem($offset, $label)
    {
        if ($offset) {
            $week = new Week($this->week, $this->year);
            $week = $week->getNextWeek($offset);
            if (!$this->isWeekPaginationValid($week)) {
                return;
            }
            $year = $week->getYear();
            $weekNum = $week->getWeek();
        } else {
            $year = (int) $this->now->format('o');
            $weekNum = (int) $this->now->format('W');
        }
        $monthOrWeek = $weekNum;
        $label = "label_$label";
        return (object) compact('year', 'monthOrWeek', 'label');
    }

    private function isWeekPaginationValid(Week $week)
    {
        $currentWeek = new Week($this->now->format('W'), $this->now->format('o'));
        return $week->compare($currentWeek->getNextWeek(-$this->config['pagination_past'])) >= 0
            && $week->compare($currentWeek->getNextWeek($this->config['pagination_future'])) <= 0;
    }
}
