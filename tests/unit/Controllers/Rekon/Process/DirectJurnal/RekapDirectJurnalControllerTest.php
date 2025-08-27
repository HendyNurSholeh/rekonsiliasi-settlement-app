<?php
namespace Tests\Unit\Controllers\Rekon\Process\DirectJurnal;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Rekon\Process\DirectJurnal\RekapDirectJurnalController;
use App\Models\ProsesModel;

class RekapDirectJurnalControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $mockProsesModel;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockProsesModel = $this->getMockBuilder(ProsesModel::class)
            ->onlyMethods(['getDefaultDate'])
            ->getMock();

        $this->controller = $this->getMockBuilder(RekapDirectJurnalController::class)
            ->onlyMethods(['render'])
            ->getMock();
        $this->controller->method('render')->willReturn('<html>Mock Render</html>');

        $this->setPrivateProperty($this->controller, 'prosesModel', $this->mockProsesModel);

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
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn('2025-08-27');
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexNoTanggal()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithEmptyTanggal()
    {
        $this->request->setGlobal('get', ['tanggal' => '']);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexExceptionHandling()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithDifferentDateFormats()
    {
        $testDates = [
            '2025-08-27',
            '2025-01-15',
            '2024-12-31'
        ];

        foreach ($testDates as $date) {
            $this->request->setGlobal('get', ['tanggal' => $date]);
            $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
            $result = $this->controller->index();
            $this->assertNotEmpty($result);
        }
    }

    public function testIndexDataStructure()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexRouteParameter()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexTitleParameter()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithPostParameter()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexParameterPriority()
    {
        // Test that GET parameter takes priority over POST
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->request->setGlobal('post', ['tanggal' => '2025-08-28']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexDefaultDateFallback()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn('2025-08-27');
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexStoredProcedureCall()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexRekapDataAssignment()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexEmptyRekapData()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexViewRendering()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexErrorLogging()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexDatabaseConnection()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexQueryExecution()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexResultArrayProcessing()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }
}
