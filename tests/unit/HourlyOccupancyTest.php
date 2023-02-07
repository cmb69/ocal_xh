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

use PHPUnit\Framework\TestCase;

class HourlyOccupancyTest extends TestCase
{
    /**
     * @var HourlyOccupancy
     */
    private $subject;

    public function setUp(): void
    {
        global $plugin_cf;

        $plugin_cf['ocal']['state_max'] = '3';
        $this->subject = new HourlyOccupancy('bar');
    }

    public function testSetAndGetState()
    {
        $this->subject->setState('2017-09-01-12', 3);
        $this->assertSame(3, $this->subject->getHourlyState(2017, 9, 1, 12));
    }
}
