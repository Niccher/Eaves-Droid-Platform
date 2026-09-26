<?php

namespace App\Models;

use CodeIgniter\Model;

class SimConfigModel extends Model
{
    protected $table = 'tbl_sim_configs';
    protected $primaryKey = 'id';
    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'owner_id',
        'device_id',
        'sim_serial',
        'subscriber_id',
        'sim_operator_name',
        'sim_country_iso',
        'sim_state',
        'phone_type',
        'is_sim_changed',
        'captured_at',
        'extracted_at',
        'created_at',
        'updated_at',
    ];

    protected $useTimestamps = true;
    protected $createdField  = 'created_at';
    protected $updatedField  = 'updated_at';

    public function getSimConfigs(int $userId, string $deviceId = null, int $perPage = 25): array
    {
        $builder = $this->db->table('tbl_sim_configs');
        $builder->where('owner_id', $userId);

        if ($deviceId !== null) {
            $builder->where('device_id', $deviceId);
        }

        $total = $builder->countAllResults(false);

        $page = service('request')->getGet('page') ?? 1;
        $offset = ($page - 1) * $perPage;

        $rows = $builder
            ->orderBy('captured_at', 'DESC')
            ->get($perPage, $offset)
            ->getResultArray();

        $pager = service('pager');
        $pager->makeLinks($page, $perPage, $total);

        return [
            'rows'  => $rows,
            'total' => $total,
            'pager' => $pager,
        ];
    }

    public function getSimConfigById(int $id, int $userId): ?array
    {
        return $this->db->table('tbl_sim_configs')
            ->where('id', $id)
            ->where('owner_id', $userId)
            ->get()
            ->getRowArray();
    }

    public function deleteSimConfig(int $id, int $userId): bool
    {
        return $this->db->table('tbl_sim_configs')
            ->where('id', $id)
            ->where('owner_id', $userId)
            ->delete();
    }

    public function getDistinctDevices(int $userId): array
    {
        return $this->db->table('tbl_sim_configs')
            ->select('device_id')
            ->where('owner_id', $userId)
            ->distinct()
            ->get()
            ->getResultArray();
    }
}
