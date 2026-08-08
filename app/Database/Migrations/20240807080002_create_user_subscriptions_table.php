<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserSubscriptions extends Migration
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
            'user_id' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'plan' => [
                'type'       => 'ENUM',
                'constraint' => ['free', 'gold', 'platinum'],
                'null'       => false,
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['active', 'canceled', 'past_due', 'trialing'],
                'null'       => false,
            ],
            'billing_cycle' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'yearly'],
                'null'       => false,
            ],
            'current_period_start' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'current_period_end' => [
                'type'       => 'DATETIME',
                'null'       => false,
            ],
            'canceled_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'trial_ends_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'payment_provider' => [
                'type'       => 'ENUM',
                'constraint' => ['stripe', 'paddle', 'manual'],
                'null'       => true,
            ],
            'provider_subscription_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'metadata' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
            ],
            'updated_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => 'CURRENT_TIMESTAMP',
                // ON UPDATE CURRENT_TIMESTAMP (add via raw query if required)
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'status'], false, false, 'user_id_status');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'user_subscriptions_user_id_foreign');

        $this->forge->createTable('user_subscriptions', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_subscriptions', true);
    }
}
