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

use DateTimeImmutable;
use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use XH\CSRFProtection as CsrfProtector;

class HourlyCalendarControllerTest extends TestCase
{
    /** @var HourlyCalendarController */
    private $sut;

    /** @var ListService&MockObject */
    private $listService;

    public function setUp(): void
    {
        global $plugin_cf;

        $_SERVER['QUERY_STRING'] = "";
        $csrfProtector = $this->createStub(CsrfProtector::class);
        $csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="dcfff515ebf5bd421d5a0777afc6358b">'
        );
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $config = $plugin_cf['ocal'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['ocal'];
        $now = new DateTimeImmutable("2023-07-02");
        $this->listService = $this->createStub(ListService::class);
        $db = $this->createStub(Db::class);
        $db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->sut = new HourlyCalendarController(
            "/",
            "./",
            $csrfProtector,
            $config,
            $lang,
            $now,
            $this->listService,
            $db,
            true,
            "test-hourly",
            1
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $response = $this->sut->defaultAction("test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $response = $this->sut->listAction("test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithAnEntry(): void
    {
        $this->listService->method('getHourlyList')->willReturn([
            (object) ['date' => new DateTimeImmutable("2023-02-10T11:00"), 'list' => [
                (object) ['range' => "12:00-13:00", 'state' => "1", 'label' => "reserved"],
            ]],
        ]);
        $response = $this->sut->listAction("test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testSaveActionReturnsEmptyResponseWhenNotLoggedIn(): void
    {
        $response = $this->sut->saveAction("test-hourly", 1);
        $this->assertEquals("", $response->output());
    }
}
