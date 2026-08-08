<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateCronJobs extends Migration
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
            'command' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => false,
            ],
            'custom_command' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'schedule' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => false,
            ],
            'description' => [
                'type'       => 'TEXT',
                'null'       => true,
            ],
            'arguments' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'enabled' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'last_run' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'next_run' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'updated_at' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('enabled', false, false, 'enabled');

        $this->forge->createTable('cron_jobs', true);
    }

    public function down()
    {
        $this->forge->dropTable('cron_jobs', true);
    }
}
