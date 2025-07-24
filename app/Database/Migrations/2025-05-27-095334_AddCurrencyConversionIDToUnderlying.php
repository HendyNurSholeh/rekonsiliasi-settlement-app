<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrencyConversionIDToUnderlying extends Migration
{
    public function up()
    {
        $fields = [
            'CURRENCY_CONVERSION_ID' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'after'      => 'USER_ID',
            ],
        ];
        $this->forge->addColumn('t_underlying', $fields);

        // Tambahkan foreign key menggunakan addForeignKey bawaan CI4
        $this->forge->addForeignKey(
            'CURRENCY_CONVERSION_ID',
            't_currency_conversions',
            'ID',
            'SET NULL',
            'CASCADE',
            'fk_transaksi_currency_conversion'
        );
        $this->forge->processIndexes('t_underlying');
    }

    public function down()
    {
        // Hapus foreign key constraint menggunakan dropForeignKey
        $this->forge->dropForeignKey('t_underlying', 'fk_transaksi_currency_conversion');
        $this->forge->dropColumn('t_underlying', 'CURRENCY_CONVERSION_ID');
    }
}
