<?php

namespace Ocal;

use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;
use Plib\DocumentStore;
use Plib\View;

class DailyCalendarControllerVisitorTest extends TestCase
{
    /** @see <https://github.com/cmb69/ocal_xh/issues/33> */
    public function testContructorDoesNotCrash(): void
    {
        vfsStream::setup("root");
        $plugin_cf = XH_includeVar("./config/config.php", 'plugin_cf');
        $config = $plugin_cf['ocal'];
        $listService = $this->createStub(ListService::class);
        $store = new DocumentStore(vfsStream::url("root/"));
        new DailyCalendarController(
            "./",
            null,
            $config,
            $listService,
            $store,
            $this->view(),
            false,
            "test-daily",
            1
        );
    }

    private function view(): View
    {
        return new View("./views/", XH_includeVar("./languages/en.php", "plugin_tx")["ocal"]);
    }
}
