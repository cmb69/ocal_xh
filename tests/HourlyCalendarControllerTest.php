<?php

namespace Ocal;

use DateTimeImmutable;
use ApprovalTests\Approvals;
use Ocal\Model\DailyOccupancy;
use Ocal\Model\Db;
use Ocal\Model\HourlyOccupancy;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
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

    /** @var DocumentStore */
    private $store;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method('tokenInput')->willReturn(
            '<input type="hidden" name="xh_csrf_token" value="dcfff515ebf5bd421d5a0777afc6358b">'
        );
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $config = $plugin_cf['ocal'];
        $this->listService = $this->createStub(ListService::class);
        $this->db = $this->createStub(Db::class);
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $this->sut = new HourlyCalendarController(
            "./",
            $this->csrfProtector,
            $config,
            $this->listService,
            $this->store,
            $this->view(),
            true,
            "test-hourly",
            1
        );
    }

    public function testReportsInvalidCalendarName(): void
    {
        $response = ($this->sut)(new FakeRequest(), "in valid", 1);
        $this->assertStringContainsString(
            "An occupancy name may only contain the letters a-z, the digits 0-9 and minus signs!",
            $response->output()
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $response = ($this->sut)(new FakeRequest(["admin" => true, "time" => 1688256000]), "test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
            "admin" => true,
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest(["header" => ["X-CMSimple-XH-Request" => "ocal"]]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testDefaultActionReportsWrongCalendarType(): void
    {
        DailyOccupancy::update("test-daily", $this->store);
        $this->store->commit();
        $response = ($this->sut)(new FakeRequest(), "test-daily", 1);
        $this->assertStringContainsString("'test-daily' is not a hourly occupancy calendar!", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
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
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list&ocal_name=test-hourly",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $this->listService->method('getHourlyList')->willReturn([]);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionReportsWrongCalendarType(): void
    {
        DailyOccupancy::update("test-daily", $this->store);
        $this->store->commit();
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_action=list"]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertStringContainsString("'test-daily' is not a hourly occupancy calendar!", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_action=save"]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->db->method('saveOccupancy')->willReturn(true);
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_name=save"]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->stringContains("Successfully saved.", $response->output());
    }

    public function testSaveActionPreventCsrf(): void
    {
        $this->csrfProtector->expects($this->once())->method('check');
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly&ocal_action=save",
            "admin" => true,
        ]);
        ($this->sut)($request, "test-hourly", 1);
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-hourly&ocal_action=save",
            "admin" => true,
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertEquals(400, $response->status());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        vfsStream::setQuota(0);
        $this->db->method('findOccupancy')->willReturn(new HourlyOccupancy("test-hourly", 3));
        $this->db->method('saveOccupancy')->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=save&ocal_name=test-hourly",
            "admin" => true,
            "post" => ["ocal_states" => json_encode(['2023-06' => array_fill(0, 90, "1")])],
        ]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertStringContainsString("Saving failed!", $response->output());
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["ocal"]);
    }
}
