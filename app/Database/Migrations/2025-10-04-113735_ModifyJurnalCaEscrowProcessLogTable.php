<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ModifyJurnalCaEscrowProcessLogTable extends Migration
{
    public function up()
    {
        // Drop kolom error_message
        $this->forge->dropColumn('t_jurnal_ca_escrow_process_log', 'error_message');
        
        // Rename kolom status menjadi status_code_res
        $this->forge->modifyColumn('t_jurnal_ca_escrow_process_log', [
            'status' => [
                'name' => 'status_code_res',
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
                'comment' => 'Status code response dari API Gateway'
            ]
        ]);
        
        // Tambah kolom is_success
        $fields = [
            'is_success' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'comment' => 'Flag sukses atau tidak (0=failed, 1=success)',
                'after' => 'status_code_res'
            ]
        ];
        
        $this->forge->addColumn('t_jurnal_ca_escrow_process_log', $fields);
    }

    public function down()
    {
        // Kembalikan perubahan
        
        // Drop kolom is_success
        $this->forge->dropColumn('t_jurnal_ca_escrow_process_log', 'is_success');
        
        // Rename kembali status_code_res menjadi status
        $this->forge->modifyColumn('t_jurnal_ca_escrow_process_log', [
            'status_code_res' => [
                'name' => 'status',
                'type' => 'ENUM',
                'constraint' => ['PENDING', 'PROCESSING', 'SUCCESS', 'FAILED'],
                'default' => 'PENDING',
                'null' => false,
                'comment' => 'Status pemrosesan transaksi'
            ]
        ]);
        
        // Tambah kembali kolom error_message
        $fields = [
            'error_message' => [
                'type' => 'TEXT',
                'null' => true,
                'comment' => 'Error message jika gagal',
                'after' => 'api_response'
            ]
        ];
        
        $this->forge->addColumn('t_jurnal_ca_escrow_process_log', $fields);
    }
}
