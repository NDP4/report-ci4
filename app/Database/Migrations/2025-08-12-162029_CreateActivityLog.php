<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateActivityLog extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id'          => [
                'type'           => 'INT',
                'unsigned'      => true,
                'auto_increment' => true
            ],
            'user_id'    => [
                'type'       => 'INT',
                'unsigned'  => true,
            ],
            'action'     => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'description'=> [
                'type'       => 'TEXT',
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'default'    => 'CURRENT_TIMESTAMP'
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'default'    => 'CURRENT_TIMESTAMP',
                'on_update'  => 'CURRENT_TIMESTAMP'
            ]
        ]);
        $this->forge->addKey('id', true);
        $this->forge->createTable('activity_log');
    }

    public function down()
    {
        $this->forge->dropTable('activity_log');
    }
}
