<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddUserIdToTicketMessage extends Migration
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
            'fk_ticket_message_user'
        );
        $this->forge->processIndexes('t_ticket_message');
    }

    public function down()
    {
        // Hapus foreign key menggunakan fitur forge
        $this->forge->dropForeignKey('t_ticket_message', 'fk_ticket_message_user');
        // Kolom user_id tidak dihapus agar data pesan tetap terjaga
    }
}
