<?php

namespace App\Controllers\Log;

use App\Libraries\LogEnum;
use CILogViewer\CILogViewer;
use App\Traits\HasLogActivity;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class LogError extends BaseController
{
	use HasLogActivity;

	// public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	// {
	// 	// Do Not Edit This Line
	// 	parent::initController($request, $response, $logger);

	// 	if (!in_array('view error', session('permissions'))) {

	// 		$this->response->redirect(base_url('dashboard'));
	// 	}
	// }

	public function index()
	{
		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman Daftar Error',
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		$logViewer = new CILogViewer();
		return $logViewer->showLogs();
	}
}
