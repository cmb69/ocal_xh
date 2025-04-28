<?php

namespace Ocal;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Ocal\Dto\ListItem;
use Ocal\Dto\WeekListItem;
use Ocal\Model\DailyOccupancy;
use Ocal\Model\HourlyOccupancy;
use Ocal\Model\Month;
use Ocal\Model\Week;

/** @small */
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
        $occupancy = new DailyOccupancy('daily', "");
        $occupancy->setState('2017-03-01', 1, 3);
        $occupancy->setState('2017-03-02', 1, 3);
        $occupancy->setState('2017-03-04', 2, 3);
        $month = new Month(3, 2017);
        $expected = [
            new ListItem("1.–2.", 1, "available"),
            new ListItem("4.", 2, "reserved"),
        ];
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
        $expected = [
            new WeekListItem(
                new DateTimeImmutable('2017-02-27'),
                [
                    new ListItem("08:00–12:00", 1, "available"),
                    new ListItem("14:00–16:00", 2, "reserved"),
                ]
            )
        ];
        $this->assertEquals($expected, $this->sut->getHourlyList($occupancy, $week));
    }

    /** @dataProvider hourlyIntervalData */
    public function testHourlyInterval(string $interval, float $expected): void
    {
        $this->assertEqualsWithDelta($expected, $this->sut->hourlyInterval($interval), 0.001);
    }

    public function hourlyIntervalData(): array
    {
        return [
            "1 hour" => ["1", 1.0],
            "2 hours" => ["2", 2.0],
            "1.5 hours" => ["1.5", 1.0],
            "30 minutes" => ["1/2", 1 / 2],
            "20 minutes" => ["1/3", 1 / 3],
            "15 minutes" => ["1/4", 1 / 4],
            "10 minutes" => ["1/6", 1 / 6],
        ];
    }
}
