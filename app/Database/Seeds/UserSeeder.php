<?php
namespace App\Database\Seeds;
use Carbon\Carbon;
use App\Libraries\StatusUserEnum;

class UserSeeder extends \CodeIgniter\Database\Seeder
{
	public function run()
	{
		$data = [
			'nomor_absen' => '01828',
			'username' => 'admintsi',
			'email' => 'ryanbagusha@gmail.com',
			'name' => 'Ryan',
			'kode_unit_kerja' => '1300',
			'role' => 'SUPER ADMIN',
			'password' => password_hash('Bankkalsel1*', PASSWORD_BCRYPT),
			'status' => StatusUserEnum::ACTIVE,
			'password_expired' => Carbon::now()->addYear(),
			'created_by' => 'SYSTEM',
		];

		$this->db->table('users')->insert($data);
	}
}
