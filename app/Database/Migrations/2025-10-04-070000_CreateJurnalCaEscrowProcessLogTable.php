<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJurnalCaEscrowProcessLogTable extends Migration
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
            'kd_settle' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'Kode settlement yang diproses'
            ],
            'request_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'comment' => 'Request ID yang dikirim ke API Gateway'
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['PENDING', 'PROCESSING', 'SUCCESS', 'FAILED'],
                'default' => 'PENDING',
                'comment' => 'Status pemrosesan transaksi'
            ],
            'total_transaksi' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
                'comment' => 'Jumlah transaksi yang dikirim'
            ],
            'api_response' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Response dari API Gateway (JSON)'
            ],
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Error message jika gagal'
            ],
            'sent_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'User yang melakukan proses'
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => 'Waktu pengiriman ke API Gateway'
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Set primary key
        $this->forge->addPrimaryKey('id');

        // Add indexes for performance
        $this->forge->addKey('kd_settle', false, false, 'idx_kd_settle');
        $this->forge->addKey('request_id', false, false, 'idx_request_id');
        $this->forge->addKey('status', false, false, 'idx_status');
        $this->forge->addKey('sent_at', false, false, 'idx_sent_at');

        // Create unique constraint untuk prevent duplicate processing
        $this->forge->addUniqueKey(['kd_settle', 'request_id'], 'unique_kd_settle_request');

        // Create table
        $this->forge->createTable('t_jurnal_ca_escrow_process_log');
    }

    public function down()
    {
        $this->forge->dropTable('t_jurnal_ca_escrow_process_log');
    }
}
