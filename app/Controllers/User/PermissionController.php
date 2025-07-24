<?php

namespace App\Controllers\User;

use App\Models\Permission;
use App\Traits\HasLogActivity;
use App\Libraries\LogEnum;
use App\Traits\HasCurlRequest;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class PermissionController extends BaseController
{
	use HasLogActivity;

	protected $helpers = ['form'];

	public function index()
	{
		$data = [
			'title' => 'Daftar Permission',
			'route' => 'permission',
		];

		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		return $this->render('user/permission.blade.php', $data);
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

		$totalRecords = Permission::all()
			->count();
		$totalRecordswithFilter = Permission::where('name', 'like', '%' . $searchValue . '%')
			->count();

		$records = Permission::orderBy($columnName, $columnSortOrder)
			->where('name', 'like', '%' . $searchValue . '%')
			->skip($start)
			->take($length)
			->get()
			->toArray();

		$data = array_map(function ($item, $index) use ($start) {
			$item['no'] = $start + $index + 1;
			$item['aksi'] = "
				<div class='btn-group btn-group-sm' role='group'>
					<button type='button' class='btn btn-sm btn-primary' onclick='editModal($index)'><i class='fal fa-edit'></i></button>
					<button type='button' class='btn btn-sm btn-danger' onclick='deleteData($index)'><i class='fal fa-trash'></i></button>
				</div>
			";

			return $item;
		}, $records, array_keys($records));

		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $data
		);

		return $this->response->setJSON($response);
	}

	public function post()
	{
		$validate = $this->validate([
			'name' => 'required|max_length[255]|is_unique[permissions.name]',
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$data = $this->request->getPost();
		unset($data['id']);
		$permission = Permission::create($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Insert Data',
			'event' => EventLogEnum::CREATED,
			'subject_id' => $permission->id,
			'subject' => Permission::class,
			'properties' => json_encode($data)
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil ditambahkan!',
			'token' => csrf_hash()
		]);
	}

	public function edit()
	{
		$data = $this->request->getPost();
		$id = $data['id'];
		unset($data['id']);

		$validate = $this->validate([
			'name' => "required|max_length[255]|is_unique[permissions.name,id,{$id}]",

		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$permission = Permission::find($id);
		$old = $permission->toArray();
		$permission->update($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Data',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $permission->id,
			'subject' => Permission::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $permission->toArray()
			])
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil diubah!',
			'token' => csrf_hash()
		]);
	}

	public function delete()
	{
		$id = $this->request->getPost('id');

		$permission = Permission::find($id);
		$permission->delete();

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Delete Data',
			'event' => EventLogEnum::DELETED,
			'subject_id' => $permission->id,
			'subject' => Permission::class,
			'properties' => json_encode($permission->toArray())
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil dihapus!',
			'token' => csrf_hash()
		]);
	}

	public function options()
	{
		$data = Permission::all()->toArray();

		$result = array_map(function ($item) {
			return [
				"id" => $item["key"],
				"text" => $item["name"],
			];
		}, $data);

		return $this->response->setJSON([
			'status' => 200,
			'data' => $result,
			'token' => csrf_hash()
		]);
	}
}