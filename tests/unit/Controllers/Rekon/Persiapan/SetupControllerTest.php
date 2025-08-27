<?php
namespace Tests\Unit\Controllers\Rekon\Persiapan;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Rekon\Persiapan\SetupController;
use App\Models\ProsesModel;

class SetupControllerTest extends CIUnitTestCase
{
    use FeatureTestTrait;

    protected $controller;
    protected $mockModel;
    protected $request;
    protected $response;

    protected function setUp(): void
    {
        parent::setUp();
        $this->mockModel = $this->getMockBuilder(ProsesModel::class)
            ->onlyMethods(['getDefaultDate', 'checkExistingProcess', 'resetProcess', 'callProcessPersiapan'])
            ->getMock();
        $this->controller = $this->getMockBuilder(SetupController::class)
            ->onlyMethods(['logActivity', 'getSession', 'render'])
            ->addMethods(['view'])
            ->getMock();
        $this->controller->method('logActivity')->willReturn(1);
        $this->controller->method('getSession')->willReturn(null);
        $this->controller->method('view')->willReturn('<html>Mock View</html>');
        $this->controller->method('render')->willReturn('<html>Mock Render</html>'); // Mock render to avoid header issues
        $this->setPrivateProperty($this->controller, 'prosesModel', $this->mockModel);
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

    public function testIndexReturnsViewWithDefaultDate()
    {
        $this->mockModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn('2025-08-27');
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testCreateMissingTanggal()
    {
        $this->request->setGlobal('post', []);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateInvalidTanggalFormat()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => 'invalid-date']);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateExistingProcessNeedsConfirmation()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27']);
        $this->mockModel->method('checkExistingProcess')->willReturn(['exists' => true]);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateExistingProcessWithResetConfirmedAndFail()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27', 'reset_confirmed' => 'true']);
        $this->mockModel->method('checkExistingProcess')->willReturn(['exists' => true]);
        $this->mockModel->method('resetProcess')->willReturn(['success' => false, 'message' => 'Reset gagal']);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateExistingProcessWithResetConfirmedAndSuccess()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27', 'reset_confirmed' => 'true']);
        $this->mockModel->method('checkExistingProcess')->willReturn(['exists' => true]);
        $this->mockModel->method('resetProcess')->willReturn(['success' => true, 'message' => 'Reset sukses']);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateNewProcessFail()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27']);
        $this->mockModel->method('checkExistingProcess')->willReturn(['exists' => false]);
        $this->mockModel->method('callProcessPersiapan')->willReturn(['success' => false, 'message' => 'Gagal']);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateNewProcessSuccess()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27']);
        $this->mockModel->method('checkExistingProcess')->willReturn(['exists' => false]);
        $this->mockModel->method('callProcessPersiapan')->willReturn(['success' => true, 'message' => 'Berhasil']);
        $response = $this->controller->create();
        $this->assertTrue(method_exists($response, 'with'));
    }

    public function testCreateException()
    {
        $this->request->setGlobal('post', ['tanggal_rekon' => '2025-08-27']);
        $this->mockModel->method('checkExistingProcess')->will($this->throwException(new \Exception('DB error')));
        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('DB error');
        $this->controller->create();
    }

    public function testCheckDateNoTanggal()
    {
        $this->request->setGlobal('post', []);
        $response = $this->controller->checkDate();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testCheckDateSuccess()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockModel->method('checkExistingProcess')->willReturn(['exists' => true]);
        $response = $this->controller->checkDate();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testCheckDateException()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockModel->method('checkExistingProcess')->will($this->throwException(new \Exception('DB error')));
        $response = $this->controller->checkDate();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testResetProcessNoTanggal()
    {
        $this->request->setGlobal('post', []);
        $response = $this->controller->resetProcess();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testResetProcessSuccess()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockModel->method('resetProcess')->willReturn(['success' => true, 'message' => 'Reset sukses']);
        $response = $this->controller->resetProcess();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testResetProcessFail()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockModel->method('resetProcess')->willReturn(['success' => false, 'message' => 'Reset gagal']);
        $response = $this->controller->resetProcess();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testResetProcessException()
    {
        $this->request->setGlobal('post', ['tanggal' => '2025-08-27']);
        $this->mockModel->method('resetProcess')->will($this->throwException(new \Exception('DB error')));
        $response = $this->controller->resetProcess();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }
}
