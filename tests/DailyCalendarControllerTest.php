<?php

namespace Ocal;

use ApprovalTests\Approvals;
use Ocal\Model\DailyOccupancy;
use Ocal\Model\HourlyOccupancy;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\MockObject\Stub;
use PHPUnit\Framework\TestCase;
use Plib\CsrfProtector;
use Plib\DocumentStore;
use Plib\FakeRequest;
use Plib\View;

class DailyCalendarControllerTest extends TestCase
{
    /** @var CsrfProtector&Stub */
    private $csrfProtector;

    /** @var array<string,string> */
    private $config;

    /** @var array<string,string> */
    private $lang;

    /** @var ListService*/
    private $listService;

    /** @var DocumentStore */
    private $store;

    /** @var View */
    private $view;

    public function setUp(): void
    {
        vfsStream::setup("root");
        $this->csrfProtector = $this->createStub(CsrfProtector::class);
        $this->csrfProtector->method('token')->willReturn("dcfff515ebf5bd421d5a0777afc6358b");
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $this->config = $plugin_cf['ocal'];
        $this->lang = XH_includeVar("./languages/en.php", "plugin_tx")["ocal"];
        $this->listService = new ListService($this->config, $this->lang);
        $this->store = new DocumentStore(vfsStream::url("root/"));
        $this->view = new View("./views/", $this->lang);
    }

    private function sut(): DailyCalendarController
    {
        return new DailyCalendarController(
            "./",
            $this->csrfProtector,
            $this->config,
            $this->listService,
            $this->store,
            $this->view
        );
    }

    public function testReportsInvalidCalendarName(): void
    {
        $response = $this->sut()(new FakeRequest(), "in valid", 1);
        $this->assertStringContainsString(
            "An occupancy name may only contain the letters a-z, the digits 0-9 and minus signs!",
            $response->output()
        );
    }

    public function testDefaultActionRendersCalendar(): void
    {
        $response = $this->sut()(new FakeRequest(["admin" => true, "time" => 1688256000]), "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionHandlesAjaxRequest(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
            "admin" => true,
            "time" => 1688256000,
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testDefaultActionIgnoresUnrelatedAjaxRequest(): void
    {
        $request = new FakeRequest(["header" => ["X-CMSimple-XH-Request" => "ocal"]]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testDefaultActionReportsWrongCalendarType(): void
    {
        HourlyOccupancy::update("test-hourly", $this->store);
        $this->store->commit();
        $response = $this->sut()(new FakeRequest(), "test-hourly", 1);
        $this->assertStringContainsString("'test-hourly' is not a daily occupancy calendar!", $response->output());
    }

    public function testListActionRendersListWithoutEntries(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "time" => 1688256000,
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionRendersListWithAnEntry(): void
    {
        $occupancy = DailyOccupancy::update("test-daily", $this->store);
        $occupancy->setState("2023-07-09", 1, 3);
        $this->store->commit();
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "time" => strtotime("2023-07-02T00:00:00+00:00"),
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        Approvals::verifyHtml($response->output());
    }

    public function testListActionHandlesAjaxRequest(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list&ocal_name=test-daily",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
            "time" => 1688256000,
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertEquals("text/html", $response->contentType());
        Approvals::verifyHtml($response->output());
    }

    public function testListActionIgnoresUnrelatedAjaxRequest(): void
    {
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_action=list",
            "header" => ["X-CMSimple-XH-Request" => "ocal"],
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testListActionReportsWrongCalendarType(): void
    {
        HourlyOccupancy::update("test-hourly", $this->store);
        $this->store->commit();
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_action=list"]);
        $response = $this->sut()($request, "test-hourly", 1);
        $this->assertStringContainsString("'test-hourly' is not a daily occupancy calendar!", $response->output());
    }

    public function testSaveActionReturnsEmptyResponseIfNameIsMissing(): void
    {
        $request = new FakeRequest(["url" => "http://example.com/?&ocal_action=save"]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionReportsSuccess(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
            "post" => [
                "ocal_states" => json_encode(['2023-02' => array_fill(0, 27, "1")]),
                "ocal_checksum" => "da39a3ee5e6b4b0d3255bfef95601890afd80709",
            ],
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertStringContainsString('Successfully saved.', $response->output());
    }

    public function testSaveActionPreventsCsrf(): void
    {
        $this->csrfProtector->method("check")->willReturn(false);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertStringContainsString("You are not authorized for this action!", $response->output());
    }

    public function testSaveActionRejectsBadRequest(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertEquals(400, $response->status());
        $this->assertEquals("", $response->output());
    }

    public function testSaveActionRejectsConflicts(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
            "post" => [
                "ocal_states" => json_encode(['2023-02' => array_fill(0, 27, "1")]),
                "ocal_checksum" => "da39a3ee5e6b4b0d3255bfef95601890afd8070a",
            ]
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertStringContainsString("The occupancy has been changed in the meantime!", $response->output());
    }

    public function testSaveActionReportsFailureToSave(): void
    {
        $this->csrfProtector->method("check")->willReturn(true);
        vfsStream::setQuota(0);
        $request = new FakeRequest([
            "url" => "http://example.com/?&ocal_name=test-daily&ocal_action=save",
            "admin" => true,
            "post" => [
                "ocal_states" => json_encode(['2023-02' => array_fill(0, 27, "1")]),
                "ocal_checksum" => "da39a3ee5e6b4b0d3255bfef95601890afd80709",
            ]
        ]);
        $response = $this->sut()($request, "test-daily", 1);
        $this->assertStringContainsString('Saving failed!', $response->output());
    }
}
