<?php

namespace App\Controllers\User;

use App\Libraries\StatusUserEnum;
use App\Models\User;
use Carbon\Carbon;
use App\Models\Role;
use App\Libraries\LogEnum;
use App\Libraries\ActiveEnum;
use App\Traits\HasCurlRequest;
use App\Traits\HasLogActivity;
use App\Libraries\ApprovalEnum;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class UserController extends BaseController
{
	use HasLogActivity;
	public $unit_kerja;

	protected $helpers = ['form'];

	public function index()
	{
		if (!in_array('view user', session('permissions'))) {
			$this->response->redirect(base_url('dashboard'));
		}

		$data = [
			'title' => 'Daftar User',
			'route' => 'user'
		];

		$this->logActivity([
			'log_name' => LogEnum::VIEW,
			'description' => session('username') . ' mengakses Halaman ' . $data['title'],
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		return $this->render('user/user.blade.php', $data);
	}

	public function dataTables()
	{
		$kode_unit_kerja = $this->request->getGet('kode_unit_kerja');

		$draw = $this->request->getGet('draw');
		$start = $this->request->getGet('start');
		$length = $this->request->getGet('length');

		$columnIndex = $this->request->getGet('order')[0]['column'];
		$columnName = $this->request->getGet('columns')[$columnIndex]['data'];
		$columnSortOrder = $this->request->getGet('order')[0]['dir'];
		$searchValue = $this->request->getGet('search')['value'];

		$totalRecords = User::when(session('role') == 'RKP', function ($query) {
			$query->where('role', '!=', 'RKP')
				->where('role', '!=', 'SUPER ADMIN');
		})
			->when($kode_unit_kerja, function ($query) use ($kode_unit_kerja) {
				$query->where('kode_unit_kerja', $kode_unit_kerja);
			})
			->count();
		$totalRecordswithFilter = User::when(session('role') == 'RKP', function ($query) {
			$query->where('role', '!=', 'RKP')
				->where('role', '!=', 'SUPER ADMIN');
		})
			->when($kode_unit_kerja, function ($query) use ($kode_unit_kerja) {
				$query->where('kode_unit_kerja', $kode_unit_kerja);
			})
			->where(function ($query) use ($searchValue) {
				$query->where('nomor_absen', 'like', '%' . $searchValue . '%')
					->orWhere('username', 'like', '%' . $searchValue . '%')
					->orWhere('name', 'like', '%' . $searchValue . '%');
			})
			->count();

		$records = User::with('unit_kerja', 'req_update')
			->when(session('role') == 'RKP', function ($query) {
				$query->where('role', '!=', 'RKP')
					->where('role', '!=', 'SUPER ADMIN');
			})
			->when($kode_unit_kerja, function ($query) use ($kode_unit_kerja) {
				$query->where('kode_unit_kerja', $kode_unit_kerja);
			})
			->orderBy($columnName, $columnSortOrder)
			->where(function ($query) use ($searchValue) {
				$query->where('nomor_absen', 'like', '%' . $searchValue . '%')
					->orWhere('username', 'like', '%' . $searchValue . '%')
					->orWhere('name', 'like', '%' . $searchValue . '%');
			})
			->skip($start)
			->take($length)
			->get()
			->toArray();

		$data = array_map(function ($item, $index) {
			$now = Carbon::now();
			$item['no'] = $index + 1;
			$item['unit_kerja'] = $item['unit_kerja']['name'];
			$item['req_update'] = ($item['req_update']) ? $item['req_update']['original_field'] : null;

			if ($item['last_login']) {
				$last_login = Carbon::create($item['last_login']);
				$item['diffForHumans'] = $last_login->diffForHumans();
				$item['diffInMinutes'] = $now->diffInMinutes($last_login);
			} else {
				$item['diffForHumans'] = null;
				$item['diffInMinutes'] = null;
			}

			$status = null;
			if ($item['status'] == StatusUserEnum::ACTIVE) {
				$status = "<a class='dropdown-item' href='javascript:void(0)' onclick='disableUser($index)'><i class='fal fa-times-circle mr-2'></i>Nonaktikan</a>";
			} else if ($item['status'] == StatusUserEnum::DISABLE) {
				$status = "<a class='dropdown-item' href='javascript:void(0)' onclick='activeUser($index)'><i class='fal fa-check-circle mr-2'></i>Aktifkan</a>";
			}

			$item['aksi'] = "
				<div class='btn-group dropleft'>
					<button type='button' class='btn btn-sm btn-secondary rounded-circle btn-icon waves-effect waves-themed' data-toggle='dropdown' aria-haspopup='true' aria-expanded='false'>
						<i class='fal fa-list'></i>
					</button>
					<div class='dropdown-menu' style=''>
						<a class='dropdown-item' href='javascript:void(0)' onclick='detailModal($index)'> <i class='fal fa-eye mr-2'></i> Informasi User</a>
						<a class='dropdown-item' href='javascript:void(0)' onclick='editModal($index)'><i class='fal fa-edit mr-2'></i>Ubah</a>
						<a class='dropdown-item' href='javascript:void(0)' onclick='deleteData($index)'><i class='fal fa-trash mr-2'></i>Hapus</a>
						$status
						<a class='dropdown-item' href='javascript:void(0)' onclick='resetPassword($index)'><i class='fal fa-sync mr-2'></i>Reset Password</a>
					</div>
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
			'nomor_absen' => 'required|is_unique[users.nomor_absen]',
			'username' => 'required|is_unique[users.username]',
			'email' => 'permit_empty|is_unique[users.email]',
			'name' => 'required|max_length[255]',
			'kode_unit_kerja' => 'required',
			'role' => 'required',
			'expired_at' => 'permit_empty',
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$data = $this->request->getPost();
		if (!$data['expired_at']) {
			unset($data['expired_at']);
		}
		unset($data['id']);

		$branch = $data['kode_unit_kerja'];
		$data['core'] = ($branch[0] == 1) ? 'K' : 'S';
		$data['password'] = password_hash('Bankkalsel1*', PASSWORD_BCRYPT);
		$data['password_expired'] = Carbon::now();
		$data['status'] = StatusUserEnum::ACTIVE;
		$data['created_by'] = session('nomor_absen');

		$user = User::create($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Insert Data',
			'event' => EventLogEnum::CREATED,
			'subject_id' => $user->id,
			'subject' => User::class,
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
			'nomor_absen' => "required|is_unique[users.nomor_absen,id,{$id}]",
			'username' => "required|is_unique[users.username,id,{$id}]",
			'email' => "permit_empty|is_unique[users.email,id,{$id}]",
			'name' => 'required|max_length[255]',
			'kode_unit_kerja' => 'required',
			'role' => 'required',
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$user = User::find($id);
		$old = $user->toArray();

		$branch = $data['kode_unit_kerja'];
		$data['core'] = ($branch[0] == 1) ? 'K' : 'S';
		$data['updated_by'] = session('nomor_absen');
		if (!$data['expired_at']) {
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

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil diubah!',
			'token' => csrf_hash()
		]);
	}

	public function delete()
	{
		$id = $this->request->getPost('id');

		$user = User::find($id);
		$user->update([
			'deleted_by' => session('nomor_absen')
		]);
		$user->delete();

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Delete Data',
			'event' => EventLogEnum::DELETED,
			'subject_id' => $user->id,
			'subject' => User::class,
			'properties' => json_encode($user->toArray())
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Data berhasil dihapus!',
			'token' => csrf_hash()
		]);
	}

	public function updateStatus()
	{
		$validate = $this->validate([
			'id' => 'required',
			'status' => 'required',
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$data = $this->request->getPost();
		$id = $data['id'];
		unset($data['id']);

		$user = User::find($id);
		$old = $user->toArray();

		$data['updated_by'] = session('nomor_absen');
		$user->update($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Update Status User',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $user->id,
			'subject' => User::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $user->toArray()
			])
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => ($data['status'] == StatusUserEnum::ACTIVE) ? 'Data berhasil diaktifkan!' : 'Data berhasil dinonaktifkan!',
			'token' => csrf_hash()
		]);
	}

	public function resetPassword()
	{
		$validate = $this->validate([
			'id' => 'required',
		]);
		if (!$validate) {
			return $this->response->setJSON([
				'status' => 400,
				'messages' => validation_list_errors(),
				'token' => csrf_hash()
			]);
		}

		$data = $this->request->getPost();
		$id = $data['id'];
		unset($data['id']);

		$user = User::find($id);
		$old = $user->toArray();

		$data['password'] = password_hash('Bankkalsel1*', PASSWORD_BCRYPT);
		$data['password_expired'] = Carbon::now();
		$data['reset_at'] = Carbon::now();
		$user->update($data);

		$this->logActivity([
			'log_name' => LogEnum::DATA,
			'description' => 'Reset Password',
			'event' => EventLogEnum::UPDATED,
			'subject_id' => $user->id,
			'subject' => User::class,
			'properties' => json_encode([
				'old' => $old,
				'new' => $user->toArray()
			])
		]);

		return $this->response->setJSON([
			'status' => 200,
			'messages' => 'Password user berhasil direset!',
			'token' => csrf_hash()
		]);
	}
}