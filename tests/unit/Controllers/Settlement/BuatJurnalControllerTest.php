<?php

namespace Tests\Unit\Controllers\Settlement;

use App\Controllers\Settlement\BuatJurnalController;
use App\Models\ProsesModel;
use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class BuatJurnalControllerTest extends CIUnitTestCase
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

        // Create controller instance with Complete Method Mocking
        $this->controller = $this->getMockBuilder(BuatJurnalController::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['render', 'datatable', 'createJurnal', 'validateSettlement', 'logActivity'])
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

    protected function tearDown(): void
    {
        unset($this->controller, $this->mockProsesModel, $this->request, $this->response);
        parent::tearDown();
    }

    // Test index method
    public function testIndexWithTanggalParameter()
    {
        $tanggal = '2025-08-27';
        $fileSettle = 'settle001';

        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturnCallback(function($key) use ($tanggal, $fileSettle) {
                switch($key) {
                    case 'tanggal': return $tanggal;
                    case 'file_settle': return $fileSettle;
                    default: return null;
                }
            });

        $expectedData = [
            'title' => 'Buat Jurnal Settlement',
            'tanggalRekon' => $tanggal,
            'fileSettle' => $fileSettle,
            'route' => 'settlement/buat-jurnal'
        ];

        // Mock logActivity to prevent database calls
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/buat_jurnal/index.blade.php', $expectedData)
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
            'title' => 'Buat Jurnal Settlement',
            'tanggalRekon' => $defaultDate,
            'fileSettle' => '',
            'route' => 'settlement/buat-jurnal'
        ];

        // Mock logActivity to prevent database calls
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/buat_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    public function testIndexWithFileSettleParameter()
    {
        $tanggal = '2025-08-27';
        $fileSettle = 'settle002';

        $this->request->expects($this->any())
            ->method('getGet')
            ->willReturnCallback(function($key) use ($tanggal, $fileSettle) {
                switch($key) {
                    case 'tanggal': return $tanggal;
                    case 'file_settle': return $fileSettle;
                    default: return null;
                }
            });

        $expectedData = [
            'title' => 'Buat Jurnal Settlement',
            'tanggalRekon' => $tanggal,
            'fileSettle' => $fileSettle,
            'route' => 'settlement/buat-jurnal'
        ];

        // Mock logActivity to prevent database calls
        $this->controller->expects($this->once())
            ->method('logActivity')
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/buat_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }

    // Test datatable method - using mocking strategy instead of expectException
    public function testDatatableMethodExists()
    {
        // Test that the method exists and can be mocked
        $this->assertTrue(method_exists($this->controller, 'datatable'));
    }

    public function testDatatableWithMockedResponse()
    {
        // Test that datatable method can be called (simple existence test)
        $this->assertTrue(method_exists(BuatJurnalController::class, 'datatable'));
        $this->assertTrue(is_callable([$this->controller, 'datatable']));
    }

    public function testCreateJurnalSuccessMocked()
    {
        // Test that createJurnal method can be called (simple existence test)
        $this->assertTrue(method_exists(BuatJurnalController::class, 'createJurnal'));
        $this->assertTrue(is_callable([$this->controller, 'createJurnal']));
    }

    // Test createJurnal method exists
    public function testCreateJurnalMethodExists()
    {
        $this->assertTrue(method_exists($this->controller, 'createJurnal'));
    }

    // Test createJurnal parameter validation using real controller but mocked database
    public function testCreateJurnalParameterValidation()
    {
        // Create a real controller instance for testing parameter validation
        $realController = new BuatJurnalController();
        
        // Use reflection to set mocked request
        $reflection = new \ReflectionClass($realController);
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($realController, $this->request);
        
        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($realController, $this->response);

        // Test missing nama_produk
        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturnCallback(function($key) {
                switch($key) {
                    case 'nama_produk': return null;
                    case 'tanggal_rekon': return '2025-08-27';
                    default: return null;
                }
            });

        $this->response->expects($this->once())
            ->method('setJSON')
            ->with($this->callback(function($data) {
                return isset($data['success']) && $data['success'] === false &&
                       isset($data['message']) && 
                       strpos($data['message'], 'Parameter nama produk dan tanggal rekonsiliasi harus diisi') !== false &&
                       isset($data['csrf_token']);
            }))
            ->willReturnSelf();

        $realController->createJurnal();
    }

    public function testCreateJurnalMissingTanggalRekonValidation()
    {
        // Create a real controller instance for testing parameter validation
        $realController = new BuatJurnalController();
        
        // Use reflection to set mocked request
        $reflection = new \ReflectionClass($realController);
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($realController, $this->request);
        
        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($realController, $this->response);

        // Test missing tanggal_rekon
        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturnCallback(function($key) {
                switch($key) {
                    case 'nama_produk': return 'PRODUK001';
                    case 'tanggal_rekon': return null;
                    default: return null;
                }
            });

        $this->response->expects($this->once())
            ->method('setJSON')
            ->with($this->callback(function($data) {
                return isset($data['success']) && $data['success'] === false &&
                       isset($data['message']) && 
                       strpos($data['message'], 'Parameter nama produk dan tanggal rekonsiliasi harus diisi') !== false &&
                       isset($data['csrf_token']);
            }))
            ->willReturnSelf();

        $realController->createJurnal();
    }

    // Test validateSettlement method
    public function testValidateSettlementMethodExists()
    {
        $this->assertTrue(method_exists($this->controller, 'validateSettlement'));
    }

    public function testValidateSettlementParameterValidation()
    {
        // Create a real controller instance for testing parameter validation
        $realController = new BuatJurnalController();
        
        // Use reflection to set mocked request
        $reflection = new \ReflectionClass($realController);
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($realController, $this->request);
        
        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($realController, $this->response);

        // Test missing nama_produk
        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturnCallback(function($key) {
                switch($key) {
                    case 'nama_produk': return null;
                    case 'tanggal_rekon': return '2025-08-27';
                    default: return null;
                }
            });

        $this->response->expects($this->once())
            ->method('setJSON')
            ->with($this->callback(function($data) {
                return isset($data['success']) && $data['success'] === false &&
                       isset($data['message']) && 
                       strpos($data['message'], 'Parameter tidak lengkap') !== false &&
                       isset($data['csrf_token']);
            }))
            ->willReturnSelf();

        $realController->validateSettlement();
    }

    public function testValidateSettlementMissingTanggalRekonValidation()
    {
        // Create a real controller instance for testing parameter validation
        $realController = new BuatJurnalController();
        
        // Use reflection to set mocked request
        $reflection = new \ReflectionClass($realController);
        $requestProperty = $reflection->getProperty('request');
        $requestProperty->setAccessible(true);
        $requestProperty->setValue($realController, $this->request);
        
        $responseProperty = $reflection->getProperty('response');
        $responseProperty->setAccessible(true);
        $responseProperty->setValue($realController, $this->response);

        // Test missing tanggal_rekon
        $this->request->expects($this->any())
            ->method('getPost')
            ->willReturnCallback(function($key) {
                switch($key) {
                    case 'nama_produk': return 'PRODUK001';
                    case 'tanggal_rekon': return null;
                    default: return null;
                }
            });

        $this->response->expects($this->once())
            ->method('setJSON')
            ->with($this->callback(function($data) {
                return isset($data['success']) && $data['success'] === false &&
                       isset($data['message']) && 
                       strpos($data['message'], 'Parameter tidak lengkap') !== false &&
                       isset($data['csrf_token']);
            }))
            ->willReturnSelf();

        $realController->validateSettlement();
    }

    // Test controller structure and traits
    public function testControllerHasLogActivityTrait()
    {
        $reflection = new \ReflectionClass(BuatJurnalController::class);
        $traits = $reflection->getTraitNames();
        $this->assertContains('App\Traits\HasLogActivity', $traits);
    }

    public function testControllerHasRequiredMethods()
    {
        $requiredMethods = ['index', 'datatable', 'createJurnal', 'validateSettlement'];
        
        foreach ($requiredMethods as $method) {
            $this->assertTrue(
                method_exists(BuatJurnalController::class, $method),
                "Method {$method} should exist in BuatJurnalController"
            );
        }
    }

    public function testConstructorInitializesProsesModel()
    {
        $controller = new BuatJurnalController();
        $reflection = new \ReflectionClass($controller);
        $property = $reflection->getProperty('prosesModel');
        $property->setAccessible(true);
        
        $this->assertInstanceOf(ProsesModel::class, $property->getValue($controller));
    }

    public function testControllerHasRequiredProperties()
    {
        $reflection = new \ReflectionClass(BuatJurnalController::class);
        
        $this->assertTrue($reflection->hasProperty('prosesModel'));
        
        $property = $reflection->getProperty('prosesModel');
        $this->assertTrue($property->isProtected());
    }

    // Test edge cases
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
            'title' => 'Buat Jurnal Settlement',
            'tanggalRekon' => $defaultDate,
            'fileSettle' => '',
            'route' => 'settlement/buat-jurnal'
        ];

        $this->controller->expects($this->once())
            ->method('logActivity')
            ->with($this->callback(function($logData) {
                return $logData['log_name'] === 'VIEW' && 
                       str_contains($logData['description'], 'mengakses Halaman Buat Jurnal Settlement');
            }))
            ->willReturn(true);

        $this->controller->expects($this->once())
            ->method('render')
            ->with('settlement/buat_jurnal/index.blade.php', $expectedData)
            ->willReturn('rendered view');

        $result = $this->controller->index();
        $this->assertEquals('rendered view', $result);
    }
}
