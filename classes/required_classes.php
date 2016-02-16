<?php

/**
 * The autoloader.
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
 * Autoloads the plugin classes.
 *
 * @param string $class A class name.
 *
 * @return void
 */
function Ocal_autoload($class)
{
    $parts = explode('_', $class, 2);
    if ($parts[0] == 'Ocal') {
        include_once dirname(__FILE__) . '/' . $parts[1] . '.php';
    }
}

spl_autoload_register('Ocal_autoload');

?>
