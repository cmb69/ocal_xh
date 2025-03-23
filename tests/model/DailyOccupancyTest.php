<?php

/**
 * Copyright 2017-2023 Christoph M. Becker
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
        $this->subject = new DailyOccupancy('foo', 3);
    }

    public function testCreateFromJsonWithInvalidType()
    {
        $this->assertNull(Occupancy::createFromJson('foo', '{"type": "invalid", "states": {}}', 3));
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
        $this->subject->setState('2017-02-27', 3);
        $this->assertSame(3, $this->subject->getDailyState(2017, 2, 27));
    }

    public function testSetStateToZeroUnsetsIt()
    {
        $this->subject->setState('2017-02-28', 0);
        $this->assertSame(0, $this->subject->getDailyState(2017, 2, 28));
    }

    public function testSerialization()
    {
        $this->subject->setName("");
        $subject = unserialize(serialize($this->subject));
        $this->assertEquals($this->subject, $subject);
    }
}
