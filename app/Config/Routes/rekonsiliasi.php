<?php

/**
 * Rekonsiliasi Settlement Routes
 * 
 * File ini berisi routes untuk:
 * - Setup & Persiapan Rekonsiliasi (Setup, Step1, Step2, Step3)
 * - Process Rekonsiliasi (Laporan, Detail vs Rekap, Direct/Indirect Jurnal)
 * - Dispute Management
 * - Report Generation
 */

// ============================================================================
// REKONSILIASI SETTLEMENT ROUTES GROUP
// ============================================================================
// Namespace: App\Controllers\Rekon

$routes->group('rekon', ['namespace' => 'App\Controllers\Rekon'], function($routes) {
    
    // ========================================================================
    // PERSIAPAN REKONSILIASI
    // ========================================================================
    // Routes untuk setup dan persiapan data rekonsiliasi
    
    // Setup Controller - untuk index.blade.php (Persiapan folder)
    $routes->get('/', 'Persiapan\SetupController::index', ['as' => 'rekon.index']);
    $routes->post('create', 'Persiapan\SetupController::create', ['as' => 'rekon.create']);
    $routes->post('checkDate', 'Persiapan\SetupController::checkDate', ['as' => 'rekon.checkDate']);
    $routes->post('resetProcess', 'Persiapan\SetupController::resetProcess', ['as' => 'rekon.resetProcess']);
    
    // Step 1 Controller - untuk step1.blade.php (Upload Files)
    $routes->get('step1', 'Persiapan\Step1Controller::index', ['as' => 'rekon.step1']);
    $routes->post('step1/upload', 'Persiapan\Step1Controller::uploadFiles', ['as' => 'rekon.step1.upload']);
    $routes->post('step1/validate', 'Persiapan\Step1Controller::validateFiles', ['as' => 'rekon.step1.validate']);
    $routes->post('step1/process', 'Persiapan\Step1Controller::processDataUpload', ['as' => 'rekon.step1.process']);
    $routes->post('step1/status', 'Persiapan\Step1Controller::checkUploadStatus', ['as' => 'rekon.step1.status']);
    $routes->get('step1/stats', 'Persiapan\Step1Controller::getUploadStats', ['as' => 'rekon.step1.stats']);
    $routes->get('step1/mapping', 'Persiapan\Step1Controller::checkProductMapping', ['as' => 'rekon.step1.mapping']);
    
    // Step 2 Controller - untuk step2.blade.php (Validasi Data)
    $routes->get('step2', 'Persiapan\Step2Controller::index', ['as' => 'rekon.step2']);
    $routes->post('step2/validate', 'Persiapan\Step2Controller::processValidation', ['as' => 'rekon.step2.validate']);
    $routes->post('step2/proses-ulang', 'Persiapan\Step2Controller::prosesUlang', ['as' => 'rekon.step2.proses-ulang']);
    $routes->get('step2/preview', 'Persiapan\Step2Controller::getDataPreview', ['as' => 'rekon.step2.preview']);
    $routes->get('step2/stats', 'Persiapan\Step2Controller::getUploadStats', ['as' => 'rekon.step2.stats']);
    
    // Step 3 Controller - untuk step3.blade.php (Proses Rekonsiliasi)
    $routes->get('step3', 'Persiapan\Step3Controller::index', ['as' => 'rekon.step3']);
    $routes->post('step3/process', 'Persiapan\Step3Controller::processReconciliation', ['as' => 'rekon.step3.process']);
    $routes->get('step3/progress', 'Persiapan\Step3Controller::getReconciliationProgress', ['as' => 'rekon.step3.progress']);
    $routes->post('step3/reports', 'Persiapan\Step3Controller::generateReports', ['as' => 'rekon.step3.reports']);
    $routes->get('step3/download', 'Persiapan\Step3Controller::downloadReport', ['as' => 'rekon.step3.download']);
    
    // ========================================================================
    // PROCESS REKONSILIASI
    // ========================================================================
    // Routes untuk proses rekonsiliasi dan penanganan dispute
    
    $routes->group('process', ['namespace' => 'App\Controllers\Rekon\Process'], function($routes) {
        
        // ====================================================================
        // LAPORAN TRANSAKSI DETAIL
        // ====================================================================
        // Untuk menampilkan dan mengelola detail transaksi
        
        $routes->get('laporan-transaksi-detail', 'LaporanTransaksiDetailController::index', ['as' => 'rekon.process.laporan-transaksi-detail']);
        $routes->get('laporan-transaksi-detail/datatable', 'LaporanTransaksiDetailController::datatable', ['as' => 'rekon.process.laporan-transaksi-detail.datatable']);
        $routes->post('laporan-transaksi-detail/datatable', 'LaporanTransaksiDetailController::datatable', ['as' => 'rekon.process.laporan-transaksi-detail.datatable.post']);
        $routes->post('laporan-transaksi-detail/detail', 'LaporanTransaksiDetailController::getDisputeDetail', ['as' => 'rekon.process.laporan-transaksi-detail.detail']);
        $routes->post('laporan-transaksi-detail/update', 'LaporanTransaksiDetailController::updateDispute', ['as' => 'rekon.process.laporan-transaksi-detail.update']);
        $routes->post('laporan-transaksi-detail/verif-settlement', 'LaporanTransaksiDetailController::verifSettlement', ['as' => 'rekon.process.laporan-transaksi-detail.verif-settlement']);
        
        // ====================================================================
        // DETAIL VS REKAP
        // ====================================================================
        // Untuk membandingkan data detail dengan rekap
        
        $routes->get('detail-vs-rekap', 'DetailVsRekapController::index', ['as' => 'rekon.process.detail-vs-rekap']);
        $routes->get('detail-vs-rekap/datatable', 'DetailVsRekapController::datatable', ['as' => 'rekon.process.detail-vs-rekap.datatable']);
        $routes->post('detail-vs-rekap/datatable', 'DetailVsRekapController::datatable', ['as' => 'rekon.process.detail-vs-rekap.datatable.post']);
        $routes->get('detail-vs-rekap/statistics', 'DetailVsRekapController::statistics', ['as' => 'rekon.process.detail-vs-rekap.statistics']);
        
        // ====================================================================
        // DIRECT JURNAL (REKAP & DISPUTE)
        // ====================================================================
        // Untuk menangani jurnal langsung dan penyelesaian dispute
        
        $routes->get('direct-jurnal-rekap', 'DirectJurnalController::rekap', ['as' => 'rekon.process.direct-jurnal-rekap']);
        $routes->get('penyelesaian-dispute', 'DirectJurnalController::dispute', ['as' => 'rekon.process.penyelesaian-dispute']);
        $routes->get('direct-jurnal/dispute/datatable', 'DirectJurnalController::disputeDataTable', ['as' => 'rekon.process.dispute.datatable']);
        $routes->post('direct-jurnal/dispute/datatable', 'DirectJurnalController::disputeDataTable', ['as' => 'rekon.process.dispute.datatable.post']);
        $routes->post('direct-jurnal/dispute/detail', 'DirectJurnalController::getDisputeDetail', ['as' => 'rekon.process.dispute.detail']);
        $routes->post('direct-jurnal/dispute/update', 'DirectJurnalController::updateDispute', ['as' => 'rekon.process.dispute.update']);
        
        // ====================================================================
        // INDIRECT JURNAL REKAP
        // ====================================================================
        // Untuk menangani jurnal tidak langsung dan konfirmasi setoran
        
        $routes->get('indirect-jurnal-rekap', 'IndirectJurnalRekapController::index', ['as' => 'rekon.process.indirect-jurnal-rekap']);
        $routes->get('indirect-jurnal-rekap/datatable', 'IndirectJurnalRekapController::datatable', ['as' => 'rekon.process.indirect-jurnal-rekap.datatable']);
        $routes->post('indirect-jurnal-rekap/datatable', 'IndirectJurnalRekapController::datatable', ['as' => 'rekon.process.indirect-jurnal-rekap.datatable.post']);
        $routes->post('indirect-jurnal-rekap/konfirmasi', 'IndirectJurnalRekapController::konfirmasiSetoran', ['as' => 'rekon.process.indirect-jurnal-rekap.konfirmasi']);
        $routes->post('indirect-jurnal-rekap/update-sukses', 'IndirectJurnalRekapController::updateSukses', ['as' => 'rekon.process.indirect-jurnal-rekap.update-sukses']);
        
        // ====================================================================
        // INDIRECT DISPUTE
        // ====================================================================
        // Untuk menangani dispute pada transaksi tidak langsung
        
        $routes->get('indirect-dispute', 'IndirectDisputeController::index', ['as' => 'rekon.process.indirect-dispute']);
        $routes->get('indirect-dispute/datatable', 'IndirectDisputeController::datatable', ['as' => 'rekon.process.indirect-dispute.datatable']);
        $routes->post('indirect-dispute/datatable', 'IndirectDisputeController::datatable', ['as' => 'rekon.process.indirect-dispute.datatable.post']);
        $routes->post('indirect-dispute/detail', 'IndirectDisputeController::getDetail', ['as' => 'rekon.process.indirect-dispute.detail']);
        $routes->post('indirect-dispute/update', 'IndirectDisputeController::update', ['as' => 'rekon.process.indirect-dispute.update']);
    });
});
