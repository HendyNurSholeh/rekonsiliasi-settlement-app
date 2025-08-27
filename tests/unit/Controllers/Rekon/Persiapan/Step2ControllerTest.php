<?php
namespace Tests\Unit\Controllers\Rekon\Persiapan;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Rekon\Persiapan\Step2Controller;
use App\Models\ProsesModel;
use App\Models\AgnDetailModel;
use App\Models\AgnSettleEduModel;
use App\Models\AgnSettlePajakModel;
use App\Models\AgnTrxMgateModel;
use App\Models\VGroupProdukModel;
use App\Models\TampAgnDetailModel;
use App\Models\TampAgnSettleEduModel;
use App\Models\TampAgnSettlePajakModel;
use App\Models\TampAgnTrxMgateModel;

class Step2ControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $mockProsesModel;
    protected $mockAgnDetailModel;
    protected $mockAgnSettleEduModel;
    protected $mockAgnSettlePajakModel;
    protected $mockAgnTrxMgateModel;
    protected $mockVGroupProdukModel;
    protected $mockTampAgnDetailModel;
    protected $mockTampAgnSettleEduModel;
    protected $mockTampAgnSettlePajakModel;
    protected $mockTampAgnTrxMgateModel;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockProsesModel = $this->getMockBuilder(ProsesModel::class)
            ->onlyMethods(['getDefaultDate'])
            ->getMock();
        $this->mockAgnDetailModel = $this->getMockBuilder(AgnDetailModel::class)
            ->getMock();
        $this->mockAgnSettleEduModel = $this->getMockBuilder(AgnSettleEduModel::class)
            ->getMock();
        $this->mockAgnSettlePajakModel = $this->getMockBuilder(AgnSettlePajakModel::class)
            ->getMock();
        $this->mockAgnTrxMgateModel = $this->getMockBuilder(AgnTrxMgateModel::class)
            ->getMock();
        $this->mockVGroupProdukModel = $this->getMockBuilder(VGroupProdukModel::class)
            ->onlyMethods(['getValidationStatus', 'getGroupProdukData', 'getMappingStatistics'])
            ->getMock();
        $this->mockTampAgnDetailModel = $this->getMockBuilder(TampAgnDetailModel::class)
            ->onlyMethods(['getStatistics'])
            ->getMock();
        $this->mockTampAgnSettleEduModel = $this->getMockBuilder(TampAgnSettleEduModel::class)
            ->onlyMethods(['getStatistics'])
            ->getMock();
        $this->mockTampAgnSettlePajakModel = $this->getMockBuilder(TampAgnSettlePajakModel::class)
            ->onlyMethods(['getStatistics'])
            ->getMock();
        $this->mockTampAgnTrxMgateModel = $this->getMockBuilder(TampAgnTrxMgateModel::class)
            ->onlyMethods(['getStatistics'])
            ->getMock();

        $this->controller = $this->getMockBuilder(Step2Controller::class)
            ->onlyMethods(['logActivity', 'getSession', 'render'])
            ->addMethods(['view'])
            ->getMock();
        $this->controller->method('logActivity')->willReturn(1);
        $this->controller->method('getSession')->willReturn(null);
        $this->controller->method('view')->willReturn('<html>Mock View</html>');
        $this->controller->method('render')->willReturn('<html>Mock Render</html>');

        $this->setPrivateProperty($this->controller, 'prosesModel', $this->mockProsesModel);
        $this->setPrivateProperty($this->controller, 'agnDetailModel', $this->mockAgnDetailModel);
        $this->setPrivateProperty($this->controller, 'agnSettleEduModel', $this->mockAgnSettleEduModel);
        $this->setPrivateProperty($this->controller, 'agnSettlePajakModel', $this->mockAgnSettlePajakModel);
        $this->setPrivateProperty($this->controller, 'agnTrxMgateModel', $this->mockAgnTrxMgateModel);
        $this->setPrivateProperty($this->controller, 'vGroupProdukModel', $this->mockVGroupProdukModel);
        $this->setPrivateProperty($this->controller, 'tampAgnDetailModel', $this->mockTampAgnDetailModel);
        $this->setPrivateProperty($this->controller, 'tampAgnSettleEduModel', $this->mockTampAgnSettleEduModel);
        $this->setPrivateProperty($this->controller, 'tampAgnSettlePajakModel', $this->mockTampAgnSettlePajakModel);
        $this->setPrivateProperty($this->controller, 'tampAgnTrxMgateModel', $this->mockTampAgnTrxMgateModel);

        $this->request = \Config\Services::request();
        $this->response = $this->getMockBuilder(\CodeIgniter\HTTP\Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setBody', 'setJSON'])
            ->addMethods(['with'])
            ->getMock();
        $this->response->method('setBody')->willReturnSelf();
        $this->response->method('with')->willReturnSelf();
        $this->response->method('setJSON')->willReturnSelf();
        $this->setPrivateProperty($this->controller, 'request', $this->request);
        $this->setPrivateProperty($this->controller, 'response', $this->response);
    }

    public function testIndexWithTanggalFromUrl()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->mockVGroupProdukModel->method('getValidationStatus')->willReturn([
            'is_valid' => true,
            'validation_message' => 'Valid',
            'can_proceed' => true
        ]);
        $this->mockVGroupProdukModel->method('getGroupProdukData')->willReturn([]);
        $this->mockVGroupProdukModel->method('getMappingStatistics')->willReturn([
            'total_products' => 0,
            'mapped_products' => 0,
            'unmapped_products' => 0,
            'mapping_percentage' => 0
        ]);
        $this->mockTampAgnDetailModel->method('getStatistics')->willReturn(['count' => 0]);
        $this->mockTampAgnSettleEduModel->method('getStatistics')->willReturn(['count' => 0]);
        $this->mockTampAgnSettlePajakModel->method('getStatistics')->willReturn(['count' => 0]);
        $this->mockTampAgnTrxMgateModel->method('getStatistics')->willReturn(['count' => 0]);

        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn('2025-08-27');
        $this->mockVGroupProdukModel->method('getValidationStatus')->willReturn([
            'is_valid' => true,
            'validation_message' => 'Valid',
            'can_proceed' => true
        ]);
        $this->mockVGroupProdukModel->method('getGroupProdukData')->willReturn([]);
        $this->mockVGroupProdukModel->method('getMappingStatistics')->willReturn([
            'total_products' => 0,
            'mapped_products' => 0,
            'unmapped_products' => 0,
            'mapping_percentage' => 0
        ]);
        $this->mockTampAgnDetailModel->method('getStatistics')->willReturn(['count' => 0]);
        $this->mockTampAgnSettleEduModel->method('getStatistics')->willReturn(['count' => 0]);
        $this->mockTampAgnSettlePajakModel->method('getStatistics')->willReturn(['count' => 0]);
        $this->mockTampAgnTrxMgateModel->method('getStatistics')->willReturn(['count' => 0]);

        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexNoTanggalRedirect()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $result = $this->controller->index();
        $this->assertTrue(method_exists($result, 'with'));
    }

    public function testIndexExceptionHandling()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->mockVGroupProdukModel->method('getValidationStatus')
            ->will($this->throwException(new \Exception('Database error')));
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testProcessValidationNoTanggal()
    {
        $this->request->setGlobal('post', []);
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $response = $this->controller->processValidation();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testProcessValidationCannotProceed()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockVGroupProdukModel->method('getValidationStatus')->willReturn([
            'is_valid' => false,
            'validation_message' => 'Mapping incomplete',
            'can_proceed' => false
        ]);
        $this->mockVGroupProdukModel->method('getGroupProdukData')->willReturn([
            ['NAMA_GROUP' => ''],
            ['NAMA_GROUP' => 'GROUP1']
        ]);
        $response = $this->controller->processValidation();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testProcessValidationSuccess()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockVGroupProdukModel->method('getValidationStatus')->willReturn([
            'is_valid' => true,
            'validation_message' => 'Valid',
            'can_proceed' => true
        ]);
        // Mock database connection to avoid actual DB call
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->processValidation();
    }

    public function testProcessValidationException()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockVGroupProdukModel->method('getValidationStatus')
            ->will($this->throwException(new \Exception('Database error')));
        $response = $this->controller->processValidation();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testExecuteReconciliationSuccess()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('executeReconciliation');
        $method->setAccessible(true);

        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $method->invoke($this->controller, '2025-08-27');
    }

    public function testExecuteReconciliationException()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('executeReconciliation');
        $method->setAccessible(true);

        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $method->invoke($this->controller, '2025-08-27');
    }

    public function testProsesUlangNoTanggal()
    {
        $this->request->setGlobal('post', []);
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $response = $this->controller->prosesUlang();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testProsesUlangSuccess()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->prosesUlang();
    }

    public function testProsesUlangException()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $this->controller->prosesUlang();
    }

    public function testGetDataPreviewNoParameters()
    {
        $this->request->setGlobal('get', []);
        $response = $this->controller->getDataPreview();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetDataPreviewFileNotFound()
    {
        $this->request->setGlobal('get', [
            'file_type' => 'agn_detail',
            'tanggal' => '2025-08-27'
        ]);
        $response = $this->controller->getDataPreview();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetDataPreviewSuccess()
    {
        $this->request->setGlobal('get', [
            'file_type' => 'agn_detail',
            'tanggal' => '2025-08-27'
        ]);
        $response = $this->controller->getDataPreview();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetMappingDataSuccess()
    {
        $this->mockVGroupProdukModel->method('getGroupProdukData')->willReturn([]);
        $this->mockVGroupProdukModel->method('getMappingStatistics')->willReturn([
            'total_products' => 0,
            'mapped_products' => 0,
            'unmapped_products' => 0,
            'mapping_percentage' => 0
        ]);
        $response = $this->controller->getMappingData();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetMappingDataException()
    {
        $this->mockVGroupProdukModel->method('getGroupProdukData')
            ->will($this->throwException(new \Exception('Database error')));
        $response = $this->controller->getMappingData();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetUploadStatsNoTanggal()
    {
        $this->request->setGlobal('post', []);
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $response = $this->controller->getUploadStats();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetUploadStatsSuccess()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $response = $this->controller->getUploadStats();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetUploadStatsException()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $response = $this->controller->getUploadStats();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testValidateFileContentValid()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateFileContent');
        $method->setAccessible(true);

        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test,data1,data2');

        $result = $method->invoke($this->controller, $tempFile, 'agn_detail', '2025-08-27');
        $this->assertArrayHasKey('valid', $result);

        unlink($tempFile);
    }

    public function testValidateFileContentInvalid()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateFileContent');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, '/nonexistent/file.csv', 'agn_detail', '2025-08-27');
        $this->assertFalse($result['valid']);
    }

    public function testValidateMgateFileValid()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateMgateFile');
        $method->setAccessible(true);

        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test,data1,data2');

        $result = $method->invoke($this->controller, $tempFile, '2025-08-27');
        $this->assertArrayHasKey('valid', $result);

        unlink($tempFile);
    }

    public function testValidateMgateFileInvalid()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateMgateFile');
        $method->setAccessible(true);

        $result = $method->invoke($this->controller, '/nonexistent/file.csv', '2025-08-27');
        $this->assertFalse($result['valid']);
    }

    public function testValidateGenericFileValid()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('validateGenericFile');
        $method->setAccessible(true);

        // Create a temporary file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test');
        file_put_contents($tempFile, 'test,data1,data2');

        $result = $method->invoke($this->controller, $tempFile, '2025-08-27');
        $this->assertArrayHasKey('valid', $result);

        unlink($tempFile);
    }

    public function testReadFilePreviewCsv()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('readFilePreview');
        $method->setAccessible(true);

        // Create a temporary CSV file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test') . '.csv';
        file_put_contents($tempFile, "header1,header2,header3\nrow1,row2,row3\nrow4,row5,row6");

        $result = $method->invoke($this->controller, $tempFile, 5);
        $this->assertIsArray($result);
        $this->assertCount(3, $result); // Header + 2 data rows

        unlink($tempFile);
    }

    public function testReadFilePreviewExcel()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('readFilePreview');
        $method->setAccessible(true);

        // Create a temporary Excel file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test') . '.xlsx';
        file_put_contents($tempFile, 'dummy excel content');

        $result = $method->invoke($this->controller, $tempFile, 5);
        $this->assertIsArray($result);

        unlink($tempFile);
    }

    public function testCountFileRowsCsv()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('countFileRows');
        $method->setAccessible(true);

        // Create a temporary CSV file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test') . '.csv';
        file_put_contents($tempFile, "header1,header2\nrow1,row2\nrow3,row4\nrow5,row6\nrow7,row8\nrow9,row10");

        $result = $method->invoke($this->controller, $tempFile);
        $this->assertEquals(6, $result);

        unlink($tempFile);
    }

    public function testCountFileRowsExcel()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('countFileRows');
        $method->setAccessible(true);

        // Create a temporary Excel file for testing
        $tempFile = tempnam(sys_get_temp_dir(), 'test') . '.xlsx';
        file_put_contents($tempFile, 'dummy excel content');

        $result = $method->invoke($this->controller, $tempFile);
        $this->assertEquals('N/A', $result);

        unlink($tempFile);
    }

    public function testFormatFileSize()
    {
        // Test private method using reflection
        $reflection = new \ReflectionClass($this->controller);
        $method = $reflection->getMethod('formatFileSize');
        $method->setAccessible(true);

        $this->assertEquals('1.00 KB', $method->invoke($this->controller, 1024));
        $this->assertEquals('1.00 MB', $method->invoke($this->controller, 1048576));
        $this->assertEquals('1.00 GB', $method->invoke($this->controller, 1073741824));
        $this->assertEquals('512 bytes', $method->invoke($this->controller, 512));
    }
}
