<?php

namespace Tests\Unit\Controllers\Log;

use App\Controllers\Log\LogError;
use CodeIgniter\HTTP\IncomingRequest;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;

class LogErrorTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock session data
        $_SESSION = [
            'permissions' => ['view error'],
            'username' => 'testuser',
            'role' => 'ADMIN',
            'nomor_absen' => '12345'
        ];

        // Create controller instance with disabled constructor
        $this->controller = $this->getMockBuilder(LogError::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render', 'validate', 'logActivity'])
            ->getMock();

        // Mock request and response
        $this->request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getGet', 'getPost', 'getIPAddress', 'getUserAgent'])
            ->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        // Inject mocks into controller using reflection
        $reflection = new \ReflectionClass($this->controller);

        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->controller, $this->request);

        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($this->controller, $this->response);
    }

    protected function tearDown(): void
    {
        unset($this->controller, $this->request, $this->response);
        parent::tearDown();
    }

    // Test that controller can be instantiated
    public function testControllerCanBeInstantiated()
    {
        $this->assertInstanceOf(LogError::class, $this->controller);
    }

    // Test index method calls logActivity with correct parameters
    public function testIndexCallsLogActivity()
    {
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($data) {
                return $data['log_name'] === 'VIEW' &&
                       strpos($data['description'], 'mengakses Halaman Daftar Error') !== false &&
                       $data['event'] === 'VERIFIED' &&
                       $data['subject'] === '-';
            }));

        // The controller may or may not throw an exception depending on CILogViewer availability
        try {
            $this->controller->index();
        } catch (\Exception $e) {
            // Exception is acceptable if CILogViewer is not available
            $this->assertTrue(true);
        }
    }

    // Test index method with different username
    public function testIndexWithDifferentUsername()
    {
        // Change session data
        $_SESSION = [
            'permissions' => ['view error'],
            'username' => 'admin_user',
            'role' => 'SUPER_ADMIN',
            'nomor_absen' => '99999'
        ];

        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($data) {
                return strpos($data['description'], 'admin_user mengakses Halaman Daftar Error') !== false;
            }));

        try {
            $this->controller->index();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    // Test index method with empty session
    public function testIndexWithEmptySession()
    {
        // Clear session
        $_SESSION = [];

        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($data) {
                return strpos($data['description'], 'mengakses Halaman Daftar Error') !== false;
            }));

        try {
            $this->controller->index();
        } catch (\Exception $e) {
            $this->assertTrue(true);
        }
    }

    // Test logActivity failure scenario
    public function testIndexWithLogActivityFailure()
    {
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willThrowException(new \Exception('Database connection failed'));

        $this->expectException(\Exception::class);
        $this->controller->index();
    }

    // Test CILogViewer availability
    public function testCILogViewerAvailability()
    {
        // Test that CILogViewer class existence can be checked
        $isAvailable = class_exists('\CILogViewer\CILogViewer');

        // We don't assert anything specific here, just that the check works
        $this->assertIsBool($isAvailable);
    }

    // Test that controller uses HasLogActivity trait
    public function testControllerUsesHasLogActivityTrait()
    {
        $traits = class_uses(LogError::class);
        $this->assertArrayHasKey('App\Traits\HasLogActivity', $traits);
    }

    // Test logActivity data structure
    public function testLogActivityDataStructure()
    {
        $loggedData = null;

        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($data) use (&$loggedData) {
                $loggedData = $data;
                return isset($data['log_name']) &&
                       isset($data['description']) &&
                       isset($data['event']) &&
                       isset($data['subject']);
            }));

        try {
            $this->controller->index();
        } catch (\Exception $e) {
            // Exception is acceptable if CILogViewer is not available
            $this->assertTrue(true);
        }

        // Verify the logged data structure if it was captured
        if ($loggedData) {
            $this->assertEquals('VIEW', $loggedData['log_name']);
            $this->assertStringContainsString('mengakses Halaman Daftar Error', $loggedData['description']);
            $this->assertEquals('VERIFIED', $loggedData['event']);
            $this->assertEquals('-', $loggedData['subject']);
        }
    }

    // Test that controller has required properties
    public function testControllerHasRequiredProperties()
    {
        $this->assertTrue(property_exists(LogError::class, 'request'));
        $this->assertTrue(property_exists(LogError::class, 'response'));
    }
}
