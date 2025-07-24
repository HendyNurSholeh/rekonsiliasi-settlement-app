<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class DropKdInvoiceFromUnderlying extends Migration
{
    public function up()
    {
        // Hapus unique key jika ada
        $this->forge->dropKey('t_underlying', 't_invoice_KD_INVOICE_idx');
        // Hapus kolom KD_INVOICE
        $this->forge->dropColumn('t_underlying', 'KD_INVOICE');
    }

    public function down()
    {
        // Tambahkan kembali kolom KD_INVOICE
        $fields = [
            'KD_INVOICE' => [
                'type' => 'VARCHAR',
                'constraint' => 5,
                'null' => true,
                'comment' => 'diisi kode invoice dari sistem 5 digit, numerik dan urut cth 00001, 00002 dst',
            ],
        ];
        $this->forge->addColumn('t_underlying', $fields);
        // Tambahkan kembali unique key
        $this->forge->addKey('KD_INVOICE', true, true, 't_invoice_KD_INVOICE_idx');
        $this->forge->processIndexes('t_underlying');
    }
}