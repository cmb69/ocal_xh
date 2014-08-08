<?php

/**
 * Testing the general plugin administration.
 *
 * PHP version 5
 *
 * @category  Testing
 * @package   Ocal
 * @author    Christoph M. Becker <cmbecker69@gmx.de>
 * @copyright 2014 Christoph M. Becker <http://3-magi.net>
 * @license   http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @version   SVN: $Id$
 * @link      http://3-magi.net/?CMSimple_XH/Ocal_XH
 */

require_once './vendor/autoload.php';
require_once '../../cmsimple/adminfuncs.php';
require_once './classes/Presentation.php';

/**
 * Testing the general plugin administration.
 *
 * @category Testing
 * @package  Ocal
 * @author   Christoph M. Becker <cmbecker69@gmx.de>
 * @license  http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 * @link     http://3-magi.net/?CMSimple_XH/Ocal_XH
 */
class AdministrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * The test subject.
     *
     * @var Ocal_Controller
     */
    private $_subject;

    /**
     * The XH_registerStandardPluginMenuItems() mock.
     *
     * @var object
     */
    private $_rspmiMock;

    /**
     * Sets up the test fixture.
     *
     * @return void
     *
     * @global string Whether the plugin administration is requested.
     * @global string The value of the <var>admin</var> GP parameter.
     * @global string The value of the <var>action</var> GP parameter.
     */
    public function setUp()
    {
        global $ocal, $admin, $action;

        $this->_defineConstant('XH_ADM', true);
        $ocal = 'true';
        $admin = 'plugin_stylesheet';
        $action = 'plugin_text';
        $this->_subject = new Ocal_Controller();
        $this->_rspmiMock = new PHPUnit_Extensions_MockFunction(
            'XH_registerStandardPluginMenuItems', $this->_subject
        );
        $printPluginAdminMock = new PHPUnit_Extensions_MockFunction(
            'print_plugin_admin', $this->_subject
        );
        $printPluginAdminMock->expects($this->once())->with('off');
        $pluginAdminCommonMock = new PHPUnit_Extensions_MockFunction(
            'plugin_admin_common', $this->_subject
        );
        $pluginAdminCommonMock->expects($this->once())
            ->with($action, $admin, 'ocal');
    }

    /**
     * Tests that the integrated plugin menu is shown.
     *
     * @return void
     */
    public function testShowsIntegratedPluginMenu()
    {
        $this->_rspmiMock->expects($this->once())->with(false);
        $this->_subject->dispatch();
    }

    /**
     * Tests the stylesheet administration.
     *
     * @return void
     */
    public function testStylesheet()
    {
        $this->_subject->dispatch();
    }

    /**
     * (Re)defines a constant.
     *
     * @param string $name  A name.
     * @param string $value A value.
     *
     * @return void
     */
    private function _defineConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }
}

?>