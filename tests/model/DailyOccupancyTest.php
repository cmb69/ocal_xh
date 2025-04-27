<?php

namespace Ocal\Model;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;

/** @small */
class DailyOccupancyTest extends TestCase
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

    public function testRoundTrip(): void
    {
        $expected = DailyOccupancy::update("foo", $this->store);
        $expected->setState('2017-02-28', 0, 3);
        $this->store->commit();
        $actual = DailyOccupancy::retrieve("foo", $this->store);
        $this->assertEquals($expected, $actual);
    }

    public function testReadBrokenFormat(): void
    {
        file_put_contents(vfsStream::url("root/foo.json"), '{}');
        $actual = DailyOccupancy::retrieve("foo", $this->store);
        $this->assertNull($actual);
    }

    public function testReadLegacyFormat(): void
    {
        file_put_contents(vfsStream::url("root/foo.dat"), '{a:1:{s:10:"2017-02-03";s:1:"1";}}');
        $actual = DailyOccupancy::retrieve("foo", $this->store);
        $expected = new DailyOccupancy("foo");
        $expected->setState("2017-02-03", 1, 3);
        $this->assertEquals($expected, $actual);
    }

    public function testReadEmptyLegacyFormat(): void
    {
        file_put_contents(vfsStream::url("root/foo.dat"), '');
        $actual = DailyOccupancy::retrieve("foo", $this->store);
        $this->assertNull($actual);
    }

    public function testReadBrokenLegacyFormat(): void
    {
        file_put_contents(vfsStream::url("root/foo.dat"), 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}');
        $actual = DailyOccupancy::retrieve("foo", $this->store);
        $this->assertNull($actual);
    }

    public function testMigration(): void
    {
        file_put_contents(vfsStream::url("root/foo.dat"), '{a:1:{s:10:"2017-02-03";s:1:"1";}}');
        $expected = DailyOccupancy::update("foo", $this->store);
        $this->store->commit();
        $this->assertFileExists(vfsStream::url("root/foo.json"));
        $actual = DailyOccupancy::retrieve("foo", $this->store);
        $this->assertEquals($expected, $actual);
    }
}
