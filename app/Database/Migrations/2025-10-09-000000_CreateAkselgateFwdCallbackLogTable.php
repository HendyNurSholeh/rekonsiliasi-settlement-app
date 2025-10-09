<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk tabel t_akselgatefwd_callback_log
 * 
 * Tabel ini menyimpan log callback dari Aksel FWD (Forward) API
 * Callback diterima per transaksi individual (bukan batch) dengan delay
 * 
 * Flow:
 * 1. Aplikasi kirim batch transaksi ke Aksel Gateway (tercatat di t_akselgate_transaction_log)
 * 2. Aksel FWD proses transaksi satu-per-satu ke core banking dengan delay
 * 3. Setiap transaksi selesai, Aksel FWD kirim callback (tercatat di tabel ini)
 * 4. Callback update status di t_settle_message
 */
class CreateAkselgateFwdCallbackLogTable extends Migration
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
            'ref_number' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => false,
                'comment' => 'Reference Number dari t_settle_message (REF_NUMBER)',
            ],
            'kd_settle' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
                'comment' => 'Kode settlement (dari t_settle_message untuk kemudahan query)',
            ],
            'res_code' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'null' => true,
                'comment' => 'Response code dari core banking (00=success)',
            ],
            'res_coreref' => [
                'type' => 'VARCHAR',
                'constraint' => 15,
                'null' => true,
                'comment' => 'Core Reference Number dari core banking',
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['SUCCESS', 'FAILED'],
                'null' => false,
                'comment' => 'Status callback (SUCCESS jika res_code=00, FAILED jika lainnya)',
            ],
            'callback_data' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Raw callback data (JSON) untuk audit',
            ],
            'ip_address' => [
                'type' => 'VARCHAR',
                'constraint' => 45,
                'null' => true,
                'comment' => 'IP address pengirim callback (untuk security)',
            ],
            'is_processed' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 0,
                'comment' => '0=Belum diproses ke t_settle_message, 1=Sudah diproses',
            ],
            'processed_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu diproses ke t_settle_message',
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'comment' => 'Waktu callback diterima',
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);

        // Set primary key
        $this->forge->addPrimaryKey('id');

        // Add indexes untuk query performance
        $this->forge->addKey('ref_number'); // Query by REF_NUMBER (most used)
        $this->forge->addKey('kd_settle'); // Query by KD_SETTLE
        $this->forge->addKey('status'); // Filter by status
        $this->forge->addKey('is_processed'); // Filter unprocessed callbacks
        $this->forge->addKey('created_at'); // Sort by time
        
        // Composite index untuk query kompleks
        $this->forge->addKey(['kd_settle', 'status'], false, false, 'idx_settle_status');
        $this->forge->addKey(['ref_number', 'is_processed'], false, false, 'idx_ref_processed');
        
        // Create table
        $this->forge->createTable('t_akselgatefwd_callback_log');
        
        log_message('info', 'Migration: Created table t_akselgatefwd_callback_log');
    }

    public function down()
    {
        $this->forge->dropTable('t_akselgatefwd_callback_log');
        
        log_message('info', 'Migration: Dropped table t_akselgatefwd_callback_log');
    }
}
