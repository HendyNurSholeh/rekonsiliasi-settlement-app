<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AlterNominalToDouble180UnderlayingTable extends Migration
{
    public function up()
    {
        $fields = [
            'NOMINAL' => [
                'name'       => 'NOMINAL',
                'type'       => 'DOUBLE',
                'constraint' => '18,0',
                'null'       => true,
            ],
        ];

        $this->forge->modifyColumn('t_underlying', $fields);
    }

    public function down()
    {
        $fields = [
            'NOMINAL' => [
                'name'       => 'NOMINAL',
                'type'       => 'DOUBLE',
                'constraint' => '18,2',
                'null'       => true,
            ],
        ];

        $this->forge->modifyColumn('t_underlying', $fields);
    }
}
