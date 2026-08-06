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

        return $this->db->table('plan_versions pv')
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

    public function getVersionHistory(int $planId): array
    {
        return $this->db->table('plan_versions')
            ->where('plan_id', $planId)
            ->orderBy('version', 'DESC')
            ->get()->getResultArray();
    }
}