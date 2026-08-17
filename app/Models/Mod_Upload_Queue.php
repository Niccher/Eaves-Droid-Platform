<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Upload_Queue extends Model
{
    protected $table = 'tbl_upload_queue';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'stored_filename',
        'original_filename',
        'file_category',
        'file_size_bytes',
        'file_record_id',
        'owner_id',
        'device_checksum',
        'device_print_id',
        'token_used',
        'upload_path',
        'upload_source',
        'status',
        'attempts',
        'error_message',
        'processing_started_at',
        'completed_at',
    ];

    protected $useTimestamps = true;
    protected $createdField = 'queued_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    public function enqueue(array $data): ?int
    {
        try {
            $insertData = [
                'stored_filename'  => $data['stored_filename'] ?? '',
                'original_filename' => $data['original_filename'] ?? '',
                'file_category'    => $data['file_category'] ?? 'unknown',
                'file_size_bytes'  => $data['file_size_bytes'] ?? 0,
                'file_record_id'   => $data['file_record_id'] ?? null,
                'owner_id'         => $data['owner_id'] ?? 0,
                'device_checksum'  => $data['device_checksum'] ?? '',
                'device_print_id'  => $data['device_print_id'] ?? '',
                'token_used'       => $data['token_used'] ?? '',
                'upload_path'      => $data['upload_path'] ?? '',
                'upload_source'    => $data['upload_source'] ?? 'auto_sync',
                'status'           => 'pending',
                'attempts'         => 0,
            ];

            $id = $this->insert($insertData);

            if ($id) {
                log_message('info', "[UploadQueue] Enqueued file {$data['stored_filename']} (category: {$data['file_category']}) as queue #{$id}");
                return $id;
            }

            log_message('error', '[UploadQueue] Failed to enqueue file');
            return null;
        } catch (\Exception $e) {
            log_message('error', '[UploadQueue] Exception during enqueue: ' . $e->getMessage());
            return null;
        }
    }

    public function getPendingBatch(int $limit = 5): array
    {
        return $this->where('status', 'pending')
            ->orderBy('queued_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    public function markProcessing(int $id): bool
    {
        return $this->update($id, [
            'status'                => 'processing',
            'processing_started_at' => date('Y-m-d H:i:s'),
            'attempts'              => $this->db->table($this->table)
                ->select('attempts')
                ->where('id', $id)
                ->get()
                ->getRow()->attempts + 1,
        ]);
    }

    public function markCompleted(int $id): bool
    {
        return $this->update($id, [
            'status'       => 'completed',
            'completed_at' => date('Y-m-d H:i:s'),
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
}
