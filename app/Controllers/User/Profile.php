<?php

namespace App\Controllers\User;

use App\Models\User;
use App\Traits\HasLogActivity;
use Carbon\Carbon;
use App\Models\Role;
use App\Libraries\LogEnum;
use App\Libraries\ActiveEnum;
use App\Traits\HasCurlRequest;
use App\Libraries\ApprovalEnum;
use App\Libraries\EventLogEnum;
use App\Controllers\BaseController;

class Profile extends BaseController
{
	use HasLogActivity;
	protected $helpers = ['form'];

	public function index()
	{
		$userData = $this->getUserDataFromSession();
		$data = $this->buildViewData($userData);

		if (!$this->request->is('post')) {
			return $this->render('user/profile.blade.php', $data);
		}

		if ($this->request->getPost('name') !== null) {
			return $this->_updateName($data);
		} else {
			return $this->_updatePassword($data);
		}
	}

	protected function getUserDataFromSession()
	{
		return [
			'name' => session()->name ?? '-',
			'username' => session()->username ?? '-',
			'role' => session()->role ?? '-',
		];
	}

	protected function buildViewData($userData)
	{
		return [
			'title' => 'Profile',
			'route' => 'profile',
			'data' => $userData,
		];
	}

	private function _updateName($data)
	{
		$validate = $this->validate([
			'name' => 'required|max_length[255]',
		]);

		if (!$validate) {
			session()->setFlashdata('warning', validation_list_errors());
			return $this->render('user/profile.blade.php', $data);
		}

		$id = session()->id;
		$user = User::find($id);
		$user->update([
			'name' => $this->request->getPost('name')
		]);

		session()->setFlashdata('success', 'Profile berhasil diubah');
		session()->name = $user['name'];
		return $this->render('user/profile.blade.php', $data);
	}

	private function _updatePassword($data)
	{
		$validate = $this->validate([
			'old_password' => 'required|max_length[255]',
			'new_password' => 'required|min_length[8]|regex_match[/^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z0-9])[\S]+$/]|max_length[255]',
			'conf_password' => 'required|matches[new_password]|max_length[255]',
		], [
			'new_password' => [
				'regex_match' => '{field} tidak valid harus memuat huruf kecil, huruf besar, angka dan simbol',
			],
		]);

		if (!$validate) {
			session()->setFlashdata('warning', validation_list_errors());
			return $this->render('user/profile.blade.php', $data);
		}

		$id = session()->id;
		$user = User::find($id);

		if (!password_verify($this->request->getPost('old_password'), $user->password)) {
			session()->setFlashdata('error', 'Password lama tidak valid');
			return $this->render('user/profile.blade.php', $data);
		}

		$user->update([
			'password' => password_hash($this->request->getPost('new_password'), PASSWORD_BCRYPT),
			'password_expired' => Carbon::now()->addYear(),
		]);

		// session()->setFlashdata('success', 'Password berhasil diubah');
		// return $this->render('user/profile.blade.php', $data);
		return $this->logout();
	}

	public function logout()
	{
		$this->logActivity([
			'log_name' => LogEnum::AUTH,
			'description' => 'User telah logout',
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		session()->remove(['id', 'nomor_absen', 'username', 'email', 'name', 'role', 'kode_unit_kerja', 'unit_kerja', 'logged_in', 'change_password', 'permissions']);
		return redirect()->route('login')
			->with('success', 'Anda berhasil logout!');
	}
}
