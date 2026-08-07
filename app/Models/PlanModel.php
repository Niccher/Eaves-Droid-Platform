<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanModel extends Model
{
    protected $table = 'plans';
    protected $primaryKey = 'id';
    protected $allowedFields = ['slug', 'name', 'description', 'is_active', 'sort_order'];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = 'updated_at';

    public function getCurrentVersion(string $slug): ?array
    {
        $plan = $this->where('slug', $slug)->where('is_active', 1)->first();
        if (!$plan) return null;

        $plan = $this->db->table('plan_versions pv')
            ->select('pv.*, p.slug, p.name as plan_name')
            ->join('plans p', 'p.id = pv.plan_id')
            ->where('pv.plan_id', $plan['id'])
            ->where('pv.effective_from <=', date('Y-m-d H:i:s'))
            ->groupStart()
                ->where('pv.effective_until IS NULL')
                ->orWhere('pv.effective_until >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->orderBy('pv.version', 'DESC')
            ->limit(1)
            ->get()->getRowArray();

        if (!$plan) {
            return null;
        }

        if (isset($plan['features']) && is_string($plan['features'])) {
            $plan['features'] = json_decode($plan['features'], true) ?? [];
        }
        if (isset($plan['ml_algorithms']) && is_string($plan['ml_algorithms'])) {
            $plan['ml_algorithms'] = json_decode($plan['ml_algorithms'], true) ?? [];
        }

        return $plan;
    }

    public function getAllCurrentVersions(): array
    {
        $db = $this->db;
        $now = date('Y-m-d H:i:s');
        
        $sql = "SELECT pv.*, p.slug, p.name as plan_name
                FROM plan_versions pv
                JOIN plans p ON p.id = pv.plan_id
                WHERE p.is_active = 1
                AND pv.effective_from <= ?
                AND (pv.effective_until IS NULL OR pv.effective_until > ?)
                ORDER BY p.sort_order, p.id";
        
        return array_column(
            $db->query($sql, [$now, $now])->getResultArray(),
            null,
            'plan_id'
        );
    }

    /**
     * Return an array of current plan version rows keyed by plan slug.
     * Each row contains at least: plan (slug), features (array), price_monthly_cents,
     * price_yearly_cents, currency.
     */
    public function getCurrentVersions(): array
    {
        $rows = $this->getAllCurrentVersions();
        $result = [];
        foreach ($rows as $row) {
            $slug = $row['slug'] ?? '';
            if ($slug) {
                // Ensure features is array
                if (isset($row['features']) && is_string($row['features'])) {
                    $row['features'] = json_decode($row['features'], true) ?? [];
                }
                $result[$slug] = $row;
            }
        }
        return $result;
    }

    public function getVersionHistory(int $planId): array
    {
        return $this->db->table('plan_versions')
            ->where('plan_id', $planId)
            ->orderBy('version', 'DESC')
            ->get()->getResultArray();
    }
}