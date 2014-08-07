<?php

/**
 * The data source layer.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Bcal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Bcal_XH
 */

/**
 * The database.
 *
 * @category CMSimple_XH
 * @package  Bcal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Bcal_XH
 */
class Bcal_Db
{
    /**
     * Finds and returns an occupancy object.
     *
     * @return Bcal_Occupancy
     */
    public function findOccupancy()
    {
        $filename = $this->getFoldername() . 'test.dat';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
        } else {
            $contents = false;
        }
        if ($contents) {
            return unserialize($contents); // FIXME: error check
        } else {
            return new Bcal_Occupancy();
        }
    }

    /**
     * Returns the data foldername.
     *
     * @return string
     *
     * @global array The paths of system files and folders.
     */
    protected function getFoldername()
    {
        global $pth;

        $foldername = $pth['folder']['content'] . 'occupancy/';
        if (!file_exists($foldername)) {
            mkdir($foldername, 0777, true);
        }
        return $foldername;
    }
}

/**
 * The occupancies.
 *
 * @category CMSimple_XH
 * @package  Bcal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Bcal_XH
 */
class Bcal_Occupancy
{
    /**
     * The states.
     *
     * @var array<date,state>
     */
    protected $states;

    /**
     * Returns a state.
     *
     * @param string $date An ISO date (YYYY-MM-DD).
     *
     * @return int
     */
    public function getState($date)
    {
        if (isset($this->states[$date])) {
            return $this->states[$date];
        } else {
            return 0;
        }
    }

    /**
     * Sets a state.
     *
     * @param string $date  An ISO date (YYYY-MM-DD).
     * @param int    $state A state.
     *
     * @return void
     */
    public function setState($date, $state)
    {
        $this->states[$date] = $state;
    }
}

?>
