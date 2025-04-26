<?php

namespace Ocal\Model;

use PHPUnit\Framework\TestCase;

class DailyOccupancyTest extends TestCase
{
    /**
     * @var Occupancy
     */
    private $subject;

    public function setUp(): void
    {
        $this->subject = new DailyOccupancy('foo');
    }

    public function testGetName()
    {
        $this->assertSame('foo', $this->subject->getName());
    }

    public function testSetAndGetName()
    {
        $this->subject->setName('bar');
        $this->assertSame('bar', $this->subject->getName());
    }

    public function testUnsetStateIsZero()
    {
        $this->assertSame(0, $this->subject->getDailyState(2017, 2, 26));
    }

    public function testSetAndGetState()
    {
        $this->subject->setState('2017-02-27', 3, 3);
        $this->assertSame(3, $this->subject->getDailyState(2017, 2, 27));
    }

    public function testSetStateToZeroUnsetsIt()
    {
        $this->subject->setState('2017-02-28', 0, 3);
        $this->assertSame(0, $this->subject->getDailyState(2017, 2, 28));
    }

    public function testSerialization()
    {
        $this->subject->setName("");
        $subject = unserialize(serialize($this->subject));
        $this->assertEquals($this->subject, $subject);
    }
}
