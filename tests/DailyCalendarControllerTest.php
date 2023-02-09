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
        $plugin_tx = XH_includeVar("./languages/en.php", 'plugin_tx');
        $lang = $plugin_tx['ocal'];
        $now = new DateTimeImmutable("2023-07-02");
        $this->listService = $this->createStub(ListService::class);
        $this->db = $this->createStub(Db::class);
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->sut = new DailyCalendarController(
            "/",
            "./",
            $this->csrfProtector,
            $config,
            $lang,
            $now,
            $this->listService,
            $this->db,
            true,
            "test-daily",
            1
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $response = $this->sut->defaultAction("test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_GET = ['ocal_name' => "test-daily"];
        $response = $this->sut->defaultAction("test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }


    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $response = $this->sut->defaultAction("test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $response = $this->sut->listAction("test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithAnEntry(): void
    {
        $this->listService->method('getDailyList')->willReturn([
            (object) ['range' => "9.", 'state' => "2", 'label' => "available"],
        ]);
        $response = $this->sut->listAction("test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $_GET = ['ocal_name' => "test-daily"];
        $response = $this->sut->listAction("test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $_SERVER['HTTP_X_REQUESTED_WITH'] = 'XMLHttpRequest';
        $response = $this->sut->listAction("test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $response = $this->sut->saveAction("test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => json_encode(['2023-02' => array_fill(0, 27, "1")])];
        $this->db->method('saveOccupancy')->willReturn(true);
        $response = $this->sut->saveAction("test-daily", 1);
        $this->assertEquals('<p class="xh_success">Successfully saved.</p>', $response->output());
    }

    public function testSaveActionPreventCsrf(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => json_encode(['2023-02' => array_fill(0, 27, "1")])];
        $this->csrfProtector->expects($this->once())->method('check');
        $this->sut->saveAction("test-daily", 1);
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => ""];
        $response = $this->sut->saveAction("test-daily", 1);
        $this->assertEquals(400, $response->statuscode());
        $this->assertEquals("text/plain", $response->contentType());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        $_GET = ['ocal_name' => "test-daily"];
        $_POST = ['ocal_states' => json_encode(['2023-02' => array_fill(0, 27, "1")])];
        $this->db->method('saveOccupancy')->willReturn(false);
        $response = $this->sut->saveAction("test-daily", 1);
        $this->assertEquals('<p class="xh_fail">Saving failed!</p>', $response->output());
    }
}
