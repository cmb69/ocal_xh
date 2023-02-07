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
use DateTime;

class ListServiceTest extends TestCase
{
    public function setUp(): void
    {
        $this->setUpLang();
        $this->setUpConfig();
    }

    private function setUpConfig()
    {
        global $plugin_cf;

        $plugin_cf['ocal'] = array(
            'state_max' => '3',
            'hour_first' => '8',
            'hour_last' => '16',
            'hour_interval' => '2'
        );
    }

    private function setUpLang()
    {
        global $plugin_tx;

        $plugin_tx['ocal'] = array(
            'label_state_0' => '',
            'label_state_1' => 'reserved',
            'label_state_2' => 'booked'
        );
    }

    public function testGetDailyList()
    {
        $occupancy = new DailyOccupancy('daily');
        $occupancy->setState('2017-03-01', 1);
        $occupancy->setState('2017-03-02', 1);
        $occupancy->setState('2017-03-04', 2);
        $month = new Month(3, 2017);
        $expected = array(
            (object) ['range' => '1.–2.', 'state' => 1 , 'label' => 'reserved'],
            (object) ['range' =>    '4.', 'state' => 2, 'label' => 'booked']
        );
        $this->assertEquals($expected, (new ListService)->getDailyList($occupancy, $month));
    }

    public function testGetHourlyList()
    {
        $occupancy = new HourlyOccupancy('hourly');
        $occupancy->setState('2017-09-01-08', 1);
        $occupancy->setState('2017-09-01-09', 1);
        $occupancy->setState('2017-09-01-10', 1);
        $occupancy->setState('2017-09-01-11', 1);
        $occupancy->setState('2017-09-01-14', 2);
        $occupancy->setState('2017-09-01-15', 2);
        $week = new Week(9, 2017);
        $expected = array(
            (object) array(
                'date' => new DateTime('2017-02-27'),
                'list' => array(
                    (object) ['range' => '08:00–11:59', 'state' => 1, 'label' => 'reserved'],
                    (object) ['range' => '14:00–15:59', 'state' => 2, 'label' => 'booked']
                )
            )
        );
        $this->assertEquals($expected, (new ListService)->getHourlyList($occupancy, $week));
    }
}
