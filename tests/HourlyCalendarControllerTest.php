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
use Plib\FakeRequest;
use Plib\View;
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
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="dcfff515ebf5bd421d5a0777afc6358b">'
        );
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $config = $plugin_cf['ocal'];
        $this->listService = $this->createStub(ListService::class);
        $this->db = $this->createStub(Db::class);
        $this->sut = new HourlyCalendarController(
            "./",
            $this->csrfProtector,
            $config,
            $this->listService,
            $this->db,
            $this->view(),
            true,
            "test-hourly",
            1
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->defaultAction(new FakeRequest(["admin" => true, "time" => 1688256000]), "test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly",
            "header" => ["X-Requested-With" => "XMLHttpRequest"],
            "admin" => true,
            "time" => 1688256000,
        ]);
        $response = $this->sut->defaultAction($request, "test-hourly", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest(["header" => ["X-Requested-With" => "XMLHttpRequest"]]);
        $response = $this->sut->defaultAction($request, "test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testDefaultActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = $this->sut->defaultAction(new FakeRequest(), "test-daily", 1);
        $this->assertStringContainsString("'test-daily' is not a hourly occupancy calendar!", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->listAction(new FakeRequest(["time" => 1688256000]), "test-hourly", 1);
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
        $response = $this->sut->listAction(new FakeRequest(["time" => 1688256000]), "test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly",
            "header" => ["X-Requested-With" => "XMLHttpRequest"],
            "time" => 1688256000,
        ]);
        $response = $this->sut->listAction($request, "test-hourly", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest(["header" => ["X-Requested-With" => "XMLHttpRequest"]]);
        $response = $this->sut->listAction($request, "test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = $this->sut->listAction(new FakeRequest(), "test-daily", 1);
        $this->assertStringContainsString("'test-daily' is not a hourly occupancy calendar!", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = $this->sut->saveAction(new FakeRequest(), "test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->db->method('saveOccupancy')->willReturn(true);
        $response = $this->sut->saveAction(new FakeRequest(), "test-hourly", 1);
        $this->stringContains("Successfully saved.", $response->output());
    }

    public function testSaveActionPreventCsrf(): void
    {
        $this->csrfProtector->expects($this->once())->method('check');
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly",
            "admin" => true,
        ]);
        $this->sut->saveAction($request, "test-hourly", 1);
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly",
            "admin" => true,
        ]);
        $response = $this->sut->saveAction($request, "test-hourly", 1);
        $this->assertEquals(400, $response->status());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->db->method('saveOccupancy')->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly",
            "admin" => true,
            "post" => ["ocal_states" => json_encode(['2023-06' => array_fill(0, 90, "1")])],
        ]);
        $response = $this->sut->saveAction($request, "test-hourly", 1);
        $this->assertStringContainsString("Saving failed!", $response->output());
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["ocal"]);
    }
}
