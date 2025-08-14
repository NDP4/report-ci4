<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAuxTables extends Migration
{
    public function up()
    {
        // TB_SDM Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'logid' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
                'unique' => true,
            ],
            'fullname' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'fullname_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'channel_name' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'channel_name_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'position' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'unit' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('logid');
        $this->forge->addKey('fullname_norm');
        $this->forge->createTable('tb_sdm');

        // PRESENSI Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'sdm_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'logid' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'fullname_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'work_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'time_in' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'time_out' => [
                'type' => 'TIME',
                'null' => true,
            ],
            'hadir' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('work_date');
        $this->forge->addKey('fullname_norm');
        $this->forge->addForeignKey('sdm_id', 'tb_sdm', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('presensi');

        // QUEUE_ONX Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'source_id' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'fullname_raw' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'fullname_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'channel_name_raw' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'channel_name_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'date_start_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'mainCategory' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'witel' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'raw_payload' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('date_start_interaction');
        $this->forge->addKey('fullname_norm');
        $this->forge->createTable('queue_onx');

        // REPORT_AGENT_LOG Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'fullname_raw' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'fullname_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'date_start' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'state' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'reason_login' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'raw_payload' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'uploaded_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('date_start');
        $this->forge->addKey('fullname_norm');
        $this->forge->createTable('report_agent_log');

        // ROSTER_KORLAP Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'korlap_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'channel_name' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'description' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('roster_korlap');

        // AGENT_BUCKET Table
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'work_date' => [
                'type' => 'DATE',
                'null' => true,
            ],
            'sdm_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'null' => true,
            ],
            'logid' => [
                'type' => 'VARCHAR',
                'constraint' => 64,
                'null' => true,
            ],
            'fullname_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'channel_name_norm' => [
                'type' => 'VARCHAR',
                'constraint' => 128,
                'null' => true,
            ],
            'queue_count' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'has_aux' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'presensi' => [
                'type' => 'TINYINT',
                'constraint' => 1,
                'default' => 0,
            ],
            'bucket' => [
                'type' => 'ENUM',
                'constraint' => ['1', '2', '3', 'idle', 'anomali', 'absent'],
                'default' => 'anomali',
            ],
            'reason' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'updated_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['work_date', 'fullname_norm'], 'uq_work_agent');
        $this->forge->addKey('work_date');
        $this->forge->addKey('bucket');
        $this->forge->addForeignKey('sdm_id', 'tb_sdm', 'id', 'SET NULL', 'SET NULL');
        $this->forge->createTable('agent_bucket');
    }

    public function down()
    {
        $this->forge->dropTable('agent_bucket', true);
        $this->forge->dropTable('roster_korlap', true);
        $this->forge->dropTable('report_agent_log', true);
        $this->forge->dropTable('queue_onx', true);
        $this->forge->dropTable('presensi', true);
        $this->forge->dropTable('tb_sdm', true);
    }
}
