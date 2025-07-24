<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCifAndTypeToTransaksi extends Migration
{
    public function up()
    {
        $this->forge->addColumn('t_transaksi', [
            'CIF' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => true,
                'after'      => 'NAMA_NASABAH'
            ],
            'TYPE' => [
                'type'       => "ENUM('underlying','non-underlying')",
                'null'       => true,
                'after'      => 'CIF'
            ],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('t_transaksi', ['CIF', 'TYPE']);
    }
}
