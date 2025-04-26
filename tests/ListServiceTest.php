<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Ocal\Model\DailyOccupancy;
use Ocal\Model\HourlyOccupancy;
use Ocal\Model\Month;
use Ocal\Model\Week;

class ListServiceTest extends TestCase
{
    /** @var ListService */
    private $sut;

    public function setUp(): void
    {
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $this->sut = new ListService($plugin_cf['ocal'], $plugin_tx['ocal']);
    }

    public function testGetDailyList()
    {
        $occupancy = new DailyOccupancy('daily');
        $occupancy->setState('2017-03-01', 1, 3);
        $occupancy->setState('2017-03-02', 1, 3);
        $occupancy->setState('2017-03-04', 2, 3);
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
        $occupancy->setState('2017-09-01-08', 1, 3);
        $occupancy->setState('2017-09-01-09', 1, 3);
        $occupancy->setState('2017-09-01-10', 1, 3);
        $occupancy->setState('2017-09-01-11', 1, 3);
        $occupancy->setState('2017-09-01-14', 2, 3);
        $occupancy->setState('2017-09-01-15', 2, 3);
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
