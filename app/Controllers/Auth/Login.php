<?php

namespace App\Controllers\Auth;

use DateTime;
use HRTime\Unit;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Libraries\LogEnum;
use App\Models\LogActivity;
use App\Libraries\ActiveEnum;
use App\Traits\HasCurlRequest;
use App\Traits\HasLogActivity;
use App\Libraries\ApprovalEnum;
use App\Libraries\EventLogEnum;
use App\Libraries\StatusUserEnum;
use App\Controllers\BaseController;
use App\Libraries\ReqUpdateUserEnum;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class Login extends BaseController
{
	use HasLogActivity;

	protected $helpers = ['form', 'captcha'];

	public function index()
	{
		if (session()->logged_in) {
			session()->remove('captcha');
			return redirect()->route('dashboard');
		}

		$data = [
			'title' => 'Login Page',
		];

		if (!$this->request->is('post')) {
			$captcha = $this->createCaptcha();
			$data['captcha'] = $captcha;
			return $this->render('auth/login.blade.php', $data);
		}

		$rules = [
			'username' => 'required',
			'password' => 'required',
		];
		if (!$this->validate($rules)) {
			session()->setFlashdata('warning', validation_list_errors());
			$captcha = $this->createCaptcha();
			$data['captcha'] = $captcha;
			return $this->render('auth/login.blade.php', $data);
		}

		if ($this->request->getPost('captcha') != session()->captcha) {
			session()->setFlashdata('warning', 'Captcha Salah!');
			$captcha = $this->createCaptcha();
			$data['captcha'] = $captcha;
			return $this->render('auth/login.blade.php', $data);
		}

		return $this->_login($data);
	}

	private function _login(array $data)
	{
		$username = $this->request->getPost('username');
		$password = $this->request->getPost('password');

		$user = User::firstWhere('username', $username);

		if ($user) {
			if ($user->status == StatusUserEnum::DISABLE) {
				session()->setFlashdata('warning', 'User dinonaktifkan, silakan hubungi admin!');
				return redirect()->route('login');
			}

			if (password_verify($password, $user->password)) {
				$now = Carbon::now();
				$password_expired = Carbon::create($user->password_expired);
				$expired_at = Carbon::create($user->expired_at);
				$change_password = ($now > $password_expired) ? true : false;

				$messages = 'Berhasil login!';

				if ($user->expired_at) {
					if ($now > $expired_at) {
						if (!$user->temp_id) {
							session()->setFlashdata('error', 'User tidak dapat diakses, silakan hubungi admin!');
							$captcha = $this->createCaptcha();
							$data['captcha'] = $captcha;
							return $this->render('auth/login.blade.php', $data);
						}

						$req = $user->req_update;
						$old = $user->toArray();
						unset($old['req_update']);

						$data = json_decode($req->original_field, true);
						unset($data['id']);
						$data['temp_id'] = null;
						$data['updated_by'] = session()->nomor_absen;

						$user->update($data);
						$new = $user->toArray();
						unset($new['req_update']);
						$this->logActivity([
							'log_name' => LogEnum::DATA,
							'description' => 'Update Data',
							'event' => EventLogEnum::UPDATED,
							'subject_id' => $user->id,
							'subject' => User::class,
							'properties' => json_encode([
								'old' => $old,
								'new' => $new
							])
						]);

						$req->update([
							'status' => ReqUpdateUserEnum::RETURN ,
							'returner' => $user->username,
							'returner_id' => $user->id,
							'return_at' => date('Y-m-d H:i:s'),
						]);

						$messages = 'Berhasil login, Data User telah dikembalikan ke data asal!';
					}
				}

				$session = [
					'id' => $user->id,
					'nomor_absen' => $user->nomor_absen,
					'username' => $user->username,
					'email' => $user->email,
					'name' => $user->name,
					'role' => $user->role,
					'core' => $user->core,
					'kode_unit_kerja' => $user->kode_unit_kerja,
					'unit_kerja' => $user->unit_kerja->name,
					'logged_in' => true,
					'change_password' => $change_password,
					'permissions' => $user->roles->permissions->pluck('name')->toArray(),
				];
				session()->set($session);

				$user->update([
					'login_attempt' => 0,
				]);

				$this->logActivity([
					'log_name' => LogEnum::AUTH,
					'description' => 'User telah login',
					'event' => EventLogEnum::VERIFIED,
					'subject' => 'User/login',
					'properties' => json_encode($session)
				]);
				return redirect()->route('dashboard')
					->with('success', $messages);
			}

			if ($user->login_attempt >= 3) {
				$user->update([
					'status' => StatusUserEnum::DISABLE,
					'login_attempt' => 0,
				]);

				session()->setFlashdata('error', 'User dinonaktifkan, silakan hubungi admin!');
				$captcha = $this->createCaptcha();
				$data['captcha'] = $captcha;
				return $this->render('auth/login.blade.php', $data);
			}
		}

		session()->setFlashdata('error', 'Username atau Password Salah!');
		$captcha = $this->createCaptcha();
		$data['captcha'] = $captcha;
		return $this->render('auth/login.blade.php', $data);
	}

	public function logout()
	{
		$this->logActivity([
			'log_name' => LogEnum::AUTH,
			'description' => 'User telah logout',
			'event' => EventLogEnum::VERIFIED,
			'subject' => '-',
		]);

		session()->remove(['id', 'nomor_absen', 'username', 'email', 'name', 'role', 'core', 'kode_unit_kerja', 'unit_kerja', 'logged_in', 'permissions', 'change_password']);
		return redirect()->route('login')
			->with('success', 'Anda berhasil logout!');
	}

	public function refreshCaptcha()
	{
		return $this->response->setJSON([
			'status' => 200,
			'data' => $this->createCaptcha()
		]);
	}

	public function createCaptcha()
	{
		$config = array(
			'img_url' => base_url() . 'captcha/',
			'img_path' => 'captcha/',
			'img_width' => 175,
			'img_height' => 50,
			'word_length' => 4,
			'font_size' => 25,
			'font_path' => 'webfonts/OpenSans-Regular.ttf',
		);

		$captcha = create_captcha($config);
		
		session()->remove('captcha');
		session()->set('captcha', $captcha['word']);

		return $captcha;
	}
}
