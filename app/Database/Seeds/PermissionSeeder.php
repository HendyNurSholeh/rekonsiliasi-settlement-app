<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class PermissionSeeder extends Seeder
{
	public function run()
	{
		$data = [
			['name' => 'view user'],
			['name' => 'view unit kerja'],
			['name' => 'view permission'],
			['name' => 'view role'],

			['name' => 'view request'],
			['name' => 'create request'],
			['name' => 'approve request'],

			['name' => 'view activity'],
			['name' => 'view error']
		];

		$this->db->table('permissions')->insertBatch($data);
	}
}
