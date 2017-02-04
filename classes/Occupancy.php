<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

use Serializable;

class Occupancy implements Serializable
{
    /**
     * @var string
     */
    protected $name;

    /**
     * @var array<date,state>
     */
    protected $states;

    /**
     * @param string $name
     */
    public function __construct($name)
    {
        $this->name = (string) $name;
        $this->states = array();
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param string $name
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @param string $date
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
     * @param string $date
     * @param int $state
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
     * @return string
     */
    public function serialize()
    {
        return serialize($this->states);
    }

    /**
     * @param string $data
     */
    public function unserialize($data)
    {
        $this->states = unserialize($data);
    }
}
