<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateBlocklistTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'           => 'INT',
                'constraint'     => 11,
                'unsigned'       => true,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
            ],
            'category' => [
                'type'       => 'ENUM',
                'constraint' => ['sms', 'call', 'notification', 'app_usage', 'location'],
                'null'       => false,
            ],
            'identifier' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
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
        
        $this->forge->addKey('id', true);
        $this->forge->addKey('owner_id');
        $this->forge->addKey('category');
        $this->forge->addKey(['owner_id', 'category', 'identifier'], false, true); // Unique constraint
        
        $this->forge->createTable('tbl_blocklist', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_blocklist', true);
    }
}
