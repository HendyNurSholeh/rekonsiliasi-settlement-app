<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.
// $routes->setAutoRoute(false);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.

// ? Auth
$routes->addRedirect('/', '/login');
$routes->get('/login', 'Auth\Login::index', ['as' => 'login']);
$routes->post('/login', 'Auth\Login::index');
$routes->get('/logout', 'Auth\Login::logout', ['as' => 'logout']);

$routes->get('/refresh/captcha', 'Auth\Login::refreshCaptcha', ['as' => 'refresh.captcha']);

// ? Profile
$routes->get('/profile', 'User\Profile::index', ['as' => 'user.profile']);
$routes->post('/profile', 'User\Profile::index');

// ? Dashboard
$routes->get('/dashboard', 'Dashboard::index', ['as' => 'dashboard']);

// ? Rekonsiliasi Settlement Routes - 4 Controller Terpisah
$routes->group('rekon', ['namespace' => 'App\Controllers\Rekon'], function($routes) {
    // Setup Controller - untuk index.blade.php (moved to Persiapan folder)
    $routes->get('/', 'Persiapan\SetupController::index', ['as' => 'rekon.index']);
    $routes->post('create', 'Persiapan\SetupController::create', ['as' => 'rekon.create']);
    $routes->post('checkDate', 'Persiapan\SetupController::checkDate', ['as' => 'rekon.checkDate']);
    $routes->post('resetProcess', 'Persiapan\SetupController::resetProcess', ['as' => 'rekon.resetProcess']);
    
    // Step 1 Controller - untuk step1.blade.php (Upload Files - moved to Persiapan folder)
    $routes->get('step1', 'Persiapan\Step1Controller::index', ['as' => 'rekon.step1']);
    $routes->post('step1/upload', 'Persiapan\Step1Controller::uploadFiles', ['as' => 'rekon.step1.upload']);
    $routes->post('step1/validate', 'Persiapan\Step1Controller::validateFiles', ['as' => 'rekon.step1.validate']);
    $routes->post('step1/process', 'Persiapan\Step1Controller::processDataUpload', ['as' => 'rekon.step1.process']);
    $routes->post('step1/status', 'Persiapan\Step1Controller::checkUploadStatus', ['as' => 'rekon.step1.status']);
    $routes->get('step1/stats', 'Persiapan\Step1Controller::getUploadStats', ['as' => 'rekon.step1.stats']);
    $routes->get('step1/mapping', 'Persiapan\Step1Controller::checkProductMapping', ['as' => 'rekon.step1.mapping']);
    
    // Step 2 Controller - untuk step2.blade.php (Validasi Data - moved to Persiapan folder)
    $routes->get('step2', 'Persiapan\Step2Controller::index', ['as' => 'rekon.step2']);
    $routes->post('step2/validate', 'Persiapan\Step2Controller::processValidation', ['as' => 'rekon.step2.validate']);
    $routes->post('step2/proses-ulang', 'Persiapan\Step2Controller::prosesUlang', ['as' => 'rekon.step2.proses-ulang']);
    $routes->get('step2/preview', 'Persiapan\Step2Controller::getDataPreview', ['as' => 'rekon.step2.preview']);
    $routes->get('step2/stats', 'Persiapan\Step2Controller::getUploadStats', ['as' => 'rekon.step2.stats']);
    
    // Step 3 Controller - untuk step3.blade.php (Proses Rekonsiliasi - moved to Persiapan folder)
    $routes->get('step3', 'Persiapan\Step3Controller::index', ['as' => 'rekon.step3']);
    $routes->post('step3/process', 'Persiapan\Step3Controller::processReconciliation', ['as' => 'rekon.step3.process']);
    $routes->get('step3/progress', 'Persiapan\Step3Controller::getReconciliationProgress', ['as' => 'rekon.step3.progress']);
    $routes->post('step3/reports', 'Persiapan\Step3Controller::generateReports', ['as' => 'rekon.step3.reports']);
    $routes->get('step3/download', 'Persiapan\Step3Controller::downloadReport', ['as' => 'rekon.step3.download']);
    
    // Process Routes - New Modular Controller Structure
    $routes->group('process', ['namespace' => 'App\Controllers\Rekon\Process'], function($routes) {
        
        // Laporan Transaksi Detail Controller
        $routes->get('laporan-transaksi-detail', 'LaporanTransaksiDetailController::index', ['as' => 'rekon.process.laporan-transaksi-detail']);
        $routes->get('laporan-transaksi-detail/datatable', 'LaporanTransaksiDetailController::datatable', ['as' => 'rekon.process.laporan-transaksi-detail.datatable']);
        $routes->post('laporan-transaksi-detail/datatable', 'LaporanTransaksiDetailController::datatable', ['as' => 'rekon.process.laporan-transaksi-detail.datatable.post']);
        $routes->post('laporan-transaksi-detail/detail', 'LaporanTransaksiDetailController::getDisputeDetail', ['as' => 'rekon.process.laporan-transaksi-detail.detail']);
        $routes->post('laporan-transaksi-detail/update', 'LaporanTransaksiDetailController::updateDispute', ['as' => 'rekon.process.laporan-transaksi-detail.update']);
        $routes->post('laporan-transaksi-detail/verif-settlement', 'LaporanTransaksiDetailController::verifSettlement', ['as' => 'rekon.process.laporan-transaksi-detail.verif-settlement']);
        
        // Detail vs Rekap Controller
        $routes->get('detail-vs-rekap', 'DetailVsRekapController::index', ['as' => 'rekon.process.detail-vs-rekap']);
        $routes->get('detail-vs-rekap/datatable', 'DetailVsRekapController::datatable', ['as' => 'rekon.process.detail-vs-rekap.datatable']);
        $routes->post('detail-vs-rekap/datatable', 'DetailVsRekapController::datatable', ['as' => 'rekon.process.detail-vs-rekap.datatable.post']);
        $routes->get('detail-vs-rekap/statistics', 'DetailVsRekapController::statistics', ['as' => 'rekon.process.detail-vs-rekap.statistics']);
        
        // Direct Jurnal Controller (handles both rekap and dispute)
        $routes->get('direct-jurnal-rekap', 'DirectJurnalController::rekap', ['as' => 'rekon.process.direct-jurnal-rekap']);
        $routes->get('penyelesaian-dispute', 'DirectJurnalController::dispute', ['as' => 'rekon.process.penyelesaian-dispute']);
        $routes->get('direct-jurnal/dispute/datatable', 'DirectJurnalController::disputeDataTable', ['as' => 'rekon.process.dispute.datatable']);
        $routes->post('direct-jurnal/dispute/datatable', 'DirectJurnalController::disputeDataTable', ['as' => 'rekon.process.dispute.datatable.post']);
        $routes->post('direct-jurnal/dispute/detail', 'DirectJurnalController::getDisputeDetail', ['as' => 'rekon.process.dispute.detail']);
        $routes->post('direct-jurnal/dispute/update', 'DirectJurnalController::updateDispute', ['as' => 'rekon.process.dispute.update']);
        
        // Indirect Jurnal Rekap Controller
        $routes->get('indirect-jurnal-rekap', 'IndirectJurnalRekapController::index', ['as' => 'rekon.process.indirect-jurnal-rekap']);
        $routes->get('indirect-jurnal-rekap/datatable', 'IndirectJurnalRekapController::datatable', ['as' => 'rekon.process.indirect-jurnal-rekap.datatable']);
        $routes->post('indirect-jurnal-rekap/datatable', 'IndirectJurnalRekapController::datatable', ['as' => 'rekon.process.indirect-jurnal-rekap.datatable.post']);
        $routes->post('indirect-jurnal-rekap/konfirmasi', 'IndirectJurnalRekapController::konfirmasiSetoran', ['as' => 'rekon.process.indirect-jurnal-rekap.konfirmasi']);
        $routes->post('indirect-jurnal-rekap/update-sukses', 'IndirectJurnalRekapController::updateSukses', ['as' => 'rekon.process.indirect-jurnal-rekap.update-sukses']);
        
        // Indirect Dispute Controller
        $routes->get('indirect-dispute', 'IndirectDisputeController::index', ['as' => 'rekon.process.indirect-dispute']);
        $routes->get('indirect-dispute/datatable', 'IndirectDisputeController::datatable', ['as' => 'rekon.process.indirect-dispute.datatable']);
        $routes->post('indirect-dispute/datatable', 'IndirectDisputeController::datatable', ['as' => 'rekon.process.indirect-dispute.datatable.post']);
        $routes->post('indirect-dispute/detail', 'IndirectDisputeController::getDetail', ['as' => 'rekon.process.indirect-dispute.detail']);
        $routes->post('indirect-dispute/update', 'IndirectDisputeController::update', ['as' => 'rekon.process.indirect-dispute.update']);
    });
    
    // Note: Report routes removed as RekonReport controller was deleted
});

