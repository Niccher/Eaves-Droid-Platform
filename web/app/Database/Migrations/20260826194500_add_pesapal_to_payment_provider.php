<?php

namespace App\Database\Migrations;

use CodeIgniter\Database\Migration;

class AddPesapalToPaymentProvider extends Migration
{
    public function up()
    {
        // Alter user_subscriptions
        $this->db->query("ALTER TABLE user_subscriptions MODIFY COLUMN payment_provider ENUM('stripe', 'paddle', 'manual', 'pesapal') NULL");
        
        // Alter user_payments
        $this->db->query("ALTER TABLE user_payments MODIFY COLUMN payment_provider ENUM('stripe', 'paddle', 'manual', 'pesapal') NULL");
    }

    public function down()
    {
        // Revert columns
        $this->db->query("ALTER TABLE user_subscriptions MODIFY COLUMN payment_provider ENUM('stripe', 'paddle', 'manual') NULL");
        $this->db->query("ALTER TABLE user_payments MODIFY COLUMN payment_provider ENUM('stripe', 'paddle', 'manual') NULL");
    }
}
