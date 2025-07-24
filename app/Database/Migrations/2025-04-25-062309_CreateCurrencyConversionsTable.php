<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCurrencyConversionsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'KODE_MATA_UANG' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'unique'     => true,
            ],
            'NAMA_MATA_UANG' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'NILAI_TUKAR_KE_IDR' => [
                'type'       => 'DECIMAL',
                'constraint' => '15,6',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        $this->forge->addKey('ID', true);
        $this->forge->createTable('t_currency_conversions');
    }

    public function down()
    {
        $this->forge->dropTable('t_currency_conversions');
    }
}