<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

abstract class WeekView
{
    /**
     * @var Week
     */
    protected $week;

    /**
     * @var Occupancy
     */
    protected $occupancy;

    public function __construct(Week $week, Occupancy $occupancy)
    {
        $this->week = $week;
        $this->occupancy = $occupancy;
    }

    /**
     * @param int $day
     * @param int $hour
     * @return string
     */
    protected function formatDate($day, $hour)
    {
        return sprintf('%04d-%02d-%02d-%02d', $this->week->getYear(), $this->week->getWeek(), $day, $hour);
    }
}
