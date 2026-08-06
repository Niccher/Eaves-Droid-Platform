<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreatePlanVersions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'plan_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'version' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'price_monthly_cents' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
            ],
            'price_yearly_cents' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 0,
            ],
            'currency' => [
                'type' => 'CHAR',
                'constraint' => 3,
                'default' => 'USD',
            ],
            'max_devices' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 1,
            ],
            'history_days' => [
                'type' => 'INT',
                'unsigned' => true,
                'default' => 10,
            ],
            'features' => [
                'type' => 'JSON',
            ],
            'ml_algorithms' => [
                'type' => 'JSON',
            ],
            'stripe_price_id_monthly' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'stripe_price_id_yearly' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'paddle_price_id_monthly' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'paddle_price_id_yearly' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'effective_from' => [
                'type' => 'DATETIME',
            ],
            'effective_until' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'change_reason' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'changed_by' => [
                'type' => 'INT',
                'unsigned' => true,
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['plan_id', 'version'], '', true); // unique
        $this->forge->addForeignKey('plan_id', 'plans', 'id', 'CASCADE', 'CASCADE');
        $this->forge->addForeignKey('changed_by', 'users', 'id', 'SET NULL', 'CASCADE');
        $this->forge->createTable('plan_versions');
        
        // Use raw SQL for timestamp defaults (after table creation)
        $this->db->query("ALTER TABLE `plan_versions` 
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('plan_versions');
    }
}