<?php

namespace App\Database\Seeds;

use CodeIgniter\Database\Seeder;

class SubscriptionSeeder extends Seeder
{
    public function run()
    {
        $now = date('Y-m-d H:i:s');

        // user_id => [plan, status, billing_cycle, payment_provider, payment_method, amount_cents]
        $subscriptions = [
            2  => ['platinum', 'active',  'monthly', 'stripe', 'visa', 999],
            3  => ['gold',     'active',  'yearly',  'paddle', 'paypal', 4990],
            4  => ['gold',     'past_due','monthly', 'stripe', 'mastercard', 499],
            5  => ['free',     'active',  'monthly', 'manual', 'cash', 0],
            13 => ['platinum', 'active',  'yearly',  'stripe', 'visa', 9990],
            14 => ['free',     'active',  'monthly', 'manual', 'cash', 0],
        ];

        foreach ($subscriptions as $userId => [$plan, $status, $cycle, $provider, $method, $amount]) {
            $periodStart = date('Y-m-d H:i:s', strtotime($plan === 'free' || $amount === 0 ? '-30 days' : '-28 days'));
            $periodEnd = date('Y-m-d H:i:s', strtotime($plan === 'free' || $amount === 0 ? '+30 days' : '+2 days'));

            // Subscription
            $this->db->table('user_subscriptions')->where('user_id', $userId)->delete();
            $this->db->table('user_subscriptions')->insert([
                'user_id' => $userId,
                'plan' => $plan,
                'status' => $status,
                'billing_cycle' => $cycle,
                'current_period_start' => $periodStart,
                'current_period_end' => $periodEnd,
                'canceled_at' => $status === 'canceled' ? date('Y-m-d H:i:s', strtotime('-5 days')) : null,
                'payment_provider' => $provider,
                'payment_method' => $method,
                'created_at' => date('Y-m-d H:i:s', strtotime('-30 days')),
                'updated_at' => $now,
            ]);

            // Payments
            if ($amount > 0) {
                $this->db->table('user_payments')->where('user_id', $userId)->delete();
                // 3 historical payments + 1 recent
                $dates = ['-90 days', '-60 days', '-30 days', '-2 days'];
                foreach ($dates as $i => $offset) {
                    $this->db->table('user_payments')->insert([
                        'user_id' => $userId,
                        'plan' => $plan,
                        'billing_cycle' => $cycle,
                        'amount_cents' => $amount,
                        'currency' => 'USD',
                        'status' => $i === 3 ? 'succeeded' : 'succeeded',
                        'payment_provider' => $provider,
                        'payment_method' => $method,
                        'paid_at' => date('Y-m-d H:i:s', strtotime($offset)),
                        'created_at' => date('Y-m-d H:i:s', strtotime($offset)),
                        'updated_at' => $now,
                    ]);
                }
            }
        }

        echo "Seeded subscriptions and payments.\n";
    }
}
