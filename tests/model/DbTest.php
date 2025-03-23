<?php

namespace Ocal\Model;

use PHPUnit\Framework\TestCase;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class DbTest extends TestCase
{
    /**
     * @var Db
     */
    private $subject;

    public function setUp(): void
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $this->subject = new Db(vfsStream::url('test/content/ocal/'), 3);
        $this->subject->lock(true);
    }

    public function tearDown(): void
    {
        $this->subject->unlock();
    }

    public function testFindNewDailyOccupancy()
    {
        $occupancy = $this->subject->findOccupancy('daily');
        $this->assertInstanceOf(DailyOccupancy::class, $occupancy);
    }

    public function testFindNewHourlyOccupancy()
    {
        $occupancy = $this->subject->findOccupancy('hourly', true);
        $this->assertInstanceOf(HourlyOccupancy::class, $occupancy);
    }

    public function testSaveAndFindOccupancy()
    {
        $occupancy1 = new DailyOccupancy('foo', 3);
        $this->subject->saveOccupancy($occupancy1);
        $occupancy2 = $this->subject->findOccupancy('foo', false);
        $this->assertEquals($occupancy1, $occupancy2);
    }

    public function testSaveAndFindHourlyOccupancy()
    {
        $occupancy1 = new HourlyOccupancy('bar', 3);
        $this->subject->saveOccupancy($occupancy1);
        $occupancy2 = $this->subject->findOccupancy('bar', true);
        $this->assertEquals($occupancy1, $occupancy2);
    }

    public function testReadingDailyAsHourlyOccupancyReturnsNull()
    {
        $occupancy1 = new DailyOccupancy("foo", 1);
        $this->subject->saveOccupancy($occupancy1);
        $occupancy2 = $this->subject->findOccupancy("foo", true);
        $this->assertNull($occupancy2);
    }

    public function testReadingHourlyAsDailyOccupancyReturnsNull()
    {
        $occupancy1 = new HourlyOccupancy("foo", 1);
        $this->subject->saveOccupancy($occupancy1);
        $occupancy2 = $this->subject->findOccupancy("foo", false);
        $this->assertNull($occupancy2);
    }

    public function testMigrateContents()
    {
        file_put_contents(
            vfsStream::url('test/content/ocal/foo.dat'),
            '{a:1:{s:10:"2017-02-03";s:1:"1";}}'
        );
        $this->assertInstanceOf(DailyOccupancy::class, $this->subject->findOccupancy('foo'));
    }

    public function testMigrateBrokenContents()
    {
        file_put_contents(
            vfsStream::url('test/content/ocal/foo.dat'),
            ''
        );
        $this->assertEquals(new DailyOccupancy('foo', 3), $this->subject->findOccupancy('foo'));
    }
}
