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

		// Filter hanya log level CRITICAL
		$logViewer = new CILogViewer();
		$logs = $this->getFilteredLogs($logViewer, ['CRITICAL']);
		
		return view('CILogViewer\Views\logs', $logs);
	}

	/**
	 * Get logs with specific level filter
	 * @param CILogViewer $logViewer
	 * @param array $allowedLevels - Array of log levels to show (e.g., ['CRITICAL', 'ERROR'])
	 * @return array
	 */
	private function getFilteredLogs($logViewer, $allowedLevels = ['CRITICAL'])
	{
		$request = \Config\Services::request();
		
		// Handle delete command
		if (!is_null($request->getGet("del"))) {
			$this->deleteLogFile(base64_decode($request->getGet("del")));
			$uri = $request->uri->getPath();
			return redirect()->to('/' . $uri);
		}

		// Handle download command
		$dlFile = $request->getGet("dl");
		if (!is_null($dlFile)) {
			$logFolderPath = WRITEPATH . 'logs/';
			$file = $logFolderPath . basename(base64_decode($dlFile));
			if (file_exists($file)) {
				$this->downloadLogFile($file);
			}
		}

		// Get log files
		$logFolderPath = WRITEPATH . 'logs/';
		$logFilePattern = "log-*.log";
		$files = $this->getLogFiles($logFolderPath, $logFilePattern);

		// Determine current file
		$fileName = $request->getGet("f");
		if (!is_null($fileName)) {
			$currentFile = $logFolderPath . basename(base64_decode($fileName));
		} else if (!empty($files)) {
			$currentFile = $logFolderPath . $files[0];
		} else {
			$currentFile = null;
		}

		// Process logs with filter
		$logs = [];
		if (!is_null($currentFile) && file_exists($currentFile)) {
			$fileSize = filesize($currentFile);
			$maxLogSize = 52428800; // 50MB

			if (is_int($fileSize) && $fileSize > $maxLogSize) {
				$logs = null;
			} else {
				$rawLogs = file($currentFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
				$logs = $this->processLogsWithFilter($rawLogs, $allowedLevels);
			}
		}

		return [
			'logs' => $logs,
			'files' => !empty($files) ? $files : [],
			'currentFile' => !is_null($currentFile) ? basename($currentFile) : "",
			'filterLevels' => $allowedLevels // Pass filter info to view
		];
	}

	/**
	 * Process logs and filter by level
	 */
	private function processLogsWithFilter($logs, $allowedLevels)
	{
		if (is_null($logs)) {
			return null;
		}

		$levelsIcon = [
			'CRITICAL' => 'glyphicon glyphicon-warning-sign',
			'ERROR' => 'glyphicon glyphicon-warning-sign',
			'WARNING' => 'glyphicon glyphicon-warning-sign',
			'NOTICE' => 'glyphicon glyphicon-warning-sign',
			'INFO'  => 'glyphicon glyphicon-info-sign',
			'DEBUG' => 'glyphicon glyphicon-exclamation-sign',
			'EMERGENCY' => 'glyphicon glyphicon-warning-sign',
			'ALERT' => 'glyphicon glyphicon-warning-sign',
		];

		$levelClasses = [
			'CRITICAL' => 'danger',
			'ERROR' => 'danger',
			'WARNING' => 'warning',
			'NOTICE' => 'info',
			'INFO'  => 'info',
			'DEBUG' => 'warning',
			'EMERGENCY' => 'danger',
			'ALERT' => 'warning',
		];

		$logLinePattern = '/^([A-Z]+)\s*\-\s*([\-\d]+\s+[\:\d]+)\s*\-\->\s*(.+)$/';
		$maxStringLength = 300;
		$superLog = [];

		foreach ($logs as $log) {
			$matches = [];
			if (preg_match($logLinePattern, $log, $matches)) {
				$level = $matches[1];
				$logDate = $matches[2];
				$logMessage = $matches[3];

				// Filter: only include allowed levels
				if (in_array($level, $allowedLevels)) {
					$data = [
						"level" => $level,
						"date" => $logDate,
						"icon" => $levelsIcon[$level] ?? 'glyphicon glyphicon-minus',
						"class" => $levelClasses[$level] ?? 'muted',
					];

					if (strlen($logMessage) > $maxStringLength) {
						$data['content'] = substr($logMessage, 0, $maxStringLength);
						$data["extra"] = substr($logMessage, ($maxStringLength + 1));
					} else {
						$data["content"] = $logMessage;
					}

					array_push($superLog, $data);
				}
			} else if (!empty($superLog)) {
				// Continuation of previous log line
				$prevLog = $superLog[count($superLog) - 1];
				$extra = (array_key_exists("extra", $prevLog)) ? $prevLog["extra"] : "";
				$prevLog["extra"] = $extra . "<br>" . $log;
				$superLog[count($superLog) - 1] = $prevLog;
			}
		}

		return $superLog;
	}

	/**
	 * Get log files from directory
	 */
	private function getLogFiles($logFolderPath, $logFilePattern)
	{
		$files = glob($logFolderPath . $logFilePattern);
		$files = array_reverse($files);
		$files = array_filter($files, 'is_file');
		return array_map('basename', $files);
	}

	/**
	 * Delete log file
	 */
	private function deleteLogFile($fileName)
	{
		$logFolderPath = WRITEPATH . 'logs/';
		if ($fileName === "all") {
			array_map('unlink', glob($logFolderPath . "log-*.log"));
		} else {
			$file = $logFolderPath . basename($fileName);
			if (file_exists($file)) {
				unlink($file);
			}
		}
	}

	/**
	 * Download log file
	 */
	private function downloadLogFile($file)
	{
		if (file_exists($file)) {
			header('Content-Description: File Transfer');
			header('Content-Type: application/octet-stream');
			header('Content-Disposition: attachment; filename="' . basename($file) . '"');
			header('Expires: 0');
			header('Cache-Control: must-revalidate');
			header('Pragma: public');
			header('Content-Length: ' . filesize($file));
			readfile($file);
			exit;
		}
	}
}