// ? Settlement Routes
$routes->group('settlement', ['namespace' => 'App\Controllers\Settlement'], function($routes) {
    
    // Buat Jurnal Settlement Controller
    $routes->get('buat-jurnal', 'BuatJurnalController::index', ['as' => 'settlement.buat-jurnal']);
    $routes->get('buat-jurnal/datatable', 'BuatJurnalController::datatable', ['as' => 'settlement.buat-jurnal.datatable']);
    $routes->post('buat-jurnal/datatable', 'BuatJurnalController::datatable', ['as' => 'settlement.buat-jurnal.datatable.post']);
    $routes->post('buat-jurnal/validate', 'BuatJurnalController::validateSettlement', ['as' => 'settlement.buat-jurnal.validate']);
    $routes->post('buat-jurnal/create', 'BuatJurnalController::createJurnal', ['as' => 'settlement.buat-jurnal.create']);
    
    // Approve Jurnal Settlement Controller
    $routes->get('approve-jurnal', 'ApproveJurnalController::index', ['as' => 'settlement.approve-jurnal']);
    $routes->get('approve-jurnal/datatable', 'ApproveJurnalController::datatable', ['as' => 'settlement.approve-jurnal.datatable']);
    $routes->post('approve-jurnal/datatable', 'ApproveJurnalController::datatable', ['as' => 'settlement.approve-jurnal.datatable.post']);
    $routes->post('approve-jurnal/detail', 'ApproveJurnalController::getDetailJurnal', ['as' => 'settlement.approve-jurnal.detail']);
    $routes->post('approve-jurnal/process', 'ApproveJurnalController::processApproval', ['as' => 'settlement.approve-jurnal.process']);
    $routes->get('approve-jurnal/summary', 'ApproveJurnalController::getSummary', ['as' => 'settlement.approve-jurnal.summary']);
    
    // Jurnal CA to Escrow Controller
    $routes->get('jurnal-ca-escrow', 'JurnalCaEscrowController::index', ['as' => 'settlement.jurnal-ca-escrow']);
    $routes->get('jurnal-ca-escrow/datatable', 'JurnalCaEscrowController::datatable', ['as' => 'settlement.jurnal-ca-escrow.datatable']);
    $routes->post('jurnal-ca-escrow/proses', 'JurnalCaEscrowController::proses', ['as' => 'settlement.jurnal-ca-escrow.proses']);
    $routes->get('jurnal-ca-escrow/status', 'JurnalCaEscrowController::status', ['as' => 'settlement.jurnal-ca-escrow.status']);
    
    // Jurnal Escrow to Biller PL Controller
    $routes->get('jurnal-escrow-biller-pl', 'JurnalEscrowBillerPlController::index', ['as' => 'settlement.jurnal-escrow-biller-pl']);
    $routes->get('jurnal-escrow-biller-pl/datatable', 'JurnalEscrowBillerPlController::datatable', ['as' => 'settlement.jurnal-escrow-biller-pl.datatable']);
    $routes->post('jurnal-escrow-biller-pl/proses', 'JurnalEscrowBillerPlController::proses', ['as' => 'settlement.jurnal-escrow-biller-pl.proses']);
});

