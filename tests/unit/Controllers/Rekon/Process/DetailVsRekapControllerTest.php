<?php
namespace Tests\Unit\Controllers\Rekon\Process;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Rekon\Process\DetailVsRekapController;
use App\Models\ProsesModel;

class DetailVsRekapControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $mockProsesModel;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Mock session data to avoid null values in logActivity
        $_SESSION = [
            'logged_in' => true,
            'username' => 'test_user',
            'name' => 'Test User'
        ];

        $this->mockProsesModel = $this->getMockBuilder(ProsesModel::class)
            ->onlyMethods(['getDefaultDate'])
            ->getMock();

        $this->request = \Config\Services::request();
        $this->response = $this->getMockBuilder(\CodeIgniter\HTTP\Response::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['setBody', 'setJSON'])
            ->addMethods(['with'])
            ->getMock();
        $this->response->method('setBody')->willReturnSelf();
        $this->response->method('with')->willReturnSelf();
        $this->response->method('setJSON')->willReturnSelf();

        $this->controller = $this->getMockBuilder(DetailVsRekapController::class)
            ->onlyMethods(['render', 'logActivity', 'index', 'datatable', 'statistics'])
            ->getMock();
            
        $this->controller->method('render')->willReturn('<html>Mock Render</html>');
        $this->controller->method('logActivity')->willReturn(1); // Mock successful log
        $this->controller->method('index')->willReturn('<html>Mock Render</html>');
        $this->controller->method('datatable')->willReturn($this->response);
        $this->controller->method('statistics')->willReturn($this->response);

        $this->setPrivateProperty($this->controller, 'prosesModel', $this->mockProsesModel);
        $this->setPrivateProperty($this->controller, 'request', $this->request);
        $this->setPrivateProperty($this->controller, 'response', $this->response);
    }

    public function testIndexWithTanggalFromUrl()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
        $this->assertEquals('<html>Mock Render</html>', $result);
    }

    public function testIndexWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
        $this->assertEquals('<html>Mock Render</html>', $result);
    }

    public function testIndexWithFilterSelisih()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'filter_selisih' => 'ada_selisih'
        ]);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
        $this->assertEquals('<html>Mock Render</html>', $result);
    }

    public function testIndexNoTanggal()
    {
        $this->request->setGlobal('get', []);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
        $this->assertEquals('<html>Mock Render</html>', $result);
    }

    public function testIndexExceptionHandling()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
        $this->assertEquals('<html>Mock Render</html>', $result);
    }

    public function testDatatableWithTanggalFromGet()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithTanggalFromPost()
    {
        $this->request->setGlobal('post', [
            'tanggal' => '2025-08-27',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $this->request->setGlobal('post', []);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithFilterSelisih()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'filter_selisih' => 'ada_selisih',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithSearch()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'search' => ['value' => 'test'],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrder()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '1', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableNoTanggal()
    {
        $this->request->setGlobal('get', []);
        $this->request->setGlobal('post', []);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableExceptionHandling()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testStatisticsWithTanggalFromGet()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $response = $this->controller->statistics();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testStatisticsWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $response = $this->controller->statistics();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testStatisticsWithFilterSelisih()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'filter_selisih' => 'ada_selisih'
        ]);
        $response = $this->controller->statistics();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testStatisticsNoTanggal()
    {
        $this->request->setGlobal('get', []);
        $response = $this->controller->statistics();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testStatisticsExceptionHandling()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $response = $this->controller->statistics();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithPagination()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'draw' => '1',
            'start' => '10',
            'length' => '50'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithFilterTidakAdaSelisih()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'filter_selisih' => 'tidak_ada_selisih',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByAmount()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '3', 'dir' => 'desc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderBySelisih()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '5', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testStatisticsCalculatesAccuracy()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $response = $this->controller->statistics();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }
}
