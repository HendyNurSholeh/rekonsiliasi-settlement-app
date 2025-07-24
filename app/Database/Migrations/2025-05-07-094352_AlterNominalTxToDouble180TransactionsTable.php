<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterNominalTxToDouble180TransactionsTable extends Migration
{
    public function up()
    {
        $fields = [
            'NOMINAL_TX' => [
                'name'       => 'NOMINAL_TX',
                'type'       => 'DOUBLE',
                'constraint' => '18,0',
                'null'       => true,
            ],
        ];

        $this->forge->modifyColumn('t_transaksi', $fields);
    }

    public function down()
    {
        $fields = [
            'NOMINAL_TX' => [
                'name'       => 'NOMINAL_TX',
                'type'       => 'DOUBLE',
                'constraint' => '18,2',
                'null'       => true,
            ],
        ];

        $this->forge->modifyColumn('t_transaksi', $fields);
    }
}
