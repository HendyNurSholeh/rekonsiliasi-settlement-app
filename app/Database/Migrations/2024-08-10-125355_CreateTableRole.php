<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableRole extends Migration
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
			'key' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => true,
			],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 255
			],
			'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
			'deleted_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);

		$this->forge->addKey('id', true);
		$this->forge->createTable('roles');
	}

	public function down()
	{
		$this->forge->dropTable('roles');
	}
}
