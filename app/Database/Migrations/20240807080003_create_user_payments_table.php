<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class CreateUserPayments extends Migration
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
            'billing_cycle' => [
                'type'       => 'ENUM',
                'constraint' => ['monthly', 'yearly'],
                'null'       => false,
            ],
            'amount_cents' => [
                'type'       => 'INT',
                'unsigned'   => true,
                'null'       => false,
            ],
            'currency' => [
                'type'       => 'VARCHAR',
                'constraint' => 3,
                'null'       => false,
                'default'    => 'USD',
            ],
            'status' => [
                'type'       => 'ENUM',
                'constraint' => ['succeeded', 'pending', 'failed', 'refunded'],
                'null'       => false,
                'default'    => 'pending',
            ],
            'payment_provider' => [
                'type'       => 'ENUM',
                'constraint' => ['stripe', 'paddle', 'manual'],
                'null'       => true,
            ],
            'provider_payment_id' => [
                'type'       => 'VARCHAR',
                'constraint' => 255,
                'null'       => true,
            ],
            'payment_method' => [
                'type'       => 'VARCHAR',
                'constraint' => 100,
                'null'       => true,
            ],
            'paid_at' => [
                'type'       => 'DATETIME',
                'null'       => true,
            ],
            'metadata' => [
                'type'       => 'JSON',
                'null'       => true,
            ],
            'created_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
            ],
            'updated_at' => [
                'type'       => 'TIMESTAMP',
                'null'       => false,
                'default'    => new \CodeIgniter\Database\RawSql('CURRENT_TIMESTAMP'),
                // ON UPDATE CURRENT_TIMESTAMP (add via raw query if required)
            ],
        ]);

        $this->forge->addPrimaryKey('id');
        $this->forge->addKey(['user_id', 'status', 'paid_at'], false, false, 'user_id_status_paid_at');
        $this->forge->addForeignKey('user_id', 'users', 'id', 'CASCADE', 'CASCADE', 'user_payments_user_id_foreign');

        $this->forge->createTable('user_payments', true);
    }

    public function down()
    {
        $this->forge->dropTable('user_payments', true);
    }
}
