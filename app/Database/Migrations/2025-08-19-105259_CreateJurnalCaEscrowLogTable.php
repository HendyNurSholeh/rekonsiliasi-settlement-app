<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateJurnalCaEscrowLogTable extends Migration
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
            ],
            'no_ref' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => false,
            ],
            'amount' => [
                'type' => 'DECIMAL',
                'constraint' => '20,2',
                'null' => false,
                'default' => 0,
            ],
            'debit_account' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'credit_account' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['PROCESSING', 'SUCCESS', 'FAILED', 'RETRY'],
                'default' => 'PROCESSING',
            ],
            'response_code' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'core_ref' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'request_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'response_data' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processing_time' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => true,
                'comment' => 'Processing time in seconds',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
            ],
            'user_agent' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'processed_at' => [
                'type' => 'TIMESTAMP',
                'null' => true,
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
        $this->forge->addKey(['kd_settle', 'no_ref'], false, false, 'idx_kd_settle_no_ref');
        $this->forge->addKey('status', false, false, 'idx_status');
        $this->forge->addKey('created_at', false, false, 'idx_created_at');
        $this->forge->addKey('processed_at', false, false, 'idx_processed_at');

        // Create unique constraint to prevent duplicate processing
        $this->forge->addUniqueKey(['kd_settle', 'no_ref', 'status'], 'unique_processing');

        // Create table
        $this->forge->createTable('jurnal_ca_escrow_log');
    }

    public function down()
    {
        $this->forge->dropTable('jurnal_ca_escrow_log');
    }
}
