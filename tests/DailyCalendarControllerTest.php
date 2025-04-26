<?php

namespace Ocal;

use ApprovalTests\Approvals;
use Ocal\Model\DailyOccupancy;
use Ocal\Model\Db;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
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
        $this->sut = new DailyCalendarController(
            "./",
            $this->csrfProtector,
            $config,
            $this->listService,
            $this->db,
            $this->store,
            $this->view(),
            true,
            "test-daily",
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
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $response = ($this->sut)(new FakeRequest(["admin" => true, "time" => 1688256000]), "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
            "admin" => true,
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest(["header" => ["X-CMSimple-XH-Request" => "ocal"]]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testDefaultActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $response = ($this->sut)(new FakeRequest(), "test-hourly", 1);
        $this->assertStringContainsString("'test-hourly' is not a daily occupancy calendar!", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithAnEntry(): void
    {
        $this->listService->method('getDailyList')->willReturn([
            (object) ['range' => "9.", 'state' => "2", 'label' => "available"],
        ]);
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list&ocal_name=test-daily",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
            "time" => 1688256000,
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionReportsWrongCalendarType(): void
    {
        $this->db->method('findOccupancy')->willReturn(null);
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_action=list"]);
        $response = ($this->sut)($request, "test-hourly", 1);
        $this->assertStringContainsString("'test-hourly' is not a daily occupancy calendar!", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_action=save"]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->db->method('saveOccupancy')->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
            "post" => ["ocal_states" => json_encode(['2023-02' => array_fill(0, 27, "1")])],
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertStringContainsString('Successfully saved.', $response->output());
    }

    public function testSaveActionPreventCsrf(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->csrfProtector->expects($this->once())->method('check');
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
        ]);
        ($this->sut)($request, "test-daily", 1);
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertEquals(400, $response->status());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        vfsStream::setQuota(0);
        $this->db->method('findOccupancy')->willReturn(new DailyOccupancy("test-daily", 3));
        $this->db->method('saveOccupancy')->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
            "post" => ["ocal_states" => json_encode(['2023-02' => array_fill(0, 27, "1")])]
        ]);
        $response = ($this->sut)($request, "test-daily", 1);
        $this->assertStringContainsString('Saving failed!', $response->output());
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["ocal"]);
    }
}
