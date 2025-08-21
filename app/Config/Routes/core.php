<?php

/**
 * Core Application Routes
 * 
 * File ini berisi routes untuk:
 * - Authentication (Login/Logout)
 * - Dashboard
 * - Profile Management
 * - Common/Utility Routes
 * - CSRF Token Management
 */

// ============================================================================
// AUTHENTICATION ROUTES
// ============================================================================
// Routes untuk login, logout, dan authentikasi

$routes->addRedirect('/', '/login');
$routes->get('/login', 'Auth\Login::index', ['as' => 'login']);
$routes->post('/login', 'Auth\Login::index');
$routes->get('/logout', 'Auth\Login::logout', ['as' => 'logout']);
$routes->get('/refresh/captcha', 'Auth\Login::refreshCaptcha', ['as' => 'refresh.captcha']);

// ============================================================================
// DASHBOARD & PROFILE ROUTES
// ============================================================================
// Routes untuk halaman utama dan profil user

$routes->get('/dashboard', 'Dashboard::index', ['as' => 'dashboard']);
$routes->get('/profile', 'User\Profile::index', ['as' => 'user.profile']);
$routes->post('/profile', 'User\Profile::index');

// ============================================================================
// COMMON UTILITY ROUTES
// ============================================================================
// Routes untuk keperluan umum seperti CSRF token

$routes->get('get-csrf-token', 'CommonController::getCsrfToken');
$routes->get('get-new-csrf-token', 'Rekon\RekonController::getCsrfToken');
