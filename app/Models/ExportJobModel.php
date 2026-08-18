<?php

namespace App\Models;

use CodeIgniter\Model;

class ExportJobModel extends Model
{
    protected $table = 'export_jobs';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'job_type',
        'requester_id',
        'target_user_id',
        'params',
        'status',
        'result_path',
        'result_size',
        'error_message',
        'attempts',
        'queued_at',
        'processing_started_at',
        'completed_at',
    ];

    protected $useTimestamps = false;

    public function enqueue(array $data): ?int
    {
        try {
            $insert = [
                'job_type'        => $data['job_type'] ?? 'forensics',
                'requester_id'    => $data['requester_id'] ?? null,
                'target_user_id'  => $data['target_user_id'] ?? null,
                'params'          => json_encode($data['params'] ?? []),
                'status'          => 'queued',
                'attempts'        => 0,
                'queued_at'       => date('Y-m-d H:i:s'),
            ];
            $id = $this->insert($insert);
            if ($id) {
                log_message('info', "[ExportJob] Enqueued job #{$id} type={$insert['job_type']} target_user={$insert['target_user_id']}");
                return $id;
            }
            log_message('error', '[ExportJob] Failed to enqueue job');
            return null;
        } catch (\Exception $e) {
            log_message('error', '[ExportJob] Exception during enqueue: ' . $e->getMessage());
            return null;
        }
    }

    public function getQueuedBatch(int $limit = 5): array
    {
        return $this->where('status', 'queued')
            ->orderBy('queued_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    public function markProcessing(int $id): bool
    {
        $current = $this->db->table($this->table)->select('attempts')
            ->where('id', $id)->get()->getRow();
        return $this->update($id, [
            'status'                => 'processing',
            'attempts'              => ((int) ($current->attempts ?? 0)) + 1,
            'processing_started_at' => date('Y-m-d H:i:s'),
        ]);
    }

    public function markCompleted(int $id, string $resultPath, int $resultSize): bool
    {
        return $this->update($id, [
            'status'        => 'done',
            'result_path'   => $resultPath,
            'result_size'   => $resultSize,
            'completed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function markFailed(int $id, string $error): bool
    {
        return $this->update($id, [
            'status'        => 'failed',
            'error_message' => substr($error, 0, 1000),
            'completed_at'  => date('Y-m-d H:i:s'),
        ]);
    }

    public function recentFor(int $requesterId, int $limit = 10): array
    {
        return $this->where('requester_id', $requesterId)
            ->orderBy('id', 'DESC')
            ->limit($limit)
            ->findAll();
    }
}
