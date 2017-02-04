<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use DateTime;

class Week
{
    /**
     * @var int
     */
    protected $week;

    /**
     * @var int
     */
    protected $year;

    /**
     * @param int $week
     * @param int $year
     */
    public function __construct($week, $year)
    {
        $this->week = (int) $week;
        $this->year = (int) $year;
    }

    /**
     * @return int
     */
    public function getWeek()
    {
        return $this->week;
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
        return sprintf('%04d-%02d', $this->year, $this->week);
    }

    /**
     * @param int $offset
     * @return Week
     */
    public function getNextWeek($offset = 1)
    {
        $date = new DateTime();
        $date->setISODate($this->year, $this->week);
        $date->modify(sprintf('+%-d week', $offset));
        $week = $date->format('W');
        $year = $date->format('o');
        return new self($week, $year);
    }
}
