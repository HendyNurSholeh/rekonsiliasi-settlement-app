<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddCurrencyFieldsToTransaksi extends Migration
{
    public function up()
    {
        $fields = [
            'ID_CURRENCY_CONVERSION' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => true,
                'unsigned'   => true,
                'after'      => 'ID_UNDERLYING',
                'comment'    => 'Relasi ke tabel currency_conversions',
            ],
            'KODE_MATA_UANG' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'null'       => true,
                'after'      => 'ID_CURRENCY_CONVERSION',
                'comment'    => 'Kode mata uang transaksi (misal: USD, EUR, JPY)',
            ],
            'KURS_KE_IDR' => [
                'type'       => 'DOUBLE',
                'constraint' => '18,6',
                'null'       => true,
                'after'      => 'KODE_MATA_UANG',
                'comment'    => 'Kurs mata uang transaksi ke IDR pada saat transaksi',
            ],
            'KURS_USD_KE_IDR' => [
                'type'       => 'DOUBLE',
                'constraint' => '18,6',
                'null'       => true,
                'after'      => 'KURS_KE_IDR',
                'comment'    => 'Kurs USD ke IDR pada saat transaksi',
            ],
            'NOMINAL_USD' => [
                'type'       => 'DOUBLE',
                'constraint' => '18,2',
                'null'       => true,
                'after'      => 'KURS_USD_KE_IDR',
                'comment'    => 'Nominal transaksi dalam USD (fix sesuai kurs saat transaksi)',
            ],
        ];
        $this->forge->addColumn('t_transaksi', $fields);

        // Tambahkan foreign key menggunakan method addForeignKey
        $this->forge->addForeignKey(
            'ID_CURRENCY_CONVERSION',
            't_currency_conversions',
            'ID',
            'SET NULL',
            'CASCADE',
            'fk_transaction_currency_conversion'
        );
        $this->forge->processIndexes('t_transaksi');
    }

    public function down()
    {
        // Hapus foreign key constraint menggunakan dropForeignKey
        $this->forge->dropForeignKey('t_transaksi', 'fk_transaction_currency_conversion');
        $this->forge->dropColumn('t_transaksi', [
            'ID_CURRENCY_CONVERSION',
            'KODE_MATA_UANG',
            'KURS_KE_IDR',
            'KURS_USD_KE_IDR',
            'NOMINAL_USD',
        ]);
    }
}
