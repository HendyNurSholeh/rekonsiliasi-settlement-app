<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddNilaiTukarBeliKeIdrToCurrencyConversions extends Migration
{
    public function up()
    {
        $fields = [
            'NILAI_TUKAR_BELI_KE_IDR' => [
                'type' => 'DECIMAL',
                'constraint' => '15,6',
                'null' => true,
                'after' => 'NILAI_TUKAR_KE_IDR',
            ],
        ];
        $this->forge->addColumn('t_currency_conversions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('t_currency_conversions', 'NILAI_TUKAR_BELI_KE_IDR');
    }
}
