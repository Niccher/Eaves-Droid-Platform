<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanVersions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type'       => 'BIGINT',
                'unsigned'   => true,
                'null'       => false,
                'auto_increment' => true,
            ],
            'plan_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'version' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'price_monthly_cents' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'price_yearly_cents' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 0,
            ],
            'currency' => [
                'type'       => 'CHAR',
                'constraint' => 3,
                'null'       => false,
                'default'    => 'USD',
            ],
            'max_devices' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 1,
            ],
            'history_days' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
                'default'    => 10,
            ],
            'features' => [
                'type'       => 'JSON',
                'null'       => false,
            ],
            'ml_algorithms' => [
                'type'       => 'JSON',
                'null'       => false,
            ],
            'alert_email' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'alert_push' => [
                'type'       => 'TINYINT',
                'constraint' => 1,
                'null'       => false,
                'default'    => 1,
            ],
            'wellbeing_depth' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 7,
            ],
            'support_tier' => [
                'type'       => 'VARCHAR',
                'constraint' => 20,
                'null'       => false,
                'default'    => 'standard',
            ],
            'stripe_price_id_monthly' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'stripe_price_id_yearly' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'paddle_price_id_monthly' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'paddle_price_id_yearly' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'effective_from' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'effective_until' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'change_reason' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'changed_by' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addUniqueKey(['plan_id', 'version'], 'plan_id_version');
        $this->forge->addKey('changed_by', false, false, 'plan_versions_changed_by_foreign');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'CASCADE', 'SET NULL', 'plan_versions_changed_by_foreign');

        $this->forge->createTable('plan_versions', true);
    }

    public function down()
    {
        $this->forge->dropTable('plan_versions', true);
    }
}
