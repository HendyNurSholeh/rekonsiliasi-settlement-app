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

// ============================================================================
// MODULAR ROUTES - LOAD FROM SEPARATE FILES
// ============================================================================
// Routes dipisahkan berdasarkan modul

// Core Application Routes (Auth, Dashboard, Profile, Common)
require_once APPPATH . 'Config/Routes/core.php';

// Rekonsiliasi Settlement Routes (Setup, Process, Dispute Management)
require_once APPPATH . 'Config/Routes/rekonsiliasi.php';

// Settlement Routes (Jurnal, Approval, Transfer Dana)
require_once APPPATH . 'Config/Routes/settlement.php';

// User Management Routes (User, Role, Permission, Unit Kerja, Log Viewer)
require_once APPPATH . 'Config/Routes/user_management.php';

// Rekon BiFast Routes (BiFast)
require_once APPPATH . 'Config/Routes/rekon_bifast.php';

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
