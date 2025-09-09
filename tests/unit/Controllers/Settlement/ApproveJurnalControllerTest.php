<?php

namespace Tests\Unit\Controllers\Settlement;

use App\Controllers\Settlement\ApproveJurnalController;
use App\Models\ProsesModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class ApproveJurnalControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $mockProsesModel;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();

        // Mock ProsesModel
        $this->mockProsesModel = $this->getMockBuilder(ProsesModel::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getDefaultDate'])
            ->getMock();

        // Create controller instance with all methods mocked (Complete Method Mocking)
        $this->controller = $this->getMockBuilder(ApproveJurnalController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render', 'datatable', 'getDetailJurnal', 'processApproval', 'logActivity'])
            ->getMock();

        // Use reflection to set protected properties
        $reflection = new \ReflectionClass($this->controller);
        $prosesModelProperty = $reflection->getProperty('prosesModel');
        $prosesModelProperty->setAccessible(true);
        $prosesModelProperty->setValue($this->controller, $this->mockProsesModel);

        // Mock request and response
        $this->request = $this->getMockBuilder(\CodeIgniter\HTTP\IncomingRequest::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['getGet', 'getPost'])
            ->getMock();
        $this->response = $this->getMockBuilder(ResponseInterface::class)
            ->getMock();

        // Inject mocks into controller using reflection
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($this->controller, $this->request);

        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($this->controller, $this->response);
    }

    public function testIndexWithTanggalParameter()
    {
        $tanggal = '2025-08-27';
        $statusApprove = '';

        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturnCallback(function($key) use ($tanggal) {
                switch($key) {
                    case 'tanggal': return $tanggal;
                    default: return null;
                }
            });

        $expectedData = [
            'title' => 'Approve Jurnal Settlement',
            'tanggalRekon' => $tanggal,
            'statusApprove' => $statusApprove,
            'route' => 'settlement/approve-jurnal'
        ];

        // Mock logActivity to prevent database calls
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/approve_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    public function testIndexWithDefaultDate()
    {
        $defaultDate = '2025-08-27';

        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturnCallback(function($key) {
                return null;
            });

        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn($defaultDate);

        $expectedData = [
            'title' => 'Approve Jurnal Settlement',
            'tanggalRekon' => $defaultDate,
            'statusApprove' => '',
            'route' => 'settlement/approve-jurnal'
        ];

        // Mock logActivity to prevent database calls
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/approve_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    public function testIndexWithStatusApproveParameter()
    {
        $tanggal = '2025-08-27';
        $statusApprove = '0';

        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturnCallback(function($key) use ($tanggal, $statusApprove) {
                switch($key) {
                    case 'tanggal': return $tanggal;
                    case 'status_approve': return $statusApprove;
                    default: return null;
                }
            });

        $expectedData = [
            'title' => 'Approve Jurnal Settlement',
            'tanggalRekon' => $tanggal,
            'statusApprove' => $statusApprove,
            'route' => 'settlement/approve-jurnal'
        ];

        // Mock logActivity to prevent database calls
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/approve_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    public function testDatatableMethodExists()
    {
        // Test that the method exists and can be called
        $this->assertTrue(method_exists($this->controller, 'datatable'));
    }

    public function testGetDetailJurnalMethodExists()
    {
        // Test that the method exists and can be called
        $this->assertTrue(method_exists($this->controller, 'getDetailJurnal'));
    }

    public function testProcessApprovalMethodExists()
    {
        // Test that the method exists and can be called
        $this->assertTrue(method_exists($this->controller, 'processApproval'));
    }

    public function testControllerHasLogActivityTrait()
    {
        // Test that the original controller class uses the HasLogActivity trait
        $reflection = new \ReflectionClass(ApproveJurnalController::class);
        $traits = $reflection->getTraitNames();
        $this->assertContains('App\Traits\HasLogActivity', $traits);
    }

    // Test edge cases and error scenarios
    public function testIndexWithEmptyParametersUsesDefaults()
    {
        $defaultDate = '2025-08-27';

        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturn(null);

        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn($defaultDate);

        $expectedData = [
            'title' => 'Approve Jurnal Settlement',
            'tanggalRekon' => $defaultDate,
            'statusApprove' => '',
            'route' => 'settlement/approve-jurnal'
        ];

        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($logData) {
                return $logData['log_name'] === 'VIEW' && 
                       str_contains($logData['description'], 'mengakses Halaman Approve Jurnal Settlement');
            }))
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/approve_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    public function testIndexLogActivityCalledWithCorrectParameters()
    {
        $tanggal = '2025-08-27';
        
        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturnCallback(function($key) use ($tanggal) {
                return $key === 'tanggal' ? $tanggal : null;
            });

        // Test that logActivity is called with correct log structure
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($logData) {
                return isset($logData['log_name']) && 
                       isset($logData['description']) && 
                       isset($logData['event']) && 
                       isset($logData['subject']) &&
                       $logData['subject'] === '-';
            }))
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->willReturn('rendered view');

        $this->controller->index();
    }

    // Test that all required public methods exist (Contract Testing)
    public function testControllerHasRequiredMethods()
    {
        $requiredMethods = ['index', 'datatable', 'getDetailJurnal', 'processApproval', 'getSummary'];
        
        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                method_exists(ApproveJurnalController::class, $method),
                "Method {$method} should exist in ApproveJurnalController"
            );
        }
    }

    // Test constructor dependency
    public function testConstructorInitializesProsesModel()
    {
        $controller = new ApproveJurnalController();
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('prosesModel');
        $property->setAccessible(true);
        
        $this->assertInstanceOf(ProsesModel::class, $property->getValue($controller));
    }

    // Test properties exist
    public function testControllerHasRequiredProperties()
    {
        $reflection = new \ReflectionClass(ApproveJurnalController::class);
        
        $this->assertTrue($reflection->hasProperty('prosesModel'));
        
        $property = $reflection->getProperty('prosesModel');
        $this->assertTrue($property->isProtected());
    }
}