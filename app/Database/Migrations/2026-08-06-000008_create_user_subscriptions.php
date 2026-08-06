<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSubscriptions extends Migration
{
    public function up()
    {
        $this->forge->addField([
            'id' => [
                'type' => 'BIGINT',
                'unsigned' => true,
                'auto_increment' => true,
            ],
            'user_id' => [
                'type' => 'INT',
                'unsigned' => true,
            ],
            'plan' => [
                'type' => 'ENUM',
                'constraint' => ['free', 'gold', 'platinum'],
            ],
            'status' => [
                'type' => 'ENUM',
                'constraint' => ['active', 'canceled', 'past_due', 'trialing'],
            ],
            'billing_cycle' => [
                'type' => 'ENUM',
                'constraint' => ['monthly', 'yearly'],
            ],
            'current_period_start' => [
                'type' => 'DATETIME',
            ],
            'current_period_end' => [
                'type' => 'DATETIME',
            ],
            'canceled_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'trial_ends_at' => [
                'type' => 'DATETIME',
                'null' => true,
            ],
            'payment_provider' => [
                'type' => 'ENUM',
                'constraint' => ['stripe', 'paddle', 'manual'],
                'null' => true,
            ],
            'provider_subscription_id' => [
                'type' => 'VARCHAR',
                'constraint' => 255,
                'null' => true,
            ],
            'payment_method' => [
                'type' => 'VARCHAR',
                'constraint' => 100,
                'null' => true,
            ],
            'metadata' => [
                'type' => 'JSON',
                'null' => true,
            ],
            'created_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
            'updated_at' => [
                'type' => 'TIMESTAMP',
                'null' => false,
            ],
        ]);

        $this->forge->addKey('id', true);
        $this->forge->addKey(['user_id', 'status']);
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE');
        $this->forge->createTable('user_subscriptions');
        
        // Use raw SQL for timestamp defaults (after table creation)
        $this->db->query("ALTER TABLE `user_subscriptions` 
            MODIFY `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            MODIFY `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP");
    }

    public function down()
    {
        $this->forge->dropTable('user_subscriptions');
    }
}