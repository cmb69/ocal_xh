<?php

namespace Ocal\Model;

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
