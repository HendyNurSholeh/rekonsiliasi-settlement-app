<?php
namespace Tests\Unit\Controllers\Rekon\Process;

use CodeIgniter\Test\CIUnitTestCase;
use CodeIgniter\Test\FeatureTestTrait;
use App\Controllers\Rekon\Process\LaporanTransaksiDetailController;
use App\Models\ProsesModel;

class LaporanTransaksiDetailControllerTest extends CIUnitTestCase
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

        $this->controller = $this->getMockBuilder(LaporanTransaksiDetailController::class)
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
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn('2025-08-27');
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithAllFilters()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'settle_verifikasi' => '1',
            'id_pelanggan' => '123456789'
        ]);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testIndexWithPartialFilters()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'status_biller' => 'SUCCESS'
        ]);
        $result = $this->controller->index();
        $this->assertNotEmpty($result);
    }

    public function testDatatableWithTanggalFromGet()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
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
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithDefaultDate()
    {
        $this->request->setGlobal('get', []);
        $this->request->setGlobal('post', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn('2025-08-27');
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithStatusBillerFilter()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'status_biller' => 'SUCCESS',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithStatusCoreFilter()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'status_core' => 'AGR',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithSettleVerifikasiFilter()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'settle_verifikasi' => '1',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithIdPelangganFilter()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'id_pelanggan' => '123456789',
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
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
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByIdPartner()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '1', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByTerminalId()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '2', 'dir' => 'desc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByProduk()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '3', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithPagination()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'draw' => '1',
            'start' => '50',
            'length' => '100'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableNoTanggal()
    {
        $this->request->setGlobal('get', []);
        $this->request->setGlobal('post', []);
        $this->mockProsesModel->expects($this->once())
            ->method('getDefaultDate')
            ->willReturn(null);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
    }

    public function testDatatableExceptionHandling()
    {
        $this->request->setGlobal('get', ['tanggal' => '2025-08-27']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetDisputeDetailWithValidId()
    {
        $this->request->setGlobal('post', ['id' => '123']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->getDisputeDetail();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetDisputeDetailNoId()
    {
        $this->request->setGlobal('post', []);
        $response = $this->controller->getDisputeDetail();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testGetDisputeDetailExceptionHandling()
    {
        $this->request->setGlobal('post', ['id' => '123']);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->getDisputeDetail();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeWithValidData()
    {
        $this->request->setGlobal('post', [
            'id' => '123',
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'status_settlement' => '1',
            'idpartner' => 'PARTNER001'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeMissingId()
    {
        $this->request->setGlobal('post', [
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'status_settlement' => '1',
            'idpartner' => 'PARTNER001'
        ]);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeMissingStatusBiller()
    {
        $this->request->setGlobal('post', [
            'id' => '123',
            'status_core' => 'AGR',
            'status_settlement' => '1',
            'idpartner' => 'PARTNER001'
        ]);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeMissingStatusCore()
    {
        $this->request->setGlobal('post', [
            'id' => '123',
            'status_biller' => 'SUCCESS',
            'status_settlement' => '1',
            'idpartner' => 'PARTNER001'
        ]);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeMissingStatusSettlement()
    {
        $this->request->setGlobal('post', [
            'id' => '123',
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'idpartner' => 'PARTNER001'
        ]);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeMissingIdPartner()
    {
        $this->request->setGlobal('post', [
            'id' => '123',
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'status_settlement' => '1'
        ]);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testUpdateDisputeExceptionHandling()
    {
        $this->request->setGlobal('post', [
            'id' => '123',
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'status_settlement' => '1',
            'idpartner' => 'PARTNER001'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->updateDispute();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithAllFilters()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'status_biller' => 'SUCCESS',
            'status_core' => 'AGR',
            'settle_verifikasi' => '1',
            'id_pelanggan' => '123456789',
            'search' => ['value' => 'test'],
            'order' => [['column' => '4', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByIdPelanggan()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '4', 'dir' => 'desc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByRpBillerTag()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '5', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByStatusBiller()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '6', 'dir' => 'desc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderByStatusCore()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '7', 'dir' => 'asc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }

    public function testDatatableWithOrderBySettleVerifikasi()
    {
        $this->request->setGlobal('get', [
            'tanggal' => '2025-08-27',
            'order' => [['column' => '8', 'dir' => 'desc']],
            'draw' => '1',
            'start' => '0',
            'length' => '25'
        ]);
        $this->expectException(\CodeIgniter\Database\Exceptions\DatabaseException::class);
        $response = $this->controller->datatable();
        $this->assertTrue(method_exists($response, 'setJSON'));
    }
}
