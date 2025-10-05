<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAkselgateTransactionLogTable extends Migration
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
            'transaction_type' => [
                'type' => 'ENUM',
                'constraint' => ['CA_ESCROW', 'ESCROW_BILLER_PL'],
                'null' => false,
                'comment' => 'Jenis transaksi yang diproses',
            ],
            'kd_settle' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => false,
                'comment' => 'Kode settlement',
            ],
            'request_id' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
                'comment' => 'Request ID unik yang dikirim ke AKSEL Gateway',
            ],
            'total_transaksi' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 0,
                'comment' => 'Jumlah transaksi dalam batch',
            ],
            'request_payload' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Request JSON yang dikirim ke AKSEL Gateway',
            ],
            'status_code_res' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'HTTP status code response (200, 201, 400, etc)',
            ],
            'response_code' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'Response code dari AKSEL Gateway API',
            ],
            'response_message' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
                'comment' => 'Response message dari AKSEL Gateway',
            ],
            'response_payload' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Full response JSON dari AKSEL Gateway',
            ],
            'is_success' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'comment' => '0=Failed/Pending, 1=Success',
            ],
            'sent_by' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
                'comment' => 'User yang melakukan proses',
            ],
            'sent_at' => [
                'type' => 'DATETIME',
                'null' => false,
                'comment' => 'Waktu pengiriman ke AKSEL Gateway',
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
        $this->forge->addKey('transaction_type');
        $this->forge->addKey('kd_settle');
        $this->forge->addKey('request_id');
        $this->forge->addKey('is_success');
        $this->forge->addKey('sent_at');
        
        // Unique constraint untuk prevent duplicate per transaction type
        $this->forge->addUniqueKey(['kd_settle', 'transaction_type'], 'unique_kd_settle_type');
        
        // Create table
        $this->forge->createTable('t_akselgate_transaction_log');
    }

    public function down()
    {
        $this->forge->dropTable('t_akselgate_transaction_log');
    }
}
