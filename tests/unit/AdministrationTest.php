<?php

/**
 * @copyright 2014-2017 Christoph M. Becker <http://3-magi.net>
 * @license http://www.gnu.org/licenses/gpl-3.0.en.html GNU GPLv3
 */

namespace Ocal;

require_once './vendor/autoload.php';
require_once '../../cmsimple/adminfuncs.php';

use PHPUnit_Framework_TestCase;
use PHPUnit_Extensions_MockFunction;

class AdministrationTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Controller
     */
    private $subject;

    /**
     * @var object
     */
    private $rspmiMock;

    public function setUp()
    {
        global $ocal, $admin, $action;

        $this->defineConstant('XH_ADM', true);
        $ocal = 'true';
        $admin = 'plugin_stylesheet';
        $action = 'plugin_text';
        $this->subject = new Controller();
        $this->rspmiMock = new PHPUnit_Extensions_MockFunction('XH_registerStandardPluginMenuItems', $this->subject);
        $printPluginAdminMock = new PHPUnit_Extensions_MockFunction('print_plugin_admin', $this->subject);
        $printPluginAdminMock->expects($this->once())->with('off');
        $pluginAdminCommonMock = new PHPUnit_Extensions_MockFunction('plugin_admin_common', $this->subject);
        $pluginAdminCommonMock->expects($this->once())
            ->with($action, $admin, 'ocal');
    }

    public function testShowsIntegratedPluginMenu()
    {
        $this->rspmiMock->expects($this->once())->with(false);
        $this->subject->dispatch();
    }

    public function testStylesheet()
    {
        $this->subject->dispatch();
    }

    /**
     * @param string $name
     * @param string $value
     */
    private function defineConstant($name, $value)
    {
        if (!defined($name)) {
            define($name, $value);
        } else {
            runkit_constant_redefine($name, $value);
        }
    }
}
