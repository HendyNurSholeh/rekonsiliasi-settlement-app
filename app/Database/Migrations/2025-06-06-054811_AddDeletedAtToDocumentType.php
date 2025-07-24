<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddDeletedAtToDocumentType extends Migration
{
    public function up()
    {
        $fields = [
            'deleted_at' => [
                'type' => 'DATETIME',
                'null' => true,
                'after' => 'updated_at',
            ],
        ];
        $this->forge->addColumn('document_types', $fields);
    }

    public function down()
    {
        $this->forge->dropColumn('document_types', 'deleted_at');
    }
}
