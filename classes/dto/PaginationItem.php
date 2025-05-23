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

namespace Ocal\Dto;

class PaginationItem
{
    /** @var int */
    public $year;

    /** @var int */
    public $monthOrWeek;

    /** @var string */
    public $label;

    /** @var ?string */
    public $url;

    public function __construct(int $year, int $monthOrWeek, string $label)
    {
        $this->year = $year;
        $this->monthOrWeek = $monthOrWeek;
        $this->label = $label;
    }
}
