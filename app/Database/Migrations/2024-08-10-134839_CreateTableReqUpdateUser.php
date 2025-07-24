<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableReqUpdateUser extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id' => [
				'type' => 'INT',
				'auto_increment' => true,
			],
			'user_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
			],
			'original_field' => [
				'type' => 'JSON',
			],
			'updated_field' => [
				'type' => 'JSON',
			],
			'status' => [ // Pending, Approved, Rejected
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'category' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'applicant' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'applicant_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
			],
			'approval' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'approval_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => true,
			],
			'returner' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'returner_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'null' => true,
			],
			'cabang' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'filename' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'base64' => [
				'type' => 'LONGTEXT',
				'null' => true,
			],
			'note' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'permission' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'approved_at' => [
				'type' => 'TIMESTAMP',
				'null' => true,
			],
			'return_at' => [
				'type' => 'TIMESTAMP',
				'null' => true,
			],
			'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		]);

		$this->forge->addKey('id', true);
		$this->forge->createTable('req_update_users');
	}

	public function down()
	{
		$this->forge->dropTable('req_update_users');
	}
}
