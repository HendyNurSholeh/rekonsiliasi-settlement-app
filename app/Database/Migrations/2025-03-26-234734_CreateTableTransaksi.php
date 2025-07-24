<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableTransaksi extends Migration
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
            'ID_UNDERLYING' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'unsigned' => true,
            ],
            'KD_CAB' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
                'comment'    => 'kode cabang user yang melakukan transaksi',
            ],
            'NOMINAL_TX' => [
                'type'       => 'DOUBLE',
                'constraint' => '18,0',
                'null'       => true,
            ],
            'TGL_TX' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'NO_REK' => [
                'type'       => 'VARCHAR',
                'constraint' => 10,
                'null'       => true,
            ],
            'NAMA_NASABAH' => [
                'type'       => 'VARCHAR',
                'constraint' => 200,
                'null'       => true,
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

        $this->forge->addKey('ID', true); // Primary key
        $this->forge->addUniqueKey('ID');
        $this->forge->addKey('ID_UNDERLYING');
        $this->forge->addForeignKey('ID_UNDERLYING', 't_underlying', 'ID', 'CASCADE', 'CASCADE');

        $this->forge->createTable('t_transaksi', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8']);
    }

    public function down()
    {
        $this->forge->dropTable('t_transaksi', true);
    }
}