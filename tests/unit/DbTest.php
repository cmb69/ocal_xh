<?php

/**
 * Copyright 2017 Christoph M. Becker
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

use PHPUnit_Framework_TestCase;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class DbTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Db
     */
    private $subject;

    public function setUp()
    {
        global $pth;

        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        $pth['folder'] = array(
            'base' => vfsStream::url('test/')
        );
        $this->subject = new Db(LOCK_EX);
    }

    public function tearDown()
    {
        unset($this->subject);
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
        $occupancy1 = new DailyOccupancy('foo');
        $this->subject->saveOccupancy($occupancy1);
        $occupancy2 = $this->subject->findOccupancy('foo');
        $this->assertEquals($occupancy1, $occupancy2);
    }

    public function testSaveAndFindHourlyOccupancy()
    {
        $occupancy1 = new HourlyOccupancy('bar');
        $this->subject->saveOccupancy($occupancy1);
        $occupancy2 = $this->subject->findOccupancy('bar');
        $this->assertEquals($occupancy1, $occupancy2);
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
        $this->assertEquals(new DailyOccupancy('foo'), $this->subject->findOccupancy('foo'));
    }
}
