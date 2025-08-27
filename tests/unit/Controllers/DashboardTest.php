<?php

namespace Tests\Unit\Controllers;

use App\Controllers\Dashboard;
use CodeIgniter\Test\CIUnitTestCase;

class DashboardTest extends CIUnitTestCase
{
    public function testControllerCanBeInstantiated()
    {
        $dashboard = new Dashboard();
        $this->assertInstanceOf(Dashboard::class, $dashboard);
    }

    public function testControllerUsesHasCurlRequestTrait()
    {
        $traits = class_uses(Dashboard::class);
        $this->assertArrayHasKey("App\Traits\HasCurlRequest", $traits);
    }

    public function testControllerExtendsBaseController()
    {
        $dashboard = new Dashboard();
        $this->assertInstanceOf(\App\Controllers\BaseController::class, $dashboard);
    }

    public function testControllerHasRequiredProperties()
    {
        $dashboard = new Dashboard();
        $this->assertTrue(property_exists($dashboard, "request"));
        $this->assertTrue(property_exists($dashboard, "response"));
    }
}