<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ChangeTypeToIsUnderlyingInTransaksi extends Migration
{
    public function up()
    {
        // Drop kolom TYPE
        $this->forge->dropColumn('t_transaksi', 'TYPE');
        // Tambah kolom IS_UNDERLYING (boolean)
        $this->forge->addColumn('t_transaksi', [
            'IS_UNDERLYING' => [
                'type' => 'BOOLEAN',
                'null' => false,
                'default' => 0,
                'after' => 'CIF',
                'comment' => '1 = underlying, 0 = non-underlying'
            ]
        ]);
    }

    public function down()
    {
        // Hapus kolom IS_UNDERLYING
        $this->forge->dropColumn('t_transaksi', 'IS_UNDERLYING');
        // Tambah kembali kolom TYPE
        $this->forge->addColumn('t_transaksi', [
            'TYPE' => [
                'type' => "ENUM('underlying','non-underlying')",
                'null' => true,
                'after' => 'CIF'
            ]
        ]);
    }
}
