<?php

/**
 * Copyright 2017 Christoph M. Becker
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

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class DailyPaginationTest extends TestCase
{
    public function setUp(): void
    {
        global $plugin_cf;

        $plugin_cf['ocal']['pagination_past'] = "0";
        $plugin_cf['ocal']['pagination_future'] = "18";
    }

    /**
     * @dataProvider provideGetItemsData
     * @param int $year
     * @param int $month
     */
    public function testGetItems($year, $month, $now, array $expected)
    {
        $subject = new DailyPagination($year, $month, $now);
        $this->assertEquals($expected, $subject->getItems());
    }

    /**
     * @return array
     */
    public function provideGetItemsData()
    {
        return array(
            [2017, 3, new DateTimeImmutable('2017-03-01'), [
                (object) ['year' => 2017, 'monthOrWeek' => 3, 'label' => 'label_today'],
                (object) ['year' => 2017, 'monthOrWeek' => 4, 'label' => 'label_next_month'],
                (object) ['year' => 2018, 'monthOrWeek' => 3, 'label' => 'label_next_year']
            ]],
            [2017, 1, new DateTimeImmutable('2016-11-01'), [
                (object) ['year' => 2016, 'monthOrWeek' => 11, 'label' => 'label_today'],
                (object) ['year' => 2016, 'monthOrWeek' => 12, 'label' => 'label_prev_month'],
                (object) ['year' => 2017, 'monthOrWeek' => 2, 'label' => 'label_next_month'],
                (object) ['year' => 2018, 'monthOrWeek' => 1, 'label' => 'label_next_year']
            ]],
            [2016, 12, new DateTimeImmutable('2015-12-01'), [
                (object) ['year' => 2015, 'monthOrWeek' => 12, 'label' => 'label_today'],
                (object) ['year' => 2016, 'monthOrWeek' => 11, 'label' => 'label_prev_month'],
                (object) ['year' => 2017, 'monthOrWeek' => 1, 'label' => 'label_next_month']
            ]],
        );
    }
}
