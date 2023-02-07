<?php

/**
 * Copyright 2023 Christoph M. Becker
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

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;

class DefaultAdminControllerTest extends TestCase
{
    public function testDefaultActionRendersPluginInfo(): void
    {
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['ocal'];
        $systemChecker = $this->createStub(SystemChecker::class);
        $systemChecker->method('checkVersion')->willReturn(false);
        $systemChecker->method('checkExtension')->willReturn(false);
        $systemChecker->method('checkWritability')->willReturn(false);
        $sut = new DefaultAdminController("./", "", $lang, $systemChecker);
        $response = $sut->defaultAction();
        Approvals::verifyHtml($response);
    }
}
