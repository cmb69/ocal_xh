<?php

namespace Ocal\Model;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;

/** @small */
class HourlyOccupancyTest extends TestCase
{
    /** @var DocumentStore */
    private $store;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->store = new DocumentStore(vfsStream::url("root/"));
    }

    public function testGetName()
    {
        $sut = new HourlyOccupancy('bar');
        $this->assertSame('bar', $sut->getName());
    }

    public function testUnsetStateIsZero()
    {
        $sut = new HourlyOccupancy('bar');
        $this->assertSame(0, $sut->getHourlyState(2017, 9, 1, 12));
    }

    public function testSetAndGetState()
    {
        $sut = new HourlyOccupancy('bar');
        $sut->setState('2017-09-01-12', 3, 3);
        $this->assertSame(3, $sut->getHourlyState(2017, 9, 1, 12));
    }

    public function testSetStateToZeroUnsetsIt()
    {
        $sut = new HourlyOccupancy('bar');
        $sut->setState('2017-09-01-12', 0, 3);
        $this->assertSame(0, $sut->getHourlyState(2017, 9, 1, 12));
    }

    public function testRoundTrip(): void
    {
        $expected = HourlyOccupancy::update("bar", $this->store);
        $expected->setState("2017-09-01-12", 0, 3);
        $this->store->commit();
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $this->assertEquals($expected, $actual);
    }
}
