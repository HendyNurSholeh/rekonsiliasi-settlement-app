<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableLogActivity extends Migration
{
	public function up()
	{
		$this->forge->addField([
			'id' => [
				'type' => 'INT',
				'constraint' => 11,
				'unsigned' => true,
				'auto_increment' => true
			],
			'log_name' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'description' => [
				'type' => 'TEXT',
				'null' => true,
			],
			'event' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'subject_id' => [
				'type' => 'INT',
				'constraint' => 11,
				'null' => true,
			],
			'subject' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'causer_id' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'causer_name' => [
				'type' => 'VARCHAR',
				'constraint' => 255,
			],
			'ip_address' => [
				'type' => 'VARCHAR',
				'constraint' => 45,
			],
			'properties' => [
				'type' => 'JSON',
				'null' => true,
			],
			'created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP',
			'updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP',
		]);

		$this->forge->addKey('id', true);
		$this->forge->createTable('log_activities');
	}

	public function down()
	{
		$this->forge->dropTable('log_activities');
	}
}
