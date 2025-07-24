<?php

namespace App\Filters;

use App\Libraries\ActiveEnum;
use App\Libraries\ApprovalEnum;
use App\Models\User;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class IsFirstLogin implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		User::find(session()->id)->update(['last_login' => date('Y-m-d H:i:s')]);
		if (session()->change_password) {
			return redirect()->route('user.profile')
				->with('warning', 'Silakan ubah password terlebih dahulu!');
		}
	}
	//--------------------------------------------------------------------

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
	}
}
