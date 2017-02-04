<?php

/**
 * The abstract month views.
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

namespace Ocal;

/**
 * The abstract month views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class MonthView
{
    /**
     * The month.
     *
     * @var Month
     */
    protected $month;

    /**
     * The occupancy.
     *
     * @var Occupancy $occupancy.
     */
    protected $occupancy;

    /**
     * Initializes a new instance.
     *
     * @param Month     $month     A month.
     * @param Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Month $month, Occupancy $occupancy)
    {
        $this->month = $month;
        $this->occupancy = $occupancy;
    }

    /**
     * Returns a formatted date.
     *
     * @param int $day A day number.
     *
     * @return string
     */
    protected function formatDate($day)
    {
        return sprintf(
            '%04d-%02d-%02d', $this->month->getYear(),
            $this->month->getMonth(), $day
        );
    }
}

?>
