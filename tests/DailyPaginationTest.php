<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;

class DailyPaginationTest extends TestCase
{
    /**
     * @dataProvider provideGetItemsData
     * @param int $year
     * @param int $month
     */
    public function testGetItems($year, $month, $now, array $expected)
    {
        $subject = new DailyPagination($year, $month, $now, 0, 18);
        $this->assertEquals($expected, $subject->getItems());
    }

    /**
     * @return array
     */
    public function provideGetItemsData()
    {
        return array(
            [2017, 3, new DateTimeImmutable('2017-03-01'), [
                (object) ['year' => 2017, 'monthOrWeek' => 3, 'label' => 'label_today'],
                (object) ['year' => 2017, 'monthOrWeek' => 4, 'label' => 'label_next_month'],
                (object) ['year' => 2018, 'monthOrWeek' => 3, 'label' => 'label_next_year']
            ]],
            [2017, 1, new DateTimeImmutable('2016-11-01'), [
                (object) ['year' => 2016, 'monthOrWeek' => 11, 'label' => 'label_today'],
                (object) ['year' => 2016, 'monthOrWeek' => 12, 'label' => 'label_prev_month'],
                (object) ['year' => 2017, 'monthOrWeek' => 2, 'label' => 'label_next_month'],
                (object) ['year' => 2018, 'monthOrWeek' => 1, 'label' => 'label_next_year']
            ]],
            [2016, 12, new DateTimeImmutable('2015-12-01'), [
                (object) ['year' => 2015, 'monthOrWeek' => 12, 'label' => 'label_today'],
                (object) ['year' => 2016, 'monthOrWeek' => 11, 'label' => 'label_prev_month'],
                (object) ['year' => 2017, 'monthOrWeek' => 1, 'label' => 'label_next_month']
            ]],
        );
    }
}
