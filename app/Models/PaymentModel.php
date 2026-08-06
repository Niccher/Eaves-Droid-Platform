<?php

namespace App\Models;

use CodeIgniter\Model;

class PaymentModel extends Model
{
    protected $table = 'user_payments';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'user_id', 'plan', 'billing_cycle', 'amount_cents', 'currency',
        'status', 'payment_provider', 'provider_payment_id',
        'payment_method', 'paid_at', 'metadata',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getForUser(int $userId): array
    {
        return $this->where('user_id', $userId)
            ->orderBy('paid_at', 'DESC')
            ->findAll();
    }

    public function getWithUser(?string $status = null): array
    {
        $db = \Config\Database::connect();

        $builder = $db->table('user_payments p')
            ->select('p.*, u.id AS user_id, u.username, ai.secret AS user_email')
            ->join('users u', 'u.id = p.user_id', 'left')
            ->join('auth_identities ai', 'ai.user_id = u.id AND ai.type = "email_password"', 'left')
            ->orderBy('p.paid_at', 'DESC');

        if ($status && $status !== 'all') {
            $builder->where('p.status', $status);
        }

        return $builder->get()->getResultArray();
    }

    public function getStats(): array
    {
        $db = \Config\Database::connect();

        $totalRevenue = $db->table('user_payments')
            ->selectSum('amount_cents')
            ->where('status', 'succeeded')
            ->get()
            ->getRowArray()['amount_cents'] ?? 0;

        $monthRevenue = $db->table('user_payments')
            ->selectSum('amount_cents')
            ->where('status', 'succeeded')
            ->where('paid_at >=', date('Y-m-01'))
            ->get()
            ->getRowArray()['amount_cents'] ?? 0;

        $succeeded = $db->table('user_payments')->where('status', 'succeeded')->countAllResults();
        $failed = $db->table('user_payments')->where('status', 'failed')->countAllResults();
        $pending = $db->table('user_payments')->where('status', 'pending')->countAllResults();
        $refunded = $db->table('user_payments')->where('status', 'refunded')->countAllResults();

        return [
            'total_revenue_cents' => (int)$totalRevenue,
            'month_revenue_cents' => (int)$monthRevenue,
            'succeeded' => $succeeded,
            'failed' => $failed,
            'pending' => $pending,
            'refunded' => $refunded,
        ];
    }
}
