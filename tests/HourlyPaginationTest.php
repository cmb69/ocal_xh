<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Ocal\Dto\PaginationItem;

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
                new PaginationItem(2017, 9, "label_today"),
                new PaginationItem(2017, 10, "label_next_interval"),
            ]],
            [2017, 9, new DateTimeImmutable('2017-01-01'), 2, [
                new PaginationItem(2016, 52, "label_today"),
                new PaginationItem(2017, 7, "label_prev_interval"),
                new PaginationItem(2017, 11, "label_next_interval"),
            ]],
        );
    }
}
