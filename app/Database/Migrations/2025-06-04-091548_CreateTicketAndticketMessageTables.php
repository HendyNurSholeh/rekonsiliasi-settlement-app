<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTicketAndTicketMessageTables extends Migration
{
    public function up()
    {
        // Tabel Ticket
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'subject'    => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => false],
            'status'     => ['type' => 'VARCHAR', 'constraint' => 20, 'default' => 'open'],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
            'updated_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('t_ticket', true);

        // Tabel Ticket Message
        $this->forge->addField([
            'id'         => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'auto_increment' => true],
            'ticket_id'  => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'user_id'    => ['type' => 'INT', 'constraint' => 11, 'unsigned' => true, 'null' => false],
            'message'    => ['type' => 'TEXT', 'null' => false],
            'created_at' => ['type' => 'DATETIME', 'null' => true],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('ticket_id', 't_ticket', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('t_ticket_message', true);
    }

    public function down()
    {
        $this->forge->dropTable('t_ticket_message', true);
        $this->forge->dropTable('t_ticket', true);
    }
}