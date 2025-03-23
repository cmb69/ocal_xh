<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class HourlyPaginationTest extends TestCase
{
    /**
     * @dataProvider provideGetItemsData
     * @param int $year
     * @param int $month
     */
    public function testGetItems($year, $week, $now, $weekCount, array $expected)
    {
        $subject = new HourlyPagination($year, $week, $now, 0, 18);
        $this->assertEquals($expected, $subject->getItems($weekCount));
    }

    /**
     * @return array
     */
    public function provideGetItemsData()
    {
        return array(
            [2017, 9, new DateTimeImmutable('2017-03-01'), 1, [
                (object) ['year' => 2017, 'monthOrWeek' => 9, 'label' => 'label_today'],
                (object) ['year' => 2017, 'monthOrWeek' => 10, 'label' => 'label_next_interval']
            ]],
            [2017, 9, new DateTimeImmutable('2017-01-01'), 2, [
                (object) ['year' => 2016, 'monthOrWeek' => 52, 'label' => 'label_today'],
                (object) ['year' => 2017, 'monthOrWeek' => 7, 'label' => 'label_prev_interval'],
                (object) ['year' => 2017, 'monthOrWeek' => 11, 'label' => 'label_next_interval']
            ]],
        );
    }
}
