<?php

namespace Ocal\Model;

use PHPUnit\Framework\TestCase;

class HourlyOccupancyTest extends TestCase
{
    /**
     * @var HourlyOccupancy
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new HourlyOccupancy('bar');
    }

    public function testSetAndGetState()
    {
        $this->subject->setState('2017-09-01-12', 3, 3);
        $this->assertSame(3, $this->subject->getHourlyState(2017, 9, 1, 12));
    }
}
