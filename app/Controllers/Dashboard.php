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
		$db = \Config\Database::connect();
        $query = $db->query("SELECT TGL_REKON FROM t_proses WHERE STATUS = 1 ORDER BY TGL_REKON DESC LIMIT 1");
        $tgl_rekon = $query->getRow();
		$data = [
			'title' => 'Beranda',
			'route' => 'dashboard',
			'tgl_rekon' => $tgl_rekon,
			'today' => Carbon::now()->isoFormat('dddd, D MMMM Y'),
			'date' => Carbon::now()->format('Y-m-d'),
		];

		return $this->render('dashboard', $data);
	}
}