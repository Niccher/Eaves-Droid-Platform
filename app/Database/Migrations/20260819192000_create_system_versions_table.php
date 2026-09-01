<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateSystemVersionsTable extends Migration
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
            'version' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'unique'     => true,
            ],
            'build_number' => [
                'type'       => 'INT',
                'constraint' => 11,
                'unsigned'   => true,
                'null'       => false,
            ],
            'release_name' => [
                'type'       => 'VARCHAR',
                'constraint' => 150,
                'null'       => false,
            ],
            'release_type' => [
                'type'       => 'ENUM',
                'constraint' => ['major', 'minor', 'patch'],
                'default'    => 'minor',
            ],
            'is_current' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'default'    => 0,
            ],
            'released_at datetime default current_timestamp',
        ]);
        $this->forge->addPrimaryKey('id');
        $this->forge->createTable('system_versions', true);
    }

    public function down()
    {
        $this->forge->dropTable('system_versions', true);
    }
}
