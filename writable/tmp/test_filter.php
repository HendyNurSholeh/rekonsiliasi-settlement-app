<?php

if (!defined('ROOTPATH')) {
	define('ROOTPATH', realpath(__DIR__ . '/..' . DIRECTORY_SEPARATOR . '..') . DIRECTORY_SEPARATOR);
}

if (!defined('FCPATH')) {
	define('FCPATH', ROOTPATH . 'public' . DIRECTORY_SEPARATOR);
}

require ROOTPATH . 'app/Config/Boot/development.php';
require ROOTPATH . 'vendor/autoload.php';
require ROOTPATH . 'app/Common.php';
require ROOTPATH . 'vendor/codeigniter4/framework/system/Common.php';
require ROOTPATH . 'app/Config/Services.php';

use Config\Services;

$db = Services::database();
$builder = $db->table('t_akselgatefwd_callback_log');
$builder->select('COUNT(*) as total');
$builder->where('created_at >=', '2025-10-08 00:00:00');
$builder->where('created_at <=', '2025-10-08 23:59:59');
$builder->like('kd_settle', 'aaaaaaaaaaa', 'both');

$result = $builder->get()->getRowArray();
var_dump($result);
