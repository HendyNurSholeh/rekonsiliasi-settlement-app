<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTransactionDocumentTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'transaksi_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'document_type_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'file_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
            ],
            'file_path' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('transaksi_id', 't_transaksi', 'ID', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('document_type_id', 'document_types', 'id', 'RESTRICT', 'CASCADE');
        $this->forge->createTable('transaction_documents', true);
    }

    public function down()
    {
        $this->forge->dropTable('transaction_documents', true);
    }
}
