<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class ExpandFeatureTiers extends Migration
{
    public function up()
    {
        $platform = $this->db->getPlatform();
        if (stripos($platform, 'mysql') !== false) {
            $this->db->query("ALTER TABLE tbl_feature_tiers MODIFY COLUMN category_type VARCHAR(50) NOT NULL");
        } else {
            $fields = [
                'category_type' => [
                    'type'       => 'VARCHAR',
                    'constraint' => 50,
                    'null'       => false,
                ]
            ];
            $this->forge->modifyColumn('tbl_feature_tiers', $fields);
        }
    }

    public function down()
    {
        // Revert to ENUM('hardware', 'software')
        $platform = $this->db->getPlatform();
        if (stripos($platform, 'mysql') !== false) {
            $this->db->query("ALTER TABLE tbl_feature_tiers MODIFY COLUMN category_type ENUM('hardware', 'software') NOT NULL");
        } else {
            $fields = [
                'category_type' => [
                    'type'       => 'ENUM',
                    'constraint' => ['hardware', 'software'],
                    'null'       => false,
                ]
            ];
            $this->forge->modifyColumn('tbl_feature_tiers', $fields);
        }
    }
}
