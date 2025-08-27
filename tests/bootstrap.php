<?php

// Include CodeIgniter test bootstrap first
require_once __DIR__ . '/../vendor/codeigniter4/framework/system/Test/bootstrap.php';

// Test bootstrap file to define mock CodeIgniter functions
if (!function_exists('validation_list_errors')) {
    function validation_list_errors() {
        return '<p>The field is required.</p>';
    }
}

if (!function_exists('csrf_hash')) {
    function csrf_hash() {
        return 'test_csrf_hash';
    }
}

if (!function_exists('base_url')) {
    function base_url($path = '') {
        return 'http://localhost/' . ltrim($path, '/');
    }
}

if (!function_exists('session')) {
    function session($key = null) {
        if ($key === null) {
            return new class {
                public function get($key) {
                    return $_SESSION[$key] ?? null;
                }
                public function set($data) {
                    foreach ($data as $k => $v) {
                        $_SESSION[$k] = $v;
                    }
                }
            };
        }
        return $_SESSION[$key] ?? null;
    }
}
