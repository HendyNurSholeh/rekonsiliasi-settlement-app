<?php

namespace App\Controllers\Log;

use App\Libraries\LogEnum;
use App\Models\LogActivity;
use App\Traits\HasLogActivity;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class LogActivityController extends BaseController
{
	use HasLogActivity;

	// public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
	// {
	// 	// Do Not Edit This Line
	// 	parent::initController($request, $response, $logger);

	// 	if (!in_array('view activity', session('permissions'))) {

	// 		$this->response->redirect(base_url('dashboard'));
	// 	}
	// }

	public function index(): string
	{
		$data = [
			'title' => 'Daftar Aktivitas',
			'route' => 'log/activity'
		];

		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		return $this->render('log/log_activity.blade.php', $data);
	}

	public function dataTables()
	{
		$draw = $this->request->getGet('draw');
		$start = $this->request->getGet('start');
		$length = $this->request->getGet('length');

		$columnIndex = $this->request->getGet('order')[0]['column'];
		$columnName = $this->request->getGet('columns')[$columnIndex]['data'];
		$columnSortOrder = $this->request->getGet('order')[0]['dir'];
		$searchValue = $this->request->getGet('search')['value'];

		$totalRecords = LogActivity::all()
			->count();
		$totalRecordswithFilter = LogActivity::where('description', 'like', '%' . $searchValue . '%')
			->count();

		$records = LogActivity::orderBy($columnName, $columnSortOrder)
			->where('description', 'like', '%' . $searchValue . '%')
			->skip($start)
			->take($length)
			->get()
			->toArray();

		$data = array_map(function ($item) {
			unset ($item['updated_at']);
			unset ($item['properties']);

			$item['created_at'] = date("d-m-Y H:i:s", strtotime($item['created_at']));

			switch ($item['log_name']) {
				case LogEnum::AUTH:
					$item['log_name'] = sprintf('<span class="badge badge-pill badge-info">%s</span>', $item['log_name']);
					break;
				case LogEnum::DATA:
					$item['log_name'] = sprintf('<span class="badge badge-pill badge-success">%s</span>', $item['log_name']);
					break;
				default:
					$item['log_name'] = sprintf('<span class="badge badge-pill badge-secondary">%s</span>', $item['log_name']);
			}

			switch ($item['event']) {
				case EventLogEnum::VERIFIED:
					$item['event'] = sprintf('<span class="badge badge-pill badge-success">%s</span>', $item['event']);
					break;
				case EventLogEnum::FAILED:
					$item['event'] = sprintf('<span class="badge badge-pill badge-danger">%s</span>', $item['event']);
					break;
				default:
					$item['event'] = sprintf('<span class="badge badge-pill badge-info">%s</span>', $item['event']);
			}

			$item['aksi'] = "
				<button type='button' class='btn btn-primary btn-xs btn-icon rounded-circle' data-toggle='modal' data-target='#detail-modal' onclick='open_modal(" . $item['id'] . ")'><i class='fal fa-info-circle'></i></button>
			";

			return $item;
		}, $records);

		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $data
		);

		return $this->response->setJSON($response);
	}

	public function showLog($id)
	{
		$properties = LogActivity::find($id)->properties;
		$array = json_decode($properties, true);
		$string = $this->displayArray($array);
		$json = json_encode($string);

		return $this->response->setJSON($json);
	}

	public function displayArray($array, $indent = 0)
	{
		$result = '';
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result .= str_repeat("- ", $indent) . "$key </br>";
				$result .= $this->displayArray($value, $indent + 1);
			} else {
				$result .= str_repeat("- ", $indent) . "$key => $value </br>";
			}
		}
		return $result;
	}
}