$routes->get('get-csrf-token', 'CommonController::getCsrfToken');
$routes->get('get-new-csrf-token', 'Rekon\RekonController::getCsrfToken');

// ? Branch
$routes->get('/unit-kerja', 'User\UnitKerjaController::index', ['as' => 'unit-kerja.index']);
// ? Branch API
$routes->post('/optionsDivOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsDivOnly');
$routes->post('/optionsCabOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsCabOnly');
$routes->post('/options/unitKerjaAPI', 'User\UnitKerjaController::options');
$routes->get('/dataTables/unitKerjaAPI', 'User\UnitKerjaController::dataTables');
$routes->post('/post/unitKerjaAPI', 'User\UnitKerjaController::post');
$routes->post('/edit/unitKerjaAPI', 'User\UnitKerjaController::edit');
$routes->post('/delete/unitKerjaAPI', 'User\UnitKerjaController::delete');

// ? Role
$routes->get('/role', 'User\RoleController::index', ['as' => 'role.index']);
// ? Role API
$routes->post('/options/roleAPI', 'User\RoleController::options');
$routes->get('/dataTables/roleAPI', 'User\RoleController::dataTables');
$routes->post('/post/roleAPI', 'User\RoleController::post');
$routes->post('/edit/roleAPI', 'User\RoleController::edit');
$routes->post('/delete/roleAPI', 'User\RoleController::delete');
$routes->get('/permission/roleAPI/(:num)', 'User\RoleController::getPermissions/$1');
$routes->put('/assignPermission/roleAPI', 'User\RoleController::assignPermission');


