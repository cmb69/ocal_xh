<?php

namespace Ocal;

use PHPUnit\Framework\TestCase;

class DicTest extends TestCase
{
    public function setUp(): void
    {
        global $pth, $plugin_cf, $plugin_tx;

        $pth = ["folder" => ["base" => "", "plugins" => ""]];
        $plugin_cf = ["ocal" => ["state_max" => ""]];
        $plugin_tx = ["ocal" => []];
    }

    public function testMakesDefaultAdminController(): void
    {
        $this->assertInstanceOf(DefaultAdminController::class, Dic::makeDefaultAdminController());
    }

    public function testMakesDailyCalendarController(): void
    {
        $this->assertInstanceOf(DailyCalendarController::class, Dic::makeDailyCalendarController());
    }

    public function testMakesHourlyCalendarController(): void
    {
        $this->assertInstanceOf(HourlyCalendarController::class, Dic::makeHourlyCalendarController());
    }
}
