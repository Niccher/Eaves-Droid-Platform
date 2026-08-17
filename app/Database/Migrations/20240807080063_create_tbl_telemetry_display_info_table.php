<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateTblTelemetryDisplayInfo extends Migration
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
            'width_px' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'height_px' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'density' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'density_dpi' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'xdpi' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'ydpi' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'scaled_density' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'real_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'real_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'usable_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'usable_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'rotation' => [
                'type'       => 'TINYINT',
                'null'       => true,
            ],
            'refresh_rate' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'mode_width' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'mode_height' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'mode_refresh_rate' => [
                'type'       => 'FLOAT',
                'null'       => true,
            ],
            'displays_json' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'screen_layout' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'smallest_screen_width_dp' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'ui_mode' => [
                'type'       => 'INT',
                'null'       => true,
            ],
            'extracted_at' => [
                'type'       => 'BIGINT',
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
        $this->forge->addKey('owner_id', false, false, 'owner_id');
        $this->forge->addKey('device_id', false, false, 'device_id');
        $this->forge->addKey('extracted_at', false, false, 'extracted_at');

        $this->forge->createTable('tbl_telemetry_display_info', true);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_telemetry_display_info', true);
    }
}
