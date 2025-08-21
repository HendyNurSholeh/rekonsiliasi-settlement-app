<?php

/**
 * Rekon BiFast Routes
 * 
 * File ini berisi routes untuk:
 * - Rekonsiliasi BiFast (Main Routes)
 * 
 * KHUSUS UNTUK DEVELOPER BIFAST
 * File ini dapat dikerjakan secara terpisah tanpa mengganggu routes lain
 */

// ============================================================================
// REKON BIFAST ROUTES GROUP
// ============================================================================
// Namespace: App\Controllers\RekonBifast

$routes->group('rekon-bifast', ['namespace' => 'App\Controllers\RekonBifast'], function($routes) {
    
    // ========================================================================
    // MAIN BIFAST ROUTES
    // ========================================================================
    // Routes utama untuk fitur Rekon BiFast
    $routes->get('rekap', 'RekonBifastController::index', ['as' => 'rekon-bifast.rekap']);
    $routes->post('upload', 'RekonBifastController::upload', ['as' => 'rekon-bifast.upload']);
});
