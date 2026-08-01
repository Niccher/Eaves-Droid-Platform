<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class UpgradeTblNotifications extends Migration
{
    public function up()
    {
        $this->forge->addColumn('tbl_notifications', [
            'channel_id'                 => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'channel_name'               => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'channel_importance'         => ['type' => 'INT', 'default' => 0],
            'group_key'                  => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'sort_key'                   => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'is_ongoing'                 => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'is_local_only'              => ['type' => 'TINYINT', 'constraint' => 1, 'default' => 0],
            'color'                      => ['type' => 'INT', 'unsigned' => true, 'null' => true],
            'badge_icon'                 => ['type' => 'INT', 'null' => true],
            'large_icon_base64'          => ['type' => 'LONGTEXT', 'null' => true],
            'actions'                    => ['type' => 'TEXT', 'null' => true],
            'remote_input_history'       => ['type' => 'TEXT', 'null' => true],
            'people'                     => ['type' => 'TEXT', 'null' => true],
            'shortcut_id'                => ['type' => 'VARCHAR', 'constraint' => 100, 'null' => true],
            'locus_id'                   => ['type' => 'VARCHAR', 'constraint' => 255, 'null' => true],
            'bubble_metadata'            => ['type' => 'TEXT', 'null' => true],
            'settings_text'              => ['type' => 'TEXT', 'null' => true],
            'timeout_after'              => ['type' => 'BIGINT', 'null' => true],
            'flags'                      => ['type' => 'INT', 'default' => 0],
            'suppressed_visual_effects'  => ['type' => 'INT', 'default' => 0],
        ]);
    }

    public function down()
    {
        $this->forge->dropColumn('tbl_notifications', [
            'channel_id', 'channel_name', 'channel_importance', 'group_key', 'sort_key',
            'is_ongoing', 'is_local_only', 'color', 'badge_icon', 'large_icon_base64',
            'actions', 'remote_input_history', 'people', 'shortcut_id', 'locus_id',
            'bubble_metadata', 'settings_text', 'timeout_after', 'flags', 'suppressed_visual_effects'
        ]);
    }
}
