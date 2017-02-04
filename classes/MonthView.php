<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

abstract class MonthView
{
    /**
     * @var Month
     */
    protected $month;

    /**
     * @var Occupancy
     */
    protected $occupancy;

    public function __construct(Month $month, Occupancy $occupancy)
    {
        $this->month = $month;
        $this->occupancy = $occupancy;
    }

    /**
     * @param int $day
     * @return string
     */
    protected function formatDate($day)
    {
        return sprintf('%04d-%02d-%02d', $this->month->getYear(), $this->month->getMonth(), $day);
    }
}