// ? Currency Conversions
$routes->get('/currency-conversion', 'User\CurrencyConversionController::index', ['as' => 'currencyConversion.index']);
// ? Role Currency Conversions
$routes->get('/dataTables/currencyConversionAPI', 'User\CurrencyConversionController::dataTables');
$routes->post('/post/currencyConversionAPI', 'User\CurrencyConversionController::post');
$routes->post('/delete/currencyConversionAPI', 'User\CurrencyConversionController::delete');
$routes->post('/edit/currencyConversionAPI', 'User\CurrencyConversionController::edit');
$routes->post('/options/currencyConversionAPI', 'User\CurrencyConversionController::options');
$routes->get('/getCurrencyRateToUsd/currencyConversionAPI', 'User\CurrencyConversionController::getCurrencyRateToUsd');

// ? Underlying
$routes->get('/underlying', 'User\UnderlyingController::index', ['as' => 'underlying.index']);
// ? Role Underlying
$routes->get('/dataTables/underlyingAPI', 'User\UnderlyingController::dataTables');
$routes->get('/getAllCurrencyConversions/underlyingAPI', 'User\UnderlyingController::getAllCurrencyConversions');
$routes->get('/getCurrencyRateToUsd/underlyingAPI', 'User\UnderlyingController::getCurrencyRateToUsd');
$routes->post('/post/underlyingAPI', 'User\UnderlyingController::post');
$routes->post('/edit/underlyingAPI', 'User\UnderlyingController::edit');
$routes->post('/delete/underlyingAPI', 'User\UnderlyingController::delete');

// ? Transaction
$routes->get('/transaction/([A-Za-z0-9\-_]+)', 'User\TransactionController::index/$1', ['as' => 'transaction.index']);
// ? Role Transaction
$routes->get('/dataTables/transactionAPI', 'User\TransactionController::dataTables');
$routes->post('/post/transactionAPI', 'User\TransactionController::post');
$routes->post('/edit/transactionAPI', 'User\TransactionController::edit');
$routes->post('/delete/transactionAPI', 'User\TransactionController::delete');
$routes->post('/getNasabah', 'User\TransactionController::getNasabah');

// ? Permission	
$routes->get('/permission', 'User\PermissionController::index', ['as' => 'permission.index']);
// ? Permission API
$routes->post('/options/permissionAPI', 'User\PermissionController::options');
$routes->get('/dataTables/permissionAPI', 'User\PermissionController::dataTables');
$routes->post('/post/permissionAPI', 'User\PermissionController::post');
$routes->post('/edit/permissionAPI', 'User\PermissionController::edit');
$routes->post('/delete/permissionAPI', 'User\PermissionController::delete');

// ? User
$routes->get('/user', 'User\UserController::index', ['as' => 'user.index']);

// ? User API
$routes->get('/dataTables/userAPI', 'User\UserController::dataTables');
$routes->post('/post/userAPI', 'User\UserController::post');
$routes->post('/edit/userAPI', 'User\UserController::edit');
$routes->post('/updateStatus/userAPI', 'User\UserController::updateStatus');
$routes->post('/delete/userAPI', 'User\UserController::delete');
$routes->post('/resetPassword/userAPI', 'User\UserController::resetPassword');

