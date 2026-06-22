<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateContactsTable extends Migration
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
            'contact_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'display_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'phone_numbers' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'phone_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'emails' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'email_count' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
                'null'       => false,
            ],
            'photo_uri' => [
                'type'       => 'VARCHAR',
                'constraint' => 500,
                'null'       => true,
            ],
            'companies' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'addresses' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'notes' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'is_favorite' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
                'null'       => false,
            ],
            'last_contacted' => [
                'type'       => 'BIGINT',
                'constraint' => 20,
                'unsigned'   => true,
                'null'       => true,
            ],
            'contact_frequency' => [
                'type'       => 'INT',
                'constraint' => 11,
                'default'    => 0,
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
            'is_active' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 1,
                'null'       => false,
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
        $this->forge->addUniqueKey(['contact_id', 'device_id', 'owner_id']);
        $this->forge->addKey('display_name');
        $this->forge->addKey('contact_id');
        $this->forge->addKey('device_id');
        $this->forge->addKey('owner_id');
        $this->forge->addKey('is_favorite');
        $this->forge->addKey('is_active');
        $this->forge->addKey('created_at');
        $this->forge->addKey(['owner_id', 'device_id']);
        $this->forge->addKey(['owner_id', 'is_favorite']);
        $this->forge->addKey(['owner_id', 'display_name']);
        $this->forge->addKey(['owner_id', 'contact_frequency']);
        $this->forge->addKey('phone_count');
        $this->forge->addKey('email_count');
        $this->forge->addKey('last_contacted');
        $this->forge->addKey('extracted_at');
        $this->forge->addKey('is_synced');
        $this->forge->addKey('last_sync');
        $this->forge->addKey('updated_at');

        $this->forge->createTable('tbl_contacts');
    }

    public function down()
    {
        $this->forge->dropTable('tbl_contacts');
    }
}