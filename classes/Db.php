<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

class Db
{
    /**
     * @var resource
     */
    protected $lockFile;

    /**
     * @param int $lockMode
     */
    public function __construct($lockMode)
    {
        $lockFilename = $this->getFoldername() . '.lock';
        $this->lockFile = fopen($lockFilename, 'a');
        flock($this->lockFile, (int) $lockMode);
    }

    public function __destruct()
    {
        flock($this->lockFile, LOCK_UN);
    }

    /**
     * @param string $name
     * @param bool $hourly
     * @return Occupancy
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
                $occupancy = new HourlyOccupancy($name);
            } else {
                $occupancy = new Occupancy($name);
            }
        }
        return $occupancy;
    }

    public function saveOccupancy(Occupancy $occupancy)
    {
        $filename = $this->getFoldername() . $occupancy->getName() . '.dat';
        file_put_contents($filename, serialize($occupancy));
    }

    /**
     * @return string
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
