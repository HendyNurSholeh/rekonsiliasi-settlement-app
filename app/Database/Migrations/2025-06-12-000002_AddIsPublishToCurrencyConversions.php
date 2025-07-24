<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddIsPublishToCurrencyConversions extends Migration
{
    public function up()
    {
        $fields = [
            'IS_PUBLISH' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
                'null' => false,
                'after' => 'NILAI_TUKAR_BELI_KE_IDR',
            ],
        ];
        $this->forge->addColumn('t_currency_conversions', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('t_currency_conversions', 'IS_PUBLISH');
    }
}
