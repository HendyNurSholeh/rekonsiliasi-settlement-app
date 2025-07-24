<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTableUnderlying extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ID' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'KD_INVOICE' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'null' => true,
                'comment' => 'diisi kode invoice dari sistem 5 digit, numerik dan urut cth 00001, 00002 dst',
            ],
            'NO_INVOICE' => [
                'type' => 'VARCHAR',
                'constraint' => 150,
                'null' => true,
                'comment' => 'inputan user',
            ],
            'PENERBIT' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'comment' => 'inputan user',
            ],
            'DESKRIPSI' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'comment' => 'inputan user',
            ],
            'NOMINAL' => [
                'type' => 'DOUBLE',
                'constraint' => '18,2',
                'null' => true,
            ],
            'PATH_FILENAME' => [
                'type' => 'VARCHAR',
                'constraint' => 200,
                'null' => true,
                'comment' => 'nama file',
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

        $this->forge->addKey('ID', true); // Primary Key
        $this->forge->addUniqueKey('KD_INVOICE', 't_invoice_KD_INVOICE_idx'); // Unique Key

        $this->forge->createTable('t_underlying', true, ['ENGINE' => 'InnoDB', 'DEFAULT CHARSET' => 'utf8']);
    }

    public function down()
    {
        $this->forge->dropTable('t_underlying', true);
    }
}