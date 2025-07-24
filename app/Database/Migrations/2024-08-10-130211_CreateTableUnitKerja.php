<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableUnitKerja extends Migration
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
			'kode' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => true,
			],
			'kode_dept' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'kode_t24' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'unique' => true,
			],
			'level' => [
				'type' => 'INT',
				'constraint' => 11,
			],
			'type' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'name' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'synonym' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'kota' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'address' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => true,
			],
			'telp' => [
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
		$this->forge->createTable('unit_kerjas');
	}

	public function down()
	{
		$this->forge->dropTable('unit_kerjas');
	}
}
