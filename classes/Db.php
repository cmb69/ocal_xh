<?php

/**
 * The database.
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
 * The database.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Db
{
    /**
     * The lock file handle.
     *
     * @var resource
     */
    protected $lockFile;

    /**
     * Initializes a new instance.
     *
     * @param int $lockMode A locking mode (LOCK_SH or LOCK_EX).
     */
    public function __construct($lockMode)
    {
        $lockFilename = $this->getFoldername() . '.lock';
        $this->lockFile = fopen($lockFilename, 'a');
        flock($this->lockFile, (int) $lockMode);
    }

    /**
     * Finalizes an instance.
     */
    public function __destruct()
    {
        flock($this->lockFile, LOCK_UN);
    }

    /**
     * Finds and returns an occupancy object.
     *
     * @param string $name   An occupancy name.
     * @param bool   $hourly Whether the occupancy is hourly.
     *
     * @return Ocal_Occupancy
     */
    public function findOccupancy($name, $hourly = false)
    {
        $filename = $this->getFoldername() . $name . '.dat';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
        } else {
            $contents = false;
        }
        if ($contents && ($occupancy = unserialize($contents))) {
            $occupancy->setName($name);
        } else {
            if ($hourly) {
                $occupancy = new Ocal_HourlyOccupancy($name);
            } else {
                $occupancy = new Ocal_Occupancy($name);
            }
        }
        return $occupancy;
    }

    /**
     * Saves an occupancy.
     *
     * @param Ocal_Occupancy $occupancy An occupancy.
     *
     * @return void
     */
    public function saveOccupancy(Ocal_Occupancy $occupancy)
    {
        $filename = $this->getFoldername() . $occupancy->getName() . '.dat';
        file_put_contents($filename, serialize($occupancy));
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

        $foldername = $pth['folder']['base'] . 'content/ocal/';
        if (!file_exists($foldername)) {
            mkdir($foldername, 0777, true);
            chmod($foldername, 0777);
        }
        return $foldername;
    }
}

?>
