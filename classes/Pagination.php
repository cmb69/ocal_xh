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

abstract class Pagination
{
    /** @var DateTimeImmutable */
    protected $now;

    /** @var int */
    protected $past;

    /** @var int */
    protected $future;

    public function __construct(DateTimeImmutable $now, int $past, int $future)
    {
        $this->now = $now;
        $this->past = $past;
        $this->future = $future;
    }

    /** @return list<PaginationItem> */
    protected function filterAndSortItems(PaginationItem $today, ?PaginationItem ...$items): array
    {
        $result = [$today];
        foreach ($items as $item) {
            if ($item && !($item->year === $today->year && $item->monthOrWeek === $today->monthOrWeek)) {
                $result[] = $item;
            }
        }
        usort($result, function ($a, $b) {
            return (100 * $a->year + $a->monthOrWeek) - (100 * $b->year + $b->monthOrWeek);
        });
        return array_values($result);
    }
}
