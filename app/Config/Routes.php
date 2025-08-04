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
    // Setup Controller - untuk index.blade.php
    $routes->get('/', 'RekonSetupController::index', ['as' => 'rekon.index']);
    $routes->post('create', 'RekonSetupController::create', ['as' => 'rekon.create']);
    $routes->post('checkDate', 'RekonSetupController::checkDate', ['as' => 'rekon.checkDate']);
    $routes->post('resetProcess', 'RekonSetupController::resetProcess', ['as' => 'rekon.resetProcess']);
    
    // CSRF Token Refresh
    $routes->get('get-csrf-token', 'RekonStep1Controller::getCSRFToken', ['as' => 'rekon.csrf-token']);
    
    // Step 1 Controller - untuk step1.blade.php (Upload Files)
    $routes->get('step1', 'RekonStep1Controller::index', ['as' => 'rekon.step1']);
    $routes->post('step1/upload', 'RekonStep1Controller::uploadFiles', ['as' => 'rekon.step1.upload']);
    $routes->post('step1/validate', 'RekonStep1Controller::validateFiles', ['as' => 'rekon.step1.validate']);
    $routes->post('step1/process', 'RekonStep1Controller::processDataUpload', ['as' => 'rekon.step1.process']);
    $routes->post('step1/status', 'RekonStep1Controller::checkUploadStatus', ['as' => 'rekon.step1.status']);
    $routes->get('step1/stats', 'RekonStep1Controller::getUploadStats', ['as' => 'rekon.step1.stats']);
    $routes->get('step1/mapping', 'RekonStep1Controller::checkProductMapping', ['as' => 'rekon.step1.mapping']);
    
    // Step 2 Controller - untuk step2.blade.php (Validasi Data)
    $routes->get('step2', 'RekonStep2Controller::index', ['as' => 'rekon.step2']);
    $routes->post('step2/validate', 'RekonStep2Controller::processValidation', ['as' => 'rekon.step2.validate']);
    $routes->get('step2/preview', 'RekonStep2Controller::getDataPreview', ['as' => 'rekon.step2.preview']);
    $routes->get('step2/stats', 'RekonStep2Controller::getUploadStats', ['as' => 'rekon.step2.stats']);
    
    // Step 3 Controller - untuk step3.blade.php (Proses Rekonsiliasi)
    $routes->get('step3', 'RekonStep3Controller::index', ['as' => 'rekon.step3']);
    $routes->post('step3/process', 'RekonStep3Controller::processReconciliation', ['as' => 'rekon.step3.process']);
    $routes->get('step3/progress', 'RekonStep3Controller::getReconciliationProgress', ['as' => 'rekon.step3.progress']);
    $routes->post('step3/reports', 'RekonStep3Controller::generateReports', ['as' => 'rekon.step3.reports']);
    $routes->get('step3/download', 'RekonStep3Controller::downloadReport', ['as' => 'rekon.step3.download']);
    
    // Process Controller - untuk Tahap 3 - Proses Rekonsiliasi menu features
    $routes->get('process/detail-vs-rekap', 'RekonProcessController::detailVsRekap', ['as' => 'rekon.process.detail-vs-rekap']);
    $routes->get('process/detail-vs-rekap/datatable', 'RekonProcessController::detailVsRekapDataTable', ['as' => 'rekon.process.detail-vs-rekap.datatable']);
    $routes->post('process/detail-vs-rekap/datatable', 'RekonProcessController::detailVsRekapDataTable', ['as' => 'rekon.process.detail-vs-rekap.datatable.post']);
    $routes->get('process/direct-jurnal-rekap', 'RekonProcessController::directJurnalRekap', ['as' => 'rekon.process.direct-jurnal-rekap']);
    $routes->get('process/penyelesaian-dispute', 'RekonProcessController::disputeResolution', ['as' => 'rekon.process.penyelesaian-dispute']);
    
    // Dispute resolution AJAX routes
    $routes->post('process/direct-jurnal/dispute/detail', 'RekonProcessController::getDisputeDetail', ['as' => 'rekon.process.dispute.detail']);
    $routes->post('process/direct-jurnal/dispute/update', 'RekonProcessController::updateDispute', ['as' => 'rekon.process.dispute.update']);
    $routes->get('process/direct-jurnal/dispute/datatable', 'RekonProcessController::disputeDataTable', ['as' => 'rekon.process.dispute.datatable']);
    $routes->post('process/direct-jurnal/dispute/datatable', 'RekonProcessController::disputeDataTable', ['as' => 'rekon.process.dispute.datatable.post']);
    
    // Indirect Jurnal routes
    $routes->get('process/indirect-jurnal-rekap', 'RekonProcessController::indirectJurnalRekap', ['as' => 'rekon.process.indirect-jurnal-rekap']);
    $routes->get('process/indirect-jurnal-rekap/datatable', 'RekonProcessController::indirectJurnalRekapDataTable', ['as' => 'rekon.process.indirect-jurnal-rekap.datatable']);
    $routes->post('process/indirect-jurnal-rekap/konfirmasi', 'RekonProcessController::konfirmasiSetoran', ['as' => 'rekon.process.indirect-jurnal-rekap.konfirmasi']);
    $routes->get('process/indirect-dispute', 'RekonProcessController::indirectDispute', ['as' => 'rekon.process.indirect-dispute']);
    $routes->get('process/indirect-dispute/datatable', 'RekonProcessController::indirectDisputeDataTable', ['as' => 'rekon.process.indirect-dispute.datatable']);
    $routes->get('process/indirect-dispute/detail', 'RekonProcessController::getIndirectDisputeDetail', ['as' => 'rekon.process.indirect-dispute.detail']);
    $routes->post('process/indirect-dispute/update', 'RekonProcessController::updateIndirectDispute', ['as' => 'rekon.process.indirect-dispute.update']);
    $routes->get('process/konfirmasi-saldo-ca', 'RekonProcessController::konfirmasiSaldoCA', ['as' => 'rekon.process.konfirmasi-saldo-ca']);
    $routes->get('process/konfirmasi-saldo-ca/datatable', 'RekonProcessController::konfirmasiSaldoCADataTable', ['as' => 'rekon.process.konfirmasi-saldo-ca.datatable']);
    $routes->get('process/konfirmasi-saldo-ca/summary', 'RekonProcessController::konfirmasiSaldoCASummary', ['as' => 'rekon.process.konfirmasi-saldo-ca.summary']);
    $routes->post('process/konfirmasi-saldo-ca', 'RekonProcessController::submitKonfirmasiSaldoCA', ['as' => 'rekon.process.konfirmasi-saldo-ca.submit']);
    $routes->post('process/konfirmasi-saldo-ca/bulk', 'RekonProcessController::bulkKonfirmasiSaldoCA', ['as' => 'rekon.process.konfirmasi-saldo-ca.bulk']);
    
    // CSRF Token refresh route
    $routes->get('process/get-csrf-token', 'RekonProcessController::getCSRFToken', ['as' => 'rekon.process.csrf-token']);

    // Report Routes (existing)
    $routes->get('reports', 'RekonReport::index', ['as' => 'rekon.reports']);
    $routes->get('reports/(:segment)', 'RekonReport::index/$1', ['as' => 'rekon.reports.date']);
    $routes->get('reports/download/(:segment)/(:segment)', 'RekonReport::downloadExcel/$1/$2', ['as' => 'rekon.reports.download']);
});

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
