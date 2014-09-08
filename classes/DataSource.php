<?php

/**
 * The data source layer.
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
     * @param string $name An occupancy name.
     *
     * @return Ocal_Occupancy
     */
    public function findOccupancy($name)
    {
        $filename = $this->getFoldername() . $name . '.dat';
        if (is_readable($filename)) {
            $contents = file_get_contents($filename);
        } else {
            $contents = false;
        }
        if ($contents) {
            // FIXME: error check
            $occupancy = unserialize($contents);
            $occupancy->setName($name);
        } else {
            $occupancy = new Ocal_Occupancy($name);
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

        $foldername = $pth['folder']['content'] . 'ocal/';
        if (!file_exists($foldername)) {
            mkdir($foldername, 0777, true);
            chmod($foldername, 0777);
        }
        return $foldername;
    }
}

/**
 * The occupancies.
 *
 * @category CMSimple_XH
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class Ocal_Occupancy implements Serializable
{
    /**
     * The name.
     *
     * @var string
     */
    protected $name;

    /**
     * The states.
     *
     * @var array<date,state>
     */
    protected $states;

    /**
     * Initializes a new instance.
     *
     * @param string $name A name.
     *
     * @return void
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
        $this->states = array();
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name.
     *
     * @param string $name A name.
     *
     * @return void
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

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
        if ($state) {
            $this->states[$date] = $state;
        } else {
            unset($this->states[$date]);
        }
    }

    /**
     * Returns the serialized representation.
     *
     * @return string
     */
    public function serialize()
    {
        return serialize($this->states);
    }

    /**
     * Sets the internal state.
     *
     * @param string $data A serialized representation.
     *
     * @return void
     */
    public function unserialize($data)
    {
        $this->states = unserialize($data);
    }
}

?>
