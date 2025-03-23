<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;

class MonthTest extends TestCase
{
    /**
     * @var Month
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new Month(2, 2017);
    }

    public function testCreateRange()
    {
        $actual = Month::createRange(2017, 3, 4);
        $this->assertContainsOnlyInstancesOf(Month::class, $actual);
        $this->assertCount(4, $actual);
        for ($i = 0; $i < 4; $i++) {
            $this->assertSame($i + 3, $actual[$i]->getMonth());
        }
    }

    public function testGetLastDay()
    {
        $this->assertSame(28, $this->subject->getLastDay());
    }

    public function testGetMonth()
    {
        $this->assertSame(2, $this->subject->getMonth());
    }

    public function testGetYear()
    {
        $this->assertSame(2017, $this->subject->getYear());
    }

    public function testGetIso()
    {
        $this->assertSame('2017-02', $this->subject->getIso());
    }

    public function testGetNextMonth()
    {
        $this->assertSame('2017-03', $this->subject->getNextMonth()->getIso());
    }

    public function testGetNextMonthOfDecember()
    {
        $subject = new Month(12, 2017);
        $this->assertSame('2018-01', $subject->getNextMonth()->getIso());
    }

    public function testGetDaysOfWeeks()
    {
        $expected = array(
            [null, null,    1,    2,    3,    4,    5],
            [   6,    7,    8,    9,   10,   11,   12],
            [  13,   14,   15,   16,   17,   18,   19],
            [  20,   21,   22,   23,   24,   25,   26],
            [  27,   28, null, null, null, null, null]
        );
        $this->assertSame($expected, $this->subject->getDaysOfWeeks());
    }
}
