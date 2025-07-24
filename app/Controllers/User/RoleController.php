<?php

namespace App\Controllers\User;

use Carbon\Carbon;
use App\Models\Role;
use App\Libraries\LogEnum;
use App\Models\Permission;
use App\Libraries\ActiveEnum;
use App\Traits\HasCurlRequest;
use App\Traits\HasLogActivity;
use App\Libraries\ApprovalEnum;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class RoleController extends BaseController
{
	use HasLogActivity;

	protected $helpers = ['form'];

	public function index()
	{
		$data = [
			'title' => 'Daftar Role',
			'route' => 'role',
			'permissions' => Permission::all()->pluck('name', 'id')
		];

		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		return $this->render('user/role.blade.php', $data);
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

		$totalRecords = Role::all()
			->count();
		$totalRecordswithFilter = Role::where('key', 'like', '%' . $searchValue . '%')
			->orWhere('name', 'like', '%' . $searchValue . '%')
			->count();

		$records = Role::orderBy($columnName, $columnSortOrder)
			->orWhere('name', 'like', '%' . $searchValue . '%')
			->skip($start)
			->take($length)
			->get()
			->toArray();

		$data = array_map(function ($item, $index) use ($start) {
			$item['no'] = $start + $index + 1;
			$item['aksi'] = "
				<div class='btn-group btn-group-sm' role='group'>
					<button type='button' class='btn btn-sm btn-secondary' onclick='assignModal($index)'><i class='fal fa-list'></i></button>
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
			'key' => 'required|is_unique[roles.key]',
			'name' => 'required|max_length[255]',
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
		$role = Role::create($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Insert Data',
			'event' => EventLogEnum::CREATED,
			'subject_id' => $role->id,
			'subject' => Role::class,
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
			'key' => "required",
			'name' => 'required',
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$role = Role::find($id);
		$old = $role->toArray();
		$oldKey = $role->key;
		$role->update($data);

		// Update users' role column if key changed
		if (isset($data['key']) && $oldKey !== $data['key']) {
			// Assuming User model exists and 'role' column stores the role key
			$userModel = new \App\Models\User();
			\App\Models\User::where('role', $oldKey)->update(['role' => $data['key']]);
		}

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Data',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $role->id,
			'subject' => Role::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $role->toArray()
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

		$role = Role::find($id);
		$role->delete();

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Delete Data',
			'event' => EventLogEnum::DELETED,
			'subject_id' => $role->id,
			'subject' => Role::class,
			'properties' => json_encode($role->toArray())
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil dihapus!',
			'token' => csrf_hash()
		]);
	}

	public function options()
	{
		$data = Role::all()->toArray();

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

	public function getPermissions($id)
	{
		return $this->response->setJSON([
			'status' => 200,
			'data' => Role::find($id)->permissions()->pluck('id')->toArray()
		]);
	}

	public function assignPermission()
	{
		$id = $this->request->getPost('id');
		$permissions = $this->request->getPost('permissions');

		Role::find($id)->permissions()->sync($permissions);

		return $this->response->setJSON([
			'status' => 200,
			'body' => "Permission berhasil diassign!",
			'token' => csrf_hash()
		]);
	}
}