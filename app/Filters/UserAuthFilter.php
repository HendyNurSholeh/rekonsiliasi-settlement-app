<?php

namespace App\Filters;

use App\Libraries\ActiveEnum;
use App\Libraries\ApprovalEnum;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use CodeIgniter\Filters\FilterInterface;

class UserAuthFilter implements FilterInterface
{
	public function before(RequestInterface $request, $arguments = null)
	{
		if (is_null(session()->logged_in)) {
			return redirect()->route('login')
				->with('warning', 'Anda belum login');
		}
	}
	//--------------------------------------------------------------------

	public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
	{
		// Do something here
	}
}
