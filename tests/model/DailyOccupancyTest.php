<?php

namespace Ocal\Model;

use PHPUnit\Framework\TestCase;

/** @small */
class DailyOccupancyTest extends TestCase
{
    public function testGetName()
    {
        $sut = new DailyOccupancy('foo');
        $this->assertSame('foo', $sut->getName());
    }

    public function testUnsetStateIsZero()
    {
        $sut = new DailyOccupancy('foo');
        $this->assertSame(0, $sut->getDailyState(2017, 2, 26));
    }

    public function testSetAndGetState()
    {
        $sut = new DailyOccupancy('foo');
        $sut->setState('2017-02-27', 3, 3);
        $this->assertSame(3, $sut->getDailyState(2017, 2, 27));
    }

    public function testSetStateToZeroUnsetsIt()
    {
        $sut = new DailyOccupancy('foo');
        $sut->setState('2017-02-28', 0, 3);
        $this->assertSame(0, $sut->getDailyState(2017, 2, 28));
    }
}
