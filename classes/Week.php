<?php

/**
 * The weeks.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2016 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The weeks.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Week
{
    /**
     * The week.
     *
     * @var int
     */
    protected $week;

    /**
     * The year.
     *
     * @var int
     */
    protected $year;

    /**
     * Initializes a new instance.
     *
     * @param int $week A week.
     * @param int $year A year.
     */
    public function __construct($week, $year)
    {
        $this->week = (int) $week;
        $this->year = (int) $year;
    }

    /**
     * Returns the week.
     *
     * @return int
     */
    public function getWeek()
    {
        return $this->week;
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
     * Returns an ISO formatted date (YYYY-WW).
     *
     * @return string
     */
    public function getIso()
    {
        return sprintf('%04d-%02d', $this->year, $this->week);
    }

    /**
     * Returns the next week.
     *
     * @param int $offset The week offset.
     *
     * @return Ocal_Week
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

?>
