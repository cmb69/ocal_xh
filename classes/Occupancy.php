<?php

/**
 * The occupancies.
 *
 * PHP version 5
 *
 * @category  CMSimple_XH
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014-2015 Christoph M. Becker <http://3-magi.net/>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

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
