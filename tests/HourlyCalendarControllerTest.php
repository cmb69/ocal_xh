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

    /** @var CsrfProtector&MockObject */
    private $csrfProtector;

    /** @var ListService&MockObject */
    private $listService;

    /** @var Db&MockObject */
    private $db;

    public function setUp(): void
    {
        $_SERVER['QUERY_STRING'] = "";
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="dcfff515ebf5bd421d5a0777afc6358b">'
        );
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $config = $plugin_cf['ocal'];
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['ocal'];
        $now = new DateTimeImmutable("2023-07-02");
        $this->listService = $this->createStub(ListService::class);
        $this->db = $this->createStub(Db::class);
        $this->sut = new HourlyCalendarController(
            new Url("/", "", []),
            "./",
            $this->csrfProtector,
            $config,
            $lang,
            $now,
            $this->listService,
            $this->db,
            true,
            "test-hourly",
            1
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->defaultAction("test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_GET = ['ocal_name' => "test-hourly"];
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->defaultAction("test-hourly", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->defaultAction("test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testDefaultActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = $this->sut->defaultAction("test-daily", 1);
        $this->assertStringContainsString("'test-daily' is not a hourly occupancy calendar!", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
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
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->listAction("test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_GET = ['ocal_name' => "test-hourly"];
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->listAction("test-hourly", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->listAction("test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = $this->sut->listAction("test-daily", 1);
        $this->assertStringContainsString("'test-daily' is not a hourly occupancy calendar!", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->saveAction("test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $_GET = ['ocal_name' => "test-hourly"];
        $_POST = ['ocal_states' => json_encode(['2023-06' => array_fill(0, 90, "1")])];
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->db->method('saveOccupancy')->willReturn(true);
        $response = $this->sut->saveAction("test-hourly", 1);
        $this->stringContains("Successfully saved.", $response->output());
    }

    public function testSaveActionPreventCsrf(): void
    {
        $_GET = ['ocal_name' => "test-hourly"];
        $_POST = ['ocal_states' => json_encode(['2023-06' => array_fill(0, 90, "1")])];
        $this->csrfProtector->expects($this->once())->method('check');
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->sut->saveAction("test-hourly", 1);
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $_GET = ['ocal_name' => "test-hourly"];
        $_POST = ['ocal_states' => ""];
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->saveAction("test-hourly", 1);
        $this->assertEquals(400, $response->status());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        $_GET = ['ocal_name' => "test-hourly"];
        $_POST = ['ocal_states' => json_encode(['2023-06' => array_fill(0, 90, "1")])];
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->db->method('saveOccupancy')->willReturn(false);
        $response = $this->sut->saveAction("test-hourly", 1);
        $this->assertStringContainsString("Saving failed!", $response->output());
    }
}
