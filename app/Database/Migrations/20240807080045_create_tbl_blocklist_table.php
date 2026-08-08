<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblBlocklist extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'owner_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
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
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['owner_id', 'category', 'identifier'], 'owner_id_category_identifier');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('category', false, false, 'category');

        $this->forge->createTable('tbl_blocklist', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_blocklist', true);
    }
}
