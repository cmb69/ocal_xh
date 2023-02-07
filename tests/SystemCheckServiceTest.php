<?php

/**
 * Copyright 2017 Christoph M. Becker
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

use PHPUnit\Framework\TestCase;
use stdClass;
use org\bovigo\vfs\vfsStreamWrapper;
use org\bovigo\vfs\vfsStreamDirectory;
use org\bovigo\vfs\vfsStream;

class SystemCheckServiceTest extends TestCase
{
    /**
     * @var SystemCheckService
     */
    private $subject;

    public function setUp(): void
    {
        global $plugin_tx;

        define('CMSIMPLE_XH_VERSION', 'CMSimple_XH 1.7.5');
        $this->setUpLanguage();
        $this->setUpVfs();
        $this->subject = new SystemCheckService(
            vfsStream::url('test/plugins/'),
            vfsStream::url('test/'),
            $plugin_tx['ocal'],
            new SystemChecker()
        );
    }

    private function setUpLanguage()
    {
        global $plugin_tx;

        $plugin_tx['ocal'] = array(
            'syscheck_extension' => 'extension',
            'syscheck_phpversion' => 'phpversion',
            'syscheck_writable' => 'writable',
            'syscheck_xhversion' => 'xhversion',
            'syscheck_success' => 'success',
            'syscheck_warning' => 'warning',
            'syscheck_fail' => 'fail'
        );
    }

    private function setUpVfs()
    {
        vfsStreamWrapper::register();
        vfsStreamWrapper::setRoot(new vfsStreamDirectory('test'));
        mkdir(vfsStream::url('test/content/ocal/'), 0777, true);
    }

    public function testGetChecks()
    {
        $actual = $this->subject->getChecks();
        $this->assertContainsOnlyInstancesOf(stdClass::class, $actual);
        $this->assertCount(8, $actual);
        $this->assertSame('phpversion', $actual[0]->label);
        $this->assertSame('success', $actual[0]->state);
        $this->assertSame('extension', $actual[1]->label);
        $this->assertSame('success', $actual[1]->state);
        $this->assertSame('xhversion', $actual[3]->label);
        $this->assertSame('success', $actual[3]->state);
        $this->assertSame('writable', $actual[4]->label);
        $this->assertSame('success', $actual[4]->state);
    }
}
