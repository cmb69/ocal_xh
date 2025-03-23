<?php

namespace Ocal;

use ApprovalTests\Approvals;
use PHPUnit\Framework\TestCase;
use Plib\FakeSystemChecker;
use Plib\View;

class DefaultAdminControllerTest extends TestCase
{
    public function testDefaultActionRendersPluginInfo(): void
    {
        $sut = new DefaultAdminController(
            "./",
            "",
            new FakeSystemChecker(),
            new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["ocal"])
        );
        $response = $sut->defaultAction();
        Approvals::verifyHtml($response);
    }
}
