<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class EnhanceLogsTable extends Migration
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
            'contact_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'phone_number' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'call_type' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => false,
            ],
            'call_date' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => false,
            ],
            'duration_seconds' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'formatted_duration' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
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
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'is_synced' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'sync_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'last_sync' => [
                'type' => 'DATETIME',
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
            'call_direction' => [
                'type'       => 'ENUM',
                'constraint' => ['incoming', 'outgoing', 'missed'],
                'null'       => true,
            ],
            'call_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 1,
                'null'       => false,
            ],
            'timezone' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'geolocation' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('counter');
        $this->forge->addKey('phone_number');
        $this->forge->addKey('call_date');
        $this->forge->addKey('call_type');
        $this->forge->addKey('device_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('created_at');
        $this->forge->addKey('updated_at');
        $this->forge->addKey('is_synced');
        $this->forge->addKey(['owner_id', 'call_date']);
        $this->forge->addKey(['device_id', 'call_date']);
        $this->forge->addKey(['phone_number', 'call_date']);
        $this->forge->addKey(['call_type', 'call_date']);
        $this->forge->addKey('contact_name');
        $this->forge->addKey('duration_seconds');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('sync_count');
        $this->forge->addKey('last_sync');
        $this->forge->addKey('call_direction');
        $this->forge->addKey('call_count');

        $this->forge->createTable('tbl_logs');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_logs');
    }
}