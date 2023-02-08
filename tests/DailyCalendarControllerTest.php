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

use DateTime;
use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use XH\CSRFProtection as CsrfProtector;

class DailyCalendarControllerTest extends TestCase
{
    /** @var DailyCalendarController */
    private $sut;

    /** @var ListService&MockObject */
    private $listService;

    public function setUp(): void
    {
        global $plugin_cf;

        $_SERVER['QUERY_STRING'] = "";
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $config = $plugin_cf['ocal'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['ocal'];
        $now = new DateTime("2023-07-02");
        $this->listService = $this->createStub(ListService::class);
        $db = $this->createStub(Db::class);
        $db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily"));
        $this->sut = new DailyCalendarController(
            "/",
            "./",
            $csrfProtector,
            $config,
            $lang,
            $now,
            $this->listService,
            $db,
            true,
            "test-daily",
            1
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $response = $this->sut->defaultAction();
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $response = $this->sut->ListAction();
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithAnEntry(): void
    {
        $this->listService->method('getDailyList')->willReturn([
            (object) ['range' => "9.", 'state' => "2", 'label' => "available"],
        ]);
        $response = $this->sut->ListAction();
        Approvals::verifyHtml($response->output());
    }

    public function testSaveActionReturnsEmptyResponseWhenNotLoggedIn(): void
    {
        $response = $this->sut->SaveAction();
        $this->assertEquals("", $response->output());
    }
}
