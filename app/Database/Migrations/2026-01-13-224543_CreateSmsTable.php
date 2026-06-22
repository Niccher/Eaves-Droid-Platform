<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSmsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'counter' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'android_sms_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'thread_id' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'null'       => false,
            ],
            'address' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'formatted_address' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'body' => [
                'type' => 'LONGTEXT',
                'null' => false,
            ],
            'body_length' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'sms_date' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'sms_date_sent' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'sms_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'type_code' => [
                'type'       => 'INT',
                'constraint' => 5,
                'null'       => false,
            ],
            'is_read' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'is_seen' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'status_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'error_code' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'protocol' => [
                'type'       => 'INT',
                'constraint' => 11,
                'null'       => false,
            ],
            'protocol_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
            ],
            'service_center' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'subject' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'is_locked' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'creator' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
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

        $this->forge->addPrimaryKey('counter');
        $this->forge->addUniqueKey(['android_sms_id', 'device_id', 'owner_id']);
        $this->forge->addKey('address');
        $this->forge->addKey('sms_date');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('thread_id');
        $this->forge->addKey('sms_type');
        $this->forge->addKey('is_read');
        $this->forge->addKey('is_seen');
        $this->forge->addKey('status_code');
        $this->forge->addKey('error_code');
        $this->forge->addKey('protocol_type');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey(['owner_id', 'sms_date']);
        $this->forge->addKey(['device_id', 'sms_date']);
        $this->forge->addKey(['owner_id', 'address']);

        $this->forge->createTable('tbl_sms');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_sms');
    }
}