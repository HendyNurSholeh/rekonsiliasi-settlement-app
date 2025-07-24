<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableUser extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true,
			],
			'nomor_absen' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => true,
			],
			'username' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => true,
			],
			'email' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => true,
				'null' => true,
			],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'kode_unit_kerja' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'role' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'password' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'expired_at' => [
				'type' => 'DATETIME',
				'null' => true,
				'deafult' => null,
			],
			'status' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'login_attempt' => [
				'type' => 'INT',
				'default' => 0,
			],
			'last_login' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'password_expired' => [
				'type' => 'DATETIME',
			],
			'reset_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
			'temp_id' => [
				'type' => 'INT',
				'null' => true
			],
			'created_by' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'updated_by' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'deleted_by' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
			'deleted_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);
		$this->forge->addKey('id', true);
		$this->forge->createTable('users');
	}

	public function down()
	{
		$this->forge->dropTable('users');
	}
}