// ? Log Viewer
$routes->get('/log/error', 'Log\LogError::index', ['as' => 'log.error']);
$routes->get('/log/activity', 'Log\LogActivityController::index', ['as' => 'log.activity']);
// ? Log Activity API
$routes->get('/dataTables/logActivityAPI', 'Log\LogActivityController::dataTables');
$routes->get('/show/logActivityAPI/(:num)', 'Log\LogActivityController::showLog/$1');

// ? Ticket
$routes->get('/ticket', 'User\TicketController::index', ['as' => 'ticket.index']);
// ? Ticket API
$routes->get('/dataTables/ticketAPI', 'User\TicketController::dataTables');
$routes->post('/post/ticketAPI', 'User\TicketController::post');
$routes->post('/edit/ticketAPI', 'User\TicketController::edit'); // <-- THIS LINE IS REQUIRED FOR MAKE OFFER
$routes->post('/delete/ticketAPI', 'User\TicketController::delete');
$routes->get('/show/ticketAPI/(:num)', 'User\TicketController::show/$1');
$routes->post('/sendMessage/ticketAPI', 'User\TicketController::sendMessage');
$routes->get('/fetchMessages/ticketAPI/(:num)', 'User\TicketController::fetchMessages/$1');
$routes->get('/ticketChat/(:any)', 'User\TicketController::showChat/$1');
$routes->post('/chatMessage/ticketAPI', 'User\TicketController::chatMessage');
$routes->get('/testChatMessage', 'User\TicketController::chatMessage');
$routes->post('/closeTicket/ticketAPI', 'User\TicketController::closeTicket');
$routes->post('/acceptOffer/ticketAPI', 'User\TicketController::acceptOffer');
$routes->get('/getAccountList/ticketAPI', 'User\TicketController::getAccountList'); // <-- add this line
$routes->get('/getVTicketWithUnitByTicketId', 'User\TicketController::getVTicketWithUnitByTicketId');
$routes->get('/ticket/export-pdf/(:any)', 'User\TicketController::exportPdf/$1');

// Add this line for stats/ticketAPI
$routes->get('/stats/ticketAPI', 'User\TicketController::stats');

$routes->get('/options/documentTypeAPI', 'User\DocumentTypeController::options');

// ? Transaksi Valas (Non-Underlying)
$routes->get('/transaksi_valas', 'User\TransaksiValasController::index', ['as' => 'transaksi_valas.index']);
$routes->get('/dataTables/valasAPI', 'User\TransaksiValasController::dataTables');
$routes->post('/post/valasAPI', 'User\TransaksiValasController::post');
$routes->post('/edit/valasAPI', 'User\TransaksiValasController::edit');
$routes->post('/delete/valasAPI', 'User\TransaksiValasController::delete');
$routes->get('/getMonthlyTotal/valasAPI', 'User\TransaksiValasController::getMonthlyTotalAPI');
$routes->get('/transaksi_valas/print/(:num)', 'User\TransaksiValasController::print/$1', ['as' => 'transaksi_valas.print']);

// Monitoring Transaksi
$routes->get('/monitoring_transaksi', 'User\MonitoringTransaksiController::index', ['as' => 'monitoring_transaksi.index']);
$routes->get('/dataTables/monitoringTransaksiAPI', 'User\MonitoringTransaksiController::dataTables');
$routes->get('/monitoring_transaksi/print', 'User\MonitoringTransaksiController::print', ['as' => 'monitoring_transaksi.print']);

// API: Get all currency conversions
$routes->get('/api/currency-conversions', 'User\CurrencyConversionController::getAll');
$routes->get('/api/jenis-transaksi', 'User\UnderlyingController::jenisTransaksi');


$routes->get('/rekon-bifast/rekap', 'Rekon\RekonController::index', ['as' => 'rekon-bifast.rekap']);
$routes->post('/rekon-bifast/upload', 'Rekon\RekonController::upload', ['as' => 'rekon-bifast.upload']);
/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
	require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
