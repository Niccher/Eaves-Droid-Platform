<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class TblFcmRetryQueue extends Migration
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
            'fcm_token' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
            ],
            'command' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
            ],
            'payload' => [
                'type' => 'TEXT', // Fallback if JSON is tricky
            ],
            'attempts' => [
                'type'       => 'TINYINT',
                'default'    => 0,
            ],
            'last_error' => [
                'type' => 'TEXT',
                'null' => true,
            ],
            'next_retry' => [
                'type' => 'DATETIME',
            ],
            'action_log_id' => [
                'type' => 'INT',
                'constraint' => 11,
                'default' => 0,
            ],
            'created_at' => [
                'type'    => 'DATETIME',
                'null'    => true,
            ],
        ]);
        $this->forge->addKey('id', true);
        $this->forge->addKey('next_retry');
        $this->forge->createTable('tbl_fcm_retry_queue', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_fcm_retry_queue', true);
    }
}
