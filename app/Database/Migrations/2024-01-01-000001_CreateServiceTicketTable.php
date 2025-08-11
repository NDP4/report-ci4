<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateServiceTicketTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'ticket_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => false,
            ],
            'subject' => [
                'type' => 'VARCHAR',
                'constraint' => 500,
                'null' => true,
            ],
            'remark' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'priority_id' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'priority_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'ticket_status_name' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'unit_id' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'unit_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'informant_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'informant_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'informant_hp' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'informant_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_hp' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'customer_email' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'date_origin_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_start_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_open' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_close' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_last_update' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'is_escalated' => [
                'type' => 'VARCHAR',
                'constraint' => 10,
                'null' => true,
            ],
            'created_by_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'updated_by_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'channel_id' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'session_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'category_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'category_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'date_created_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'sla' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'channel_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'main_category' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'category' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'sub_category' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'detail_sub_category' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'detail_sub_category2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'regional' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'type_queue_priority' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'group_id' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'group_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'date_first_pickup_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'status_case' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'indihome_num' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'witel' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'feedback' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'date_first_response_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_pickup_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'date_end_interaction' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'case_in' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'case_out' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'account' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'account_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'informant_member_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_member_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'shift' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'status_date' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'sentiment_incoming' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'sentiment_outgoing' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'sentiment_all' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'sentiment_service' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'parent_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'count_merged' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'source_id' => [
                'type' => 'DOUBLE',
                'constraint' => '15,4',
                'null' => true,
            ],
            'source_name' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'msisdn' => [
                'type' => 'VARCHAR',
                'constraint' => 50,
                'null' => true,
            ],
            'from_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'from_username' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ticket_id_digipos' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ticket_customer_consent' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ticket_no_indi_home_alternatif' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'sla_second' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'informant_1' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'informant_2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_1' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'customer_2' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'ticket_no_k_t_p' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
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

        $this->forge->addPrimaryKey('ticket_id');
        $this->forge->addKey(['date_start_interaction', 'main_category', 'category', 'witel']);
        $this->forge->createTable('service_tickets');
    }

    public function down()
    {
        $this->forge->dropTable('service_tickets');
    }
}
