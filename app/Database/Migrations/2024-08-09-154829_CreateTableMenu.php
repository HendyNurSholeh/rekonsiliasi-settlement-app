<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableMenu extends Migration
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
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'icon' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'route' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'sort' => [
				'type' => 'INT'
			],
			'permission' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'parent_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true
			],
			'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
			'deleted_at' => [
				'type' => 'DATETIME',
				'null' => true,
			],
		]);

		$this->forge->addKey('id', true);
		$this->forge->createTable('menus');
	}

	public function down()
	{
		$this->forge->dropTable('menus');
	}
}
