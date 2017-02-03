<?php

/**
 * The abstract week views.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

/**
 * The abstract week views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class Ocal_WeekView
{
    /**
     * The week.
     *
     * @var Ocal_Week
     */
    protected $week;

    /**
     * The occupancy.
     *
     * @var Ocal_Occupancy $occupancy.
     */
    protected $occupancy;

    /**
     * Initializes a new instance.
     *
     * @param Ocal_Week      $week      A week.
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Week $week, Ocal_Occupancy $occupancy)
    {
        $this->week = $week;
        $this->occupancy = $occupancy;
    }

    /**
     * Returns a formatted date.
     *
     * @param int $day  A day.
     * @param int $hour An hour.
     *
     * @return string
     */
    protected function formatDate($day, $hour)
    {
        return sprintf(
            '%04d-%02d-%02d-%02d', $this->week->getYear(),
            $this->week->getWeek(), $day, $hour
        );
    }
}

?>
