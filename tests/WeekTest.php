<?php

/**
 * Copyright 2014-2023 Christoph M. Becker
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

class WeekTest extends TestCase
{
    /**
     * @var Week
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new Week(9, 2017);
    }

    public function testCreateRange()
    {
        $actual = Week::createRange(2017, 9, 7);
        $this->assertContainsOnlyInstancesOf(Week::class, $actual);
        $this->assertCount(7, $actual);
        for ($i = 0; $i < 7; $i++) {
            $this->assertSame($i + 9, $actual[$i]->getWeek());
        }
    }

    public function testGetWeek()
    {
        $this->assertSame(9, $this->subject->getWeek());
    }

    public function testGetYear()
    {
        $this->assertSame(2017, $this->subject->getYear());
    }

    public function testGetIso()
    {
        $this->assertSame('2017-09', $this->subject->getIso());
    }

    public function testGetDatesOfWeek()
    {
        $actual = $this->subject->getDatesOfWeek();
        $this->assertContainsOnlyInstancesOf(DateTimeImmutable::class, $actual);
        $this->assertCount(7, $actual);
        $this->assertSame('27', $actual[1]->format('j'));
        $this->assertSame('5', $actual[7]->format('j'));
    }

    /**
     * @dataProvider provideGetNextWeekData
     * @param int $offset
     * @param string $iso
     */
    public function testGetNextWeek($offset, $iso)
    {
        $this->assertSame($iso, $this->subject->getNextWeek($offset)->getIso());
    }

    /**
     * @return array
     */
    public function provideGetNextWeekData()
    {
        return array(
            [-9, '2016-52'],
            [-8, '2017-01'],
            [ 0, '2017-09'],
            [ 1, '2017-10'],
            [43, '2017-52'],
            [44, '2018-01']
        );
    }

    public function testGetNextWeekDefaut()
    {
        $this->assertSame('2017-10', $this->subject->getNextWeek()->getIso());
    }

    /**
     * @dataProvider provideCompareData
     * @param int $month
     * @param int $year
     * @param int $expected
     */
    public function testCompare($month, $year, $expected)
    {
        $this->assertSame($expected, $this->subject->compare(new Week($month, $year)));
    }

    /**
     * @return array
     */
    public function provideCompareData()
    {
        return array(
            [52, 2016,  1],
            [ 1, 2017,  1],
            [ 9, 2017,  0],
            [52, 2017, -1],
            [ 1, 2018, -1]
        );
    }
}
