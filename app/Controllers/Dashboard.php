<?php

namespace App\Controllers;

use App\Models\UnitKerja;
use App\Traits\HasCurlRequest;
use Carbon\Carbon;
use App\Models\Role;
use App\Models\User;
use App\Libraries\LogEnum;
use App\Traits\HasLogActivity;
use App\Libraries\EventLogEnum;

class Dashboard extends BaseController
{
	use HasCurlRequest;

	public function index()
	{
		Carbon::setLocale('id');
		$data = [
			'title' => 'Beranda',
			'route' => 'dashboard',
			'today' => Carbon::now()->isoFormat('dddd, D MMMM Y'),
			'date' => Carbon::now()->format('Y-m-d'),
		];

		return $this->render('dashboard', $data);
	}

	// public function callSeeder()
	// {
	// 	$role = Role::all();
	// 	foreach ($role as $item) {
	// 		$item->permissions()->sync([10,11]);
	// 	}

	// 	$role = Role::where('key', 'SUPER ADMIN')->first();
	// 	$role->permissions()->sync([1, 2, 3, 4, 8, 9, 10, 11]);
	// }
}