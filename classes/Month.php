<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

class Month
{
    /**
     * @var int
     */
    protected $month;

    /**
     * @var int
     */
    protected $year;

    /**
     * @var int
     */
    protected $timestamp;

    /**
     * @param int $month
     * @param int $year
     */
    public function __construct($month, $year)
    {
        $this->month = (int) $month;
        $this->year = (int) $year;
        $this->timestamp = mktime(0, 0, 0, $month, 1, $year);
    }

    /**
     * @return int
     */
    public function getDayOffset()
    {
        $weekday = date('w', $this->timestamp);
        return $weekday ? 2 - $weekday : 2 - 7;
    }

    /**
     * @return int
     */
    public function getLastDay()
    {
        return date('t', $this->timestamp);
    }

    /**
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * @return string
     */
    public function getIso()
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }

    /**
     * @return Month
     */
    public function getNextMonth()
    {
        $month = $this->month + 1;
        $year = $this->year;
        if ($month > 12) {
            $month = 1;
            $year += 1;
        }
        return new Month($month, $year);
    }
}
