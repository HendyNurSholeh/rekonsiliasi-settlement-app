<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class RoleSeeder extends Seeder
{
	public function run()
	{
		$data = [
			[
				'key' => 'SUPER ADMIN',
				'name' => 'Super Admin',
			],
			[
				'key' => 'ADMIN',
				'name' => 'Admin',
			],
			[
				'key' => 'PEMASAR',
				'name' => 'Pemasar',
			],
			[
				'key' => 'OPERASIONAL',
				'name' => 'Operasional',
			],
			[
				'key' => 'KANTOR PUSAT',
				'name' => 'Kantor Pusat',
			],
			[
				'key' => 'KEPALA CABANG',
				'name' => 'Kepala Cabang',
			],
			[
				'key' => 'PELAYANAN',
				'name' => 'Pelayanan',
			]
		];

		$this->db->table('roles')->insertBatch($data);
	}
}
