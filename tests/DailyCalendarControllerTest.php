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

class DailyCalendarControllerTest extends TestCase
{
    /** @var DailyCalendarController */
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
        $this->listService = $this->createStub(ListService::class);
        $this->db = $this->createStub(Db::class);
        $this->sut = new DailyCalendarController(
            new Url("/", "", []),
            "./",
            $this->csrfProtector,
            $config,
            $this->listService,
            $this->db,
            $this->view(),
            true,
            "test-daily",
            1
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->defaultAction(new FakeRequest(["admin" => true, "time" => 1688256000]), "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_GET = ['ocal_name' => "test-daily"];
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->defaultAction(new FakeRequest(["admin" => true, "time" => 1688256000]), "test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->defaultAction(new FakeRequest(), "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testDefaultActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = $this->sut->defaultAction(new FakeRequest(), "test-hourly", 1);
        $this->assertStringContainsString("'test-hourly' is not a daily occupancy calendar!", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->listAction(new FakeRequest(["time" => 1688256000]), "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithAnEntry(): void
    {
        $this->listService->method('getDailyList')->willReturn([
            (object) ['range' => "9.", 'state' => "2", 'label' => "available"],
        ]);
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->listAction(new FakeRequest(["time" => 1688256000]), "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_GET = ['ocal_name' => "test-daily"];
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->listAction(new FakeRequest(["time" => 1688256000]), "test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->listAction(new FakeRequest(), "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = $this->sut->listAction(new FakeRequest(), "test-hourly", 1);
        $this->assertStringContainsString("'test-hourly' is not a daily occupancy calendar!", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->saveAction(new FakeRequest(), "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => json_encode(['2023-02' => array_fill(0, 27, "1")])];
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->db->method('saveOccupancy')->willReturn(true);
        $response = $this->sut->saveAction(new FakeRequest(["admin" => true]), "test-daily", 1);
        $this->assertStringContainsString('Successfully saved.', $response->output());
    }

    public function testSaveActionPreventCsrf(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => json_encode(['2023-02' => array_fill(0, 27, "1")])];
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->csrfProtector->expects($this->once())->method('check');
        $this->sut->saveAction(new FakeRequest(["admin" => true]), "test-daily", 1);
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => ""];
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = $this->sut->saveAction(new FakeRequest(["admin" => true]), "test-daily", 1);
        $this->assertEquals(400, $response->status());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => json_encode(['2023-02' => array_fill(0, 27, "1")])];
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->db->method('saveOccupancy')->willReturn(false);
        $response = $this->sut->saveAction(new FakeRequest(["admin" => true]), "test-daily", 1);
        $this->assertStringContainsString('Saving failed!', $response->output());
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["ocal"]);
    }
}
