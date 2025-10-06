<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

/**
 * Migration untuk menambahkan versioning system ke t_akselgate_transaction_log
 * 
 * Perubahan:
 * 1. Drop unique constraint (kd_settle, transaction_type)
 * 2. Tambah kolom attempt_number (INT) - Nomor percobaan (1, 2, 3, dst)
 * 3. Tambah kolom is_latest (TINYINT) - Flag record terbaru (untuk query cepat)
 * 4. Tambah index untuk performa query
 * 
 * Aturan Bisnis:
 * - Jika is_success = 1: Hanya boleh ada 1 record dengan is_latest = 1
 * - Jika is_success = 0: Boleh proses ulang (insert attempt baru)
 */
class AlterAkselgateTransactionLogAddVersioning extends Migration
{
    public function up()
    {
        // Step 1: Drop unique constraint yang lama
        $this->forge->dropKey('t_akselgate_transaction_log', 'unique_kd_settle_type');
        
        // Step 2: Tambah kolom baru untuk versioning
        $fields = [
            'attempt_number' => [
                'type' => 'INT',
                'constraint' => 11,
                'null' => false,
                'default' => 1,
                'comment' => 'Nomor percobaan transaksi (1, 2, 3, dst)',
                'after' => 'transaction_type'
            ],
            'is_latest' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'null' => false,
                'default' => 1,
                'comment' => '1=Record terbaru, 0=Record lama (untuk query cepat)',
                'after' => 'is_success'
            ],
        ];
        
        $this->forge->addColumn('t_akselgate_transaction_log', $fields);
        
        // Step 3: Tambah index untuk performa query
        // Index untuk query by (kd_settle, transaction_type, is_latest)
        $this->db->query('ALTER TABLE t_akselgate_transaction_log ADD INDEX idx_settle_type_latest (kd_settle, transaction_type, is_latest)');
        
        // Index untuk query by attempt_number
        $this->db->query('ALTER TABLE t_akselgate_transaction_log ADD INDEX idx_attempt (attempt_number)');
        
        // Step 4: Update existing records
        // Set semua existing records sebagai attempt_number = 1 dan is_latest = 1
        $this->db->query('UPDATE t_akselgate_transaction_log SET attempt_number = 1, is_latest = 1');
        
        log_message('info', 'Migration: Added versioning columns (attempt_number, is_latest) to t_akselgate_transaction_log');
    }

    public function down()
    {
        // Step 1: Drop indexes yang ditambahkan
        $this->forge->dropKey('t_akselgate_transaction_log', 'idx_settle_type_latest');
        $this->forge->dropKey('t_akselgate_transaction_log', 'idx_attempt');
        
        // Step 2: Drop kolom versioning
        $this->forge->dropColumn('t_akselgate_transaction_log', ['attempt_number', 'is_latest']);
        
        // Step 3: Restore unique constraint yang lama
        $this->forge->addUniqueKey(['kd_settle', 'transaction_type'], 'unique_kd_settle_type');
        
        log_message('info', 'Migration: Rolled back versioning columns from t_akselgate_transaction_log');
    }
}
