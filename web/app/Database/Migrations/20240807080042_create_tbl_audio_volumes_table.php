<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblAudioVolumes extends Migration
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
                'null'       => true,
            ],
            'device_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'stream' => [
                'type'       => 'VARCHAR',
                'constraint' => 50,
                'null'       => true,
            ],
            'volume_min' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'volume_max' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'volume_current' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'is_muted' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => true,
                'default'    => 0,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('stream', false, false, 'stream');

        $this->forge->createTable('tbl_audio_volumes', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_audio_volumes', true);
    }
}
