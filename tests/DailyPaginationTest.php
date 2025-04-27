<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;
use DateTimeImmutable;
use Ocal\Dto\PaginationItem;

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
                new PaginationItem(2017, 3, "label_today"),
                new PaginationItem(2017, 4, "label_next_month"),
                new PaginationItem(2018, 3, "label_next_year"),
            ]],
            [2017, 1, new DateTimeImmutable('2016-11-01'), [
                new PaginationItem(2016, 11, "label_today"),
                new PaginationItem(2016, 12, "label_prev_month"),
                new PaginationItem(2017, 2, "label_next_month"),
                new PaginationItem(2018, 1, "label_next_year"),
            ]],
            [2016, 12, new DateTimeImmutable('2015-12-01'), [
                new PaginationItem(2015, 12, "label_today"),
                new PaginationItem(2016, 11, "label_prev_month"),
                new PaginationItem(2017, 1, "label_next_month"),
            ]],
        );
    }
}
