<?php

/**
 * The abstract month views.
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
 * The abstract month views.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
abstract class Ocal_MonthView
{
    /**
     * The month.
     *
     * @var Ocal_Month
     */
    protected $month;

    /**
     * The occupancy.
     *
     * @var Ocal_Occupancy $occupancy.
     */
    protected $occupancy;

    /**
     * Initializes a new instance.
     *
     * @param Ocal_Month     $month     A month.
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function __construct(Ocal_Month $month, Ocal_Occupancy $occupancy)
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
