<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToTicket extends Migration
{
    public function up()
    {

        // Tambahkan foreign key menggunakan fitur forge
        $this->forge->addForeignKey(
            'user_id',
            'users',
            'id',
            'CASCADE',
            'CASCADE',
            'fk_ticket_user'
        );
        $this->forge->processIndexes('t_ticket');
    }

    public function down()
    {
        // Hapus foreign key menggunakan fitur forge
        $this->forge->dropForeignKey('t_ticket', 'fk_ticket_user');
        // Kolom user_id tidak dihapus agar data pesan tetap terjaga
    }
}
