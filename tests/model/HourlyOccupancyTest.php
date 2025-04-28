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
        $sut = new HourlyOccupancy('bar', "");
        $this->assertSame('bar', $sut->getName());
    }

    public function testUnsetStateIsZero()
    {
        $sut = new HourlyOccupancy('bar', "");
        $this->assertSame(0, $sut->getHourlyState(2017, 9, 1, 12));
    }

    public function testSetAndGetState()
    {
        $sut = new HourlyOccupancy('bar', "");
        $sut->setState('2017-09-01-12', 3, 3);
        $this->assertSame(3, $sut->getHourlyState(2017, 9, 1, 12));
    }

    public function testSetStateToZeroUnsetsIt()
    {
        $sut = new HourlyOccupancy('bar', "");
        $sut->setState('2017-09-01-12', 0, 3);
        $this->assertSame(0, $sut->getHourlyState(2017, 9, 1, 12));
    }

    public function testRoundTrip(): void
    {
        $expected = HourlyOccupancy::update("bar", $this->store);
        $expected->setState("2017-09-01-12", 0, 3);
        $this->store->commit();
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $actual->setChecksum("da39a3ee5e6b4b0d3255bfef95601890afd80709");
        $this->assertEquals($expected, $actual);
    }

    public function testReadBrokenFormat(): void
    {
        file_put_contents(vfsStream::url("root/bar.json"), '{}');
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $this->assertNull($actual);
    }

    public function testReadLegacyFormat(): void
    {
        file_put_contents(vfsStream::url("root/bar.dat"), '{a:1:{s:13:"2017-09-01-12";s:1:"1";}}');
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $expected = new HourlyOccupancy("bar", "000235ad796aa51abb945efa10f2d1cd18316525");
        $expected->setState("2017-09-01-12", 1, 3);
        $this->assertEquals($expected, $actual);
    }

    public function testReadEmptyLegacyFormat(): void
    {
        file_put_contents(vfsStream::url("root/bar.dat"), '');
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $this->assertNull($actual);
    }

    public function testReadBrokenLegacyFormat(): void
    {
        file_put_contents(vfsStream::url("root/bar.dat"), 'O:8:"stdClass":1:{s:5:"hello";s:5:"world";}');
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $this->assertNull($actual);
    }

    public function testMigration(): void
    {
        file_put_contents(vfsStream::url("root/bar.dat"), '{a:1:{s:13:"2017-09-01-12";s:1:"1";}}');
        $expected = HourlyOccupancy::update("bar", $this->store);
        $this->store->commit();
        $this->assertFileExists(vfsStream::url("root/bar.json"));
        $actual = HourlyOccupancy::retrieve("bar", $this->store);
        $this->assertEquals($expected, $actual);
    }

    /** @dataProvider formatHourMinutesData */
    public function testFormatHourMinutes(float $hour, string $expected): void
    {
        $this->assertSame($expected, HourlyOccupancy::formatHourMinutes($hour));
    }

    public function formatHourMinutesData(): array
    {
        return [
            [17.99, "18:00"],
            [9.01, "09:00"],
        ];
    }
}
