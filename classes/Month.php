<?php

/**
 * The months.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The months.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Month
{
    /**
     * The month.
     *
     * @var int
     */
    protected $month;

    /**
     * The year.
     *
     * @var int
     */
    protected $year;

    /**
     * The timestamp of the beginning of the month.
     *
     * @var int
     */
    protected $timestamp;

    /**
     * Initializes a new instance.
     *
     * @param int $month A month.
     * @param int $year  A year.
     */
    public function __construct($month, $year)
    {
        $this->month = (int) $month;
        $this->year = (int) $year;
        $this->timestamp = mktime(0, 0, 0, $month, 1, $year);
    }

    /**
     * Returns the day offset, i.e. day number of the first monday of the
     * monthly calendar (in range [-5,1]).
     *
     * @return int
     */
    public function getDayOffset()
    {
        $weekday = date('w', $this->timestamp);
        return $weekday ? 2 - $weekday : 2 - 7;

    }

    /**
     * Returns the number of the last day (in range [28,31]).
     *
     * @return int
     */
    public function getLastDay()
    {
        return date('t', $this->timestamp);
    }

    /**
     * Returns the month.
     *
     * @return int
     */
    public function getMonth()
    {
        return $this->month;
    }

    /**
     * Returns the year.
     *
     * @return int
     */
    public function getYear()
    {
        return $this->year;
    }

    /**
     * Returns an ISO formatted date (YYYY-MM).
     *
     * @return string
     */
    public function getIso()
    {
        return sprintf('%04d-%02d', $this->year, $this->month);
    }

    /**
     * Returns the next month.
     *
     * @return Ocal_Month
     */
    public function getNextMonth()
    {
        $month = $this->month + 1;
        $year = $this->year;
        if ($month > 12) {
            $month = 1;
            $year += 1;
        }
        return new Ocal_Month($month, $year);
    }
}

?>
