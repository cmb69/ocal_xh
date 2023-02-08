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
use DateTimeImmutable;

class ListServiceTest extends TestCase
{
    /** @var ListService */
    private $sut;

    public function setUp(): void
    {
        $this->setUpLang();
        $this->setUpConfig();
        $this->sut = new ListService();
    }

    private function setUpConfig()
    {
        global $plugin_cf;

        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
    }

    private function setUpLang()
    {
        global $plugin_tx;

        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
    }

    public function testGetDailyList()
    {
        $occupancy = new DailyOccupancy('daily', 3);
        $occupancy->setState('2017-03-01', 1);
        $occupancy->setState('2017-03-02', 1);
        $occupancy->setState('2017-03-04', 2);
        $month = new Month(3, 2017);
        $expected = array(
            (object) ['range' => '1.–2.', 'state' => 1 , 'label' => 'available'],
            (object) ['range' =>    '4.', 'state' => 2, 'label' => 'reserved']
        );
        $this->assertEquals($expected, $this->sut->getDailyList($occupancy, $month));
    }

    public function testGetHourlyList()
    {
        $occupancy = new HourlyOccupancy('hourly', 3);
        $occupancy->setState('2017-09-01-08', 1);
        $occupancy->setState('2017-09-01-09', 1);
        $occupancy->setState('2017-09-01-10', 1);
        $occupancy->setState('2017-09-01-11', 1);
        $occupancy->setState('2017-09-01-14', 2);
        $occupancy->setState('2017-09-01-15', 2);
        $week = new Week(9, 2017);
        $expected = array(
            (object) array(
                'date' => new DateTimeImmutable('2017-02-27'),
                'list' => array(
                    (object) ['range' => '08:00–11:59', 'state' => 1, 'label' => 'available'],
                    (object) ['range' => '14:00–15:59', 'state' => 2, 'label' => 'reserved']
                )
            )
        );
        $this->assertEquals($expected, $this->sut->getHourlyList($occupancy, $week));
    }
}
