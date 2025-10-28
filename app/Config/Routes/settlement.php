<?php

/**
 * Settlement Routes
 * 
 * File ini berisi routes untuk:
 * - Buat Jurnal Settlement
 * - Approve Jurnal Settlement
 * - Jurnal CA to Escrow (Transfer dana dari CA ke Escrow via AKSEL Gateway)
 * - Jurnal Escrow to Biller PL (Transfer dana dari Escrow ke Biller)
 * - AKSEL Gateway Callback (Public endpoint untuk menerima callback dari AKSEL Gateway)
 */

// ============================================================================
// SETTLEMENT ROUTES GROUP
// ============================================================================
// Namespace: App\Controllers\Settlement

$routes->group('settlement', ['namespace' => 'App\Controllers\Settlement'], function($routes) {
    
    // ========================================================================
    // BUAT JURNAL SETTLEMENT
    // ========================================================================
    // Routes untuk membuat jurnal settlement baru
    
    $routes->get('buat-jurnal', 'BuatJurnalController::index', ['as' => 'settlement.buat-jurnal']);
    $routes->get('buat-jurnal/datatable', 'BuatJurnalController::datatable', ['as' => 'settlement.buat-jurnal.datatable']);
    $routes->post('buat-jurnal/datatable', 'BuatJurnalController::datatable', ['as' => 'settlement.buat-jurnal.datatable.post']);
    $routes->post('buat-jurnal/validate', 'BuatJurnalController::validateSettlement', ['as' => 'settlement.buat-jurnal.validate']);
    $routes->post('buat-jurnal/create', 'BuatJurnalController::createJurnal', ['as' => 'settlement.buat-jurnal.create']);
    
    // ========================================================================
    // APPROVE JURNAL SETTLEMENT
    // ========================================================================
    // Routes untuk approval jurnal settlement
    
    $routes->get('approve-jurnal', 'ApproveJurnalController::index', ['as' => 'settlement.approve-jurnal']);
    $routes->get('approve-jurnal/datatable', 'ApproveJurnalController::datatable', ['as' => 'settlement.approve-jurnal.datatable']);
    $routes->post('approve-jurnal/datatable', 'ApproveJurnalController::datatable', ['as' => 'settlement.approve-jurnal.datatable.post']);
    $routes->post('approve-jurnal/detail', 'ApproveJurnalController::getDetailJurnal', ['as' => 'settlement.approve-jurnal.detail']);
    $routes->post('approve-jurnal/process', 'ApproveJurnalController::processApproval', ['as' => 'settlement.approve-jurnal.process']);
    $routes->get('approve-jurnal/summary', 'ApproveJurnalController::getSummary', ['as' => 'settlement.approve-jurnal.summary']);
    
    // ========================================================================
    // JURNAL CA TO ESCROW
    // ========================================================================
    // Routes untuk transfer dana dari Customer Account ke Escrow
    
    $routes->get('jurnal-ca-escrow', 'JurnalCaEscrowController::index', ['as' => 'settlement.jurnal-ca-escrow']);
    $routes->get('jurnal-ca-escrow/datatable', 'JurnalCaEscrowController::datatable', ['as' => 'settlement.jurnal-ca-escrow.datatable']);
    $routes->post('jurnal-ca-escrow/proses', 'JurnalCaEscrowController::proses', ['as' => 'settlement.jurnal-ca-escrow.proses']);
    $routes->get('jurnal-ca-escrow/callback-logs/(:segment)', 'JurnalCaEscrowController::getCallbackLogs/$1', ['as' => 'settlement.jurnal-ca-escrow.callback-logs']);
    $routes->get('jurnal-ca-escrow/callback-logs-by-ref/(:segment)', 'JurnalCaEscrowController::getCallbackLogsByRef/$1', ['as' => 'settlement.jurnal-ca-escrow.callback-logs-by-ref']);
    $routes->get('jurnal-ca-escrow/get-callback-by-request-id/(:segment)', 'JurnalCaEscrowController::getCallbackByRequestId/$1', ['as' => 'settlement.jurnal-ca-escrow.get-callback-by-request-id']);
    $routes->get('jurnal-ca-escrow/get-callback-detail/(:segment)', 'JurnalCaEscrowController::getCallbackDetail/$1', ['as' => 'settlement.jurnal-ca-escrow.get-callback-detail']);
    
    // ========================================================================
    // JURNAL ESCROW TO BILLER PL
    // ========================================================================
    // Routes untuk transfer dana dari Escrow ke Biller (Pemerintah Lokal)
    
    $routes->get('jurnal-escrow-biller-pl', 'JurnalEscrowBillerPlController::index', ['as' => 'settlement.jurnal-escrow-biller-pl']);
    $routes->get('jurnal-escrow-biller-pl/datatable', 'JurnalEscrowBillerPlController::datatable', ['as' => 'settlement.jurnal-escrow-biller-pl.datatable']);
    $routes->post('jurnal-escrow-biller-pl/proses', 'JurnalEscrowBillerPlController::proses', ['as' => 'settlement.jurnal-escrow-biller-pl.proses']);
    $routes->get('jurnal-escrow-biller-pl/get-callback-by-request-id/(:segment)', 'JurnalEscrowBillerPlController::getCallbackByRequestId/$1', ['as' => 'settlement.jurnal-escrow-biller-pl.get-callback-by-request-id']);
    $routes->get('jurnal-escrow-biller-pl/get-callback-detail/(:segment)', 'JurnalEscrowBillerPlController::getCallbackDetail/$1', ['as' => 'settlement.jurnal-escrow-biller-pl.get-callback-detail']);
});

// ============================================================================
// AKSEL GATEWAY CALLBACK ROUTES
// ============================================================================
// Callback endpoint untuk menerima response dari AKSEL Gateway
// Endpoint ini di-exempt dari auth dan CSRF (lihat Config\Filters.php)

$routes->group('aksel-gate', ['namespace' => 'App\Controllers\Settlement'], function($routes) {
    $routes->get('callback', 'AkselGateCallbackController::index', ['as' => 'aksel-gate.callback']);
});
