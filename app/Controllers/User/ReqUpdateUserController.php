<?php

namespace App\Controllers\User;

use App\Libraries\ReqUpdateUserEnum;
use App\Models\Role;
use App\Models\User;
use App\Libraries\LogEnum;
use App\Models\ReqUpdateUser;
use App\Traits\HasCurlRequest;
use App\Traits\HasLogActivity;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class ReqUpdateUserController extends BaseController
{
	use HasLogActivity;

	public function index()
	{
		if (!in_array('view request', session('permissions'))) {
			$this->response->redirect(base_url('dashboard'));
		}

		$data = [
			'title' => 'Daftar Permohonan Update User',
			'route' => 'request',
			'roles' => Role::whereNot('key', 'SUPER ADMIN')->pluck('name', 'key'),
			'permissions' => session()->permissions
		];

		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		return $this->render('user/request.blade.php', $data);
	}

	public function dataTables()
	{
		$status = $this->request->getGet('status');

		$draw = $this->request->getGet('draw');
		$start = $this->request->getGet('start');
		$length = $this->request->getGet('length');

		$columnIndex = $this->request->getGet('order')[0]['column'];
		$columnName = $this->request->getGet('columns')[$columnIndex]['data'];
		$columnSortOrder = $this->request->getGet('order')[0]['dir'];
		$searchValue = $this->request->getGet('search')['value'];

		$totalRecords = ReqUpdateUser::when(in_array('create request', session()->permissions), function ($query) {
			return $query->where('user_id', session('id'));
		})
			->count();
		$totalRecordswithFilter = ReqUpdateUser::when($status, function ($query) use ($status) {
			return $query->whereIn('status', $status);
		})
			->when(in_array('create request', session()->permissions), function ($query) {
				return $query->where('user_id', session('id'));
			})
			->count();

		$records = ReqUpdateUser::select([
			'id',
			'user_id',
			'original_field',
			'updated_field',
			'status',
			'category',
			'applicant',
			'applicant_id',
			'approval',
			'approval_id',
			'returner',
			'returner_id',
			'cabang',
			'filename',
			'note',
			'approved_at',
			'return_at',
			'created_at',
		])
			->orderBy($columnName, $columnSortOrder)
			->when($status, function ($query) use ($status) {
				return $query->whereIn('status', $status);
			})
			->when(in_array('create request', session()->permissions), function ($query) {
				return $query->where('user_id', session('id'));
			})
			->skip($start)
			->take($length)
			->get()
			->toArray();

		$result = array_map(function ($item, $index) {
			$permissions = session()->permissions;
			$is_disabled = false;
			$cancel_maker = ($item['status'] == ReqUpdateUserEnum::PENDING && $item['user_id'] == session()->id) ? true : false;
			$return = ($item['status'] == ReqUpdateUserEnum::APPROVED && $item['category'] != 'Mutasi') ? true : false;

			if ($item['status'] == ReqUpdateUserEnum::PENDING && in_array('approve request', $permissions)) {
				$is_disabled = true;
			}

			$item['aksi'] = "<button type='button' class='btn btn-secondary btn-icon rounded waves-effect waves-themed mx-1' data-disabled='$is_disabled' data-return='$return' data-cancel-maker='$cancel_maker' onclick='detail_modal(this, \"$index\")'><i class='fal fa-list-ul'></i></button>";
			return $item;
		}, $records, array_keys($records));

		$response = array(
			"draw" => intval($draw),
			"iTotalRecords" => $totalRecords,
			"iTotalDisplayRecords" => $totalRecordswithFilter,
			"aaData" => $result
		);

		return $this->response->setJSON($response);
	}

	public function post()
	{
		$data = $this->request->getPost();
		$file = $this->request->getFile('file');

		$cek = ReqUpdateUser::where('user_id', $data['id'])->where('status', ReqUpdateUserEnum::PENDING)->first();
		// print_r($cek);die;
		if ($cek) {
			return $this->response->setJSON([
				'status' => 400,
				'message' => 'Anda sudah membuat permohonan update user dengan status pending',
				'token' => csrf_hash()
			]);
		}

		if ((session('role') == $data['role']) && (session('kode_unit_kerja') == $data['branch'])) {
			return $this->response->setJSON([
				'status' => 400,
				'message' => 'Anda membuat permohonan update user dengan role dan unit kerja yang sekarang',
				'token' => csrf_hash()
			]);
		}

		if ($file->isValid() && !$file->hasMoved()) {
			$fileContent = file_get_contents($file->getTempName());
			$base64 = base64_encode($fileContent);
			$filename = $file->getClientName();
			$originalData = [
				'id' => $data['id'],
				'role' => session('role'),
				'kode_unit_kerja' => session('kode_unit_kerja'),
				'core' => session('core'),
				'expired_at' => null
			];

			$branch = $data['branch'];
			$updatedData = [
				'id' => $data['id'],
				'role' => $data['role'],
				'kode_unit_kerja' => $branch,
				'core' => ($branch[0] == 1) ? 'K' : 'S',
				'expired_at' => $data['masa_aktif'] ?? null,
			];

			$insertData = [
				'user_id' => $data['id'],
				'original_field' => json_encode($originalData),
				'updated_field' => json_encode($updatedData),
				'status' => ReqUpdateUserEnum::PENDING, // pending, approved, rejected
				'category' => $data['category'],
				'applicant' => session('username'),
				'applicant_id' => session('id'),
				'cabang' => session('kode_unit_kerja'),
				'filename' => $filename,
				'base64' => $base64,
				'note' => $data['keterangan'],
				'permission' => 'tsi',
			];
			$req = ReqUpdateUser::create($insertData);

			unset($insertData['base64']);
			$this->logActivity([
				'log_name' => LogEnum::DATA,
				'description' => 'Insert Data',
				'event' => EventLogEnum::CREATED,
				'subject_id' => $req->id,
				'subject' => ReqUpdateUser::class,
				'properties' => json_encode($insertData)
			]);

			return $this->response->setJSON([
				'status' => 200,
				'message' => "Permohonan berhasil diajukan!",
				'token' => csrf_hash()
			]);
		} else {
			return $this->response->setJSON([
				'status' => 400,
				'message' => "File gagal diupload!",
				'token' => csrf_hash()
			]);
		}
	}

	public function getPdf()
	{
		$id = $this->request->getPost('id');
		$data = ReqUpdateUser::find($id);

		if ($data) {
			return $this->response->setJSON([
				'status' => 200,
				'data' => $data->base64,
				'token' => csrf_hash()
			]);
		} else {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => 'Data tidak ditemukan!',
				'token' => csrf_hash()
			]);
		}
	}

	public function approval()
	{
		$id = $this->request->getPost('id');
		$id_user = $this->request->getPost('id_user');
		$action = $this->request->getPost('action');

		if ($action == 'approve') {
			return $this->approveRequest($id, $id_user);
		} elseif ($action == 'reject') {
			return $this->rejectRequest($id);
		} elseif ($action == 'return') {
			return $this->returnRequest($id, $id_user);
		} else {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => 'Action tidak ditemukan',
				'token' => csrf_hash()
			]);
		}
	}

	public function approveRequest($id, $id_user)
	{
		$req = ReqUpdateUser::find($id);
		$user = User::find($id_user);
		$old = $user->toArray();

		$data = json_decode($req->updated_field, true);
		unset($data['id']);
		$data['updated_by'] = session()->nomor_absen;
		if ($req->category != 'Mutasi') {
			$data['temp_id'] = $id;
		} else {
			$data['expired_at'] = NULL;
		}

		$user->update($data);
		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Data',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $user->id,
			'subject' => User::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $user->toArray()
			])
		]);

		$req->update([
			'status' => ReqUpdateUserEnum::APPROVED,
			'approval' => session('username'),
			'approval_id' => session('id'),
			'approved_at' => date('Y-m-d H:i:s'),
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Permohonan berhasil disetujui',
			'token' => csrf_hash()
		]);
	}

	public function rejectRequest($id)
	{
		$req = ReqUpdateUser::find($id);

		$req->update([
			'status' => ReqUpdateUserEnum::REJECTED,
			'approval' => session('username'),
			'approval_id' => session('id'),
			'approved_at' => date('Y-m-d H:i:s'),
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Permohonan ditolak!',
			'token' => csrf_hash()
		]);
	}

	public function returnRequest($id, $id_user)
	{
		$req = ReqUpdateUser::find($id);
		$user = User::find($id_user);
		$old = $user->toArray();

		$data = json_decode($req->original_field, true);
		unset($data['id']);
		$data['temp_id'] = null;
		$data['updated_by'] = session()->nomor_absen;

		$user->update($data);
		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Data',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $user->id,
			'subject' => User::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $user->toArray()
			])
		]);

		$req->update([
			'status' => ReqUpdateUserEnum::RETURN ,
			'returner' => session('username'),
			'returner_id' => session('id'),
			'return_at' => date('Y-m-d H:i:s'),
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data User telah dikembalikan ke data asal',
			'token' => csrf_hash()
		]);
	}
}