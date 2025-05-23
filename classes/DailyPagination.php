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

namespace Ocal;

use DateTimeImmutable;
use Ocal\Dto\PaginationItem;

class DailyPagination extends Pagination
{
    /** @var int */
    private $year;

    /** @var int */
    private $month;

    public function __construct(int $year, int $month, DateTimeImmutable $now, int $past, int $future)
    {
        parent::__construct($now, $past, $future);
        $this->year = (int) $year;
        $this->month = (int) $month;
    }

    /** @return list<PaginationItem> */
    public function getItems(): array
    {
        return $this->filterAndSortItems(
            $this->getItem(null, null, 'today'),
            $this->getItem(0, -1, 'prev_year'),
            $this->getItem(-1, 0, 'prev_month'),
            $this->getItem(1, 0, 'next_month'),
            $this->getItem(0, 1, 'next_year')
        );
    }

    /** @return ($month is null ? ($year is null ? PaginationItem : ?PaginationItem) : ?PaginationItem) */
    private function getItem(?int $month, ?int $year, string $label): ?PaginationItem
    {
        if ($month === null || $year === null) {
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
                return null;
            }
        }
        $monthOrWeek = $month;
        $label = "label_$label";
        return new PaginationItem($year, $monthOrWeek, $label);
    }

    private function isValid(int $month): bool
    {
        $currentMonth = 12 * $this->now->format('Y') + $this->now->format('n');
        return $month >= $currentMonth - $this->past
            && $month <= $currentMonth + $this->future;
    }
}
