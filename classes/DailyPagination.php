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

class DailyPagination extends Pagination
{
    /**
     * @var int
     */
    private $year;

    /**
     * @var int
     */
    private $month;

    /**
     * @param int $year
     * @param int $month
     */
    public function __construct($year, $month, DateTime $now)
    {
        parent::__construct($now);
        $this->year = (int) $year;
        $this->month = (int) $month;
    }

    /**
     * @return stdClass[]
     */
    public function getItems()
    {
        return $this->filterAndSortItems(
            $this->getItem(false, false, 'today'),
            $this->getItem(0, -1, 'prev_year'),
            $this->getItem(-1, 0, 'prev_month'),
            $this->getItem(1, 0, 'next_month'),
            $this->getItem(0, 1, 'next_year')
        );
    }

    /**
     * @param int $month
     * @param int $year
     * @param string $label
     * @return ?object
     */
    private function getItem($month, $year, $label)
    {
        if ($month === false && $year === false) {
            $year = (int) $this->now->format('Y');
            $month = (int) $this->now->format('n');
        } else {
            $month = $this->month + $month;
            $year = $this->year + $year;
            if ($month < 1) {
                $month = 12;
                $year -= 1;
            } elseif ($month > 12) {
                $month = 1;
                $year += 1;
            }
            $wantedMonth = 12 * $year + $month;
            if (!$this->isValid($wantedMonth)) {
                return;
            }
        }
        $monthOrWeek = $month;
        $label = "label_$label";
        return (object) compact('year', 'monthOrWeek', 'label');
    }

    /**
     * @param int $month
     */
    private function isValid($month)
    {
        $currentMonth = 12 * $this->now->format('Y') + $this->now->format('n');
        return $month >= $currentMonth - $this->config['pagination_past']
            && $month <= $currentMonth + $this->config['pagination_future'];
    }
}
