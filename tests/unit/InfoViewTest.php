<?php

/**
 * Copyright 2014-2017 Christoph M. Becker
 *
 * This file is part of Ocal_XH.
 *
 * Ocal_XH is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Ocal_XH is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Ocal_XH.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Ocal;

use PHPUnit_Framework_TestCase;
use PHPUnit_Extensions_MockFunction;

class InfoViewTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Controller
     */
    private $subject;

    public function setUp()
    {
        global $ocal, $o, $pth, $plugin_tx;

        $this->defineConstant('XH_ADM', true);
        $this->defineConstant('OCAL_VERSION', '1.0');
        $ocal = 'true';
        $o = '';
        $pth = array(
            'folder' => array('plugins' => './plugins/')
        );
        $plugin_tx = array(
            'ocal' => array('alt_logo' => 'Calendar')
        );
        $this->subject = new Controller();
        $rspmiMock = new PHPUnit_Extensions_MockFunction('XH_registerStandardPluginMenuItems', $this->subject);
        $printPluginAdminMock = new PHPUnit_Extensions_MockFunction('print_plugin_admin', $this->subject);
        $this->subject->dispatch();
    }

    public function testRendersHeading()
    {
        global $o;

        @$this->assertTag(
            array(
                'tag' => 'h1',
                'content' => 'Ocal'
            ),
            $o
        );
    }

    public function testRendersLogo()
    {
        global $o;

        @$this->assertTag(
            array(
                'tag' => 'img',
                'attributes' => array(
                    'src' => './plugins/ocal/ocal.png',
                    'class' => 'ocal_logo',
                    'alt' => 'Calendar'
                )
            ),
            $o
        );
    }

    public function testRendersVersion()
    {
        global $o;

        @$this->assertTag(
            array(
                'tag' => 'p',
                'content' => 'Version: ' . OCAL_VERSION
            ),
            $o
        );
    }

    public function testRendersCopyright()
    {
        global $o;

        @$this->assertTag(
            array(
                'tag' => 'p',
                'content' => "Copyright \xC2\xA9 2014-2017",
                'child' => array(
                    'tag' => 'a',
                    'attributes' => array(
                        'href' => 'http://3-magi.net/',
                        'target' => '_blank'
                    ),
                    'content' => 'Christoph M. Becker'
                )
            ),
            $o
        );
    }

    public function testRendersLicense()
    {
        global $o;

        @$this->assertTag(
            array(
                'tag' => 'p',
                'attributes' => array('class' => 'ocal_license'),
                'content' => 'This program is free software:'
            ),
            $o
        );
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
