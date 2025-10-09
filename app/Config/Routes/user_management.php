<?php

/**
 * User Management Routes
 * 
 * File ini berisi routes untuk:
 * - Unit Kerja Management (Branch/Division)
 * - Role Management (User Roles)
 * - Permission Management (User Permissions)
 * - User Management (CRUD Users)
 * - Log Viewer (Error & Activity Logs)
 */

// ============================================================================
// UNIT KERJA (BRANCH) MANAGEMENT
// ============================================================================
// Routes untuk mengelola unit kerja/cabang

$routes->get('/unit-kerja', 'User\UnitKerjaController::index', ['as' => 'unit-kerja.index']);

// Unit Kerja API Routes
$routes->post('/optionsDivOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsDivOnly');
$routes->post('/optionsCabOnly/unitKerjaAPI', 'User\UnitKerjaController::optionsCabOnly');
$routes->post('/options/unitKerjaAPI', 'User\UnitKerjaController::options');
$routes->get('/dataTables/unitKerjaAPI', 'User\UnitKerjaController::dataTables');
$routes->post('/post/unitKerjaAPI', 'User\UnitKerjaController::post');
$routes->post('/edit/unitKerjaAPI', 'User\UnitKerjaController::edit');
$routes->post('/delete/unitKerjaAPI', 'User\UnitKerjaController::delete');

// ============================================================================
// ROLE MANAGEMENT
// ============================================================================
// Routes untuk mengelola role/peran user

$routes->get('/role', 'User\RoleController::index', ['as' => 'role.index']);

// Role API Routes
$routes->post('/options/roleAPI', 'User\RoleController::options');
$routes->get('/dataTables/roleAPI', 'User\RoleController::dataTables');
$routes->post('/post/roleAPI', 'User\RoleController::post');
$routes->post('/edit/roleAPI', 'User\RoleController::edit');
$routes->post('/delete/roleAPI', 'User\RoleController::delete');
$routes->get('/permission/roleAPI/(:num)', 'User\RoleController::getPermissions/$1');
$routes->put('/assignPermission/roleAPI', 'User\RoleController::assignPermission');

// ============================================================================
// PERMISSION MANAGEMENT
// ============================================================================
// Routes untuk mengelola hak akses/permission

$routes->get('/permission', 'User\PermissionController::index', ['as' => 'permission.index']);

// Permission API Routes
$routes->post('/options/permissionAPI', 'User\PermissionController::options');
$routes->get('/dataTables/permissionAPI', 'User\PermissionController::dataTables');
$routes->post('/post/permissionAPI', 'User\PermissionController::post');
$routes->post('/edit/permissionAPI', 'User\PermissionController::edit');
$routes->post('/delete/permissionAPI', 'User\PermissionController::delete');

// ============================================================================
// USER MANAGEMENT
// ============================================================================
// Routes untuk mengelola user/pengguna sistem

$routes->get('/user', 'User\UserController::index', ['as' => 'user.index']);

// User API Routes
$routes->get('/dataTables/userAPI', 'User\UserController::dataTables');
$routes->post('/post/userAPI', 'User\UserController::post');
$routes->post('/edit/userAPI', 'User\UserController::edit');
$routes->post('/updateStatus/userAPI', 'User\UserController::updateStatus');
$routes->post('/delete/userAPI', 'User\UserController::delete');
$routes->post('/resetPassword/userAPI', 'User\UserController::resetPassword');

// ============================================================================
// LOG VIEWER
// ============================================================================
// Routes untuk melihat log error dan aktivitas sistem

$routes->get('/log/error', 'Log\LogError::index', ['as' => 'log.error']);
$routes->get('/log/activity', 'Log\LogActivityController::index', ['as' => 'log.activity']);

// Log Activity API Routes
$routes->get('/dataTables/logActivityAPI', 'Log\LogActivityController::dataTables');
$routes->get('/show/logActivityAPI/(:num)', 'Log\LogActivityController::showLog/$1');

// ============================================================================
// AKSELGATE LOG VIEWER
// ============================================================================
// Routes untuk melihat log transaksi Akselgate API

$routes->get('/log/akselgate', 'Log\AkselgateLogController::index', ['as' => 'log.akselgate']);
$routes->post('/log/akselgate/datatable', 'Log\AkselgateLogController::datatable');
$routes->get('/log/akselgate/detail/(:num)', 'Log\AkselgateLogController::detail/$1');

// ============================================================================
// AKSELGATE FWD CALLBACK LOG VIEWER
// ============================================================================
// Routes untuk melihat log callback dari Akselgate FWD Gateway

$routes->get('/log/callback', 'Log\AkselgateFwdCallbackLogController::index', ['as' => 'log.callback']);
$routes->post('/log/callback/datatable', 'Log\AkselgateFwdCallbackLogController::datatable');
$routes->get('/log/callback/detail/(:num)', 'Log\AkselgateFwdCallbackLogController::detail/$1');
