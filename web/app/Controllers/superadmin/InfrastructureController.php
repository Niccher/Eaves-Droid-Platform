<?php

namespace App\Controllers\superadmin;

use App\Models\AnomaliesModel;

class InfrastructureController extends BaseSuperadminController
{
    /**
     * Display the Infrastructure & Container Telemetry Dashboard.
     */
    public function index()
    {
        return $this->renderView('superadmin/infrastructure', [
            'pag' => 'superadmin-infrastructure',
        ]);
    }

    /**
     * Returns real-time telemetry metrics for WebApp, MySQL, and Python ML Backend.
     */
    public function getTelemetry()
    {
        $t0 = microtime(true);
        $db = $this->getDb();

        // -------------------------------------------------------------
        // 1. WEBAPP CONTAINER METRICS
        // -------------------------------------------------------------
        $memCurrent = memory_get_usage(true);
        $memPeak = memory_get_peak_usage(true);
        $memLimitRaw = ini_get('memory_limit');
        $memLimitBytes = $this->parseSizeToBytes($memLimitRaw);

        $memUsedMb = round($memCurrent / (1024 * 1024), 2);
        $memPeakMb = round($memPeak / (1024 * 1024), 2);
        $memLimitMb = $memLimitBytes > 0 ? round($memLimitBytes / (1024 * 1024), 2) : 512;
        $memPercent = $memLimitMb > 0 ? round(($memUsedMb / $memLimitMb) * 100, 1) : 0;

        // System Load Average & CPU Estimation
        $loadAvg = function_exists('sys_getloadavg') ? sys_getloadavg() : [0.0, 0.0, 0.0];
        $cpuCores = 1;
        if (is_readable('/proc/cpuinfo')) {
            $cpuinfo = @file_get_contents('/proc/cpuinfo');
            $cpuCores = max(1, substr_count((string)$cpuinfo, 'processor'));
        } elseif (function_exists('shell_exec')) {
            $nproc = @shell_exec('nproc 2>/dev/null');
            if ($nproc) {
                $cpuCores = max(1, (int)trim($nproc));
            }
        }
        $cpuEstimated = isset($loadAvg[0]) ? round(($loadAvg[0] / $cpuCores) * 100, 1) : 0.0;

        // Disk Storage (/ directory)
        $diskPath = FCPATH;
        $diskTotal = @disk_total_space($diskPath) ?: (40 * 1024 * 1024 * 1024);
        $diskFree = @disk_free_space($diskPath) ?: (25 * 1024 * 1024 * 1024);
        $diskUsed = $diskTotal - $diskFree;
        $diskTotalGb = round($diskTotal / (1024 * 1024 * 1024), 2);
        $diskFreeGb = round($diskFree / (1024 * 1024 * 1024), 2);
        $diskUsedGb = round($diskUsed / (1024 * 1024 * 1024), 2);
        $diskPercent = $diskTotalGb > 0 ? round(($diskUsedGb / $diskTotalGb) * 100, 1) : 0;

        // Uploads & Loot Storage Size
        $uploadsPath = FCPATH . 'uploads';
        $uploadsSizeMb = $this->getDirectorySizeMb($uploadsPath);

        $webappMetrics = [
            'status'           => 'healthy',
            'php_version'      => PHP_VERSION,
            'server_software'  => $_SERVER['SERVER_SOFTWARE'] ?? 'PHP CLI / Built-in',
            'os'               => PHP_OS . ' (' . php_uname('m') . ')',
            'hostname'         => gethostname() ?: 'webapp-container',
            'memory_used_mb'   => $memUsedMb,
            'memory_peak_mb'   => $memPeakMb,
            'memory_limit_mb'  => $memLimitMb,
            'memory_percent'   => $memPercent,
            'load_1m'          => round($loadAvg[0] ?? 0.0, 2),
            'load_5m'          => round($loadAvg[1] ?? 0.0, 2),
            'load_15m'         => round($loadAvg[2] ?? 0.0, 2),
            'cpu_percent'      => min(100, max(0.5, $cpuEstimated)),
            'cpu_cores'        => $cpuCores,
            'disk_total_gb'    => $diskTotalGb,
            'disk_used_gb'     => $diskUsedGb,
            'disk_free_gb'     => $diskFreeGb,
            'disk_percent'     => $diskPercent,
            'uploads_size_mb'  => $uploadsSizeMb,
            'opcache_enabled'  => function_exists('opcache_get_status') && !empty(opcache_get_status(false)),
        ];

        // -------------------------------------------------------------
        // 2. MYSQL DATABASE CONTAINER METRICS
        // -------------------------------------------------------------
        $mysqlMetrics = [
            'status'                   => 'offline',
            'latency_ms'               => 0,
            'version'                  => 'MySQL 8.0',
            'uptime_seconds'           => 0,
            'threads_connected'        => 1,
            'max_used_connections'     => 1,
            'max_connections'          => 151,
            'buffer_pool_used_mb'      => 0,
            'buffer_pool_total_mb'     => 128,
            'buffer_pool_percent'      => 0,
            'total_size_mb'            => 0,
            'questions_per_sec'        => 0,
            'tables_count'             => 10,
            'core_tables'              => [],
            'error'                    => null,
        ];

        try {
            $tDb0 = microtime(true);
            $db->query('SELECT 1');
            $dbLatency = round((microtime(true) - $tDb0) * 1000, 2);

            $mysqlMetrics['status'] = 'healthy';
            $mysqlMetrics['latency_ms'] = $dbLatency;

            // MySQL Version & Uptime
            try {
                $verRow = $db->query('SELECT VERSION() AS v')->getRow();
                if ($verRow && !empty($verRow->v)) {
                    $mysqlMetrics['version'] = $verRow->v;
                }
            } catch (\Throwable $ve) {}

            // Status Variables
            try {
                $statusRows = $db->query("SHOW GLOBAL STATUS WHERE Variable_name IN (
                    'Threads_connected', 'Max_used_connections', 'Uptime', 'Questions',
                    'Innodb_buffer_pool_bytes_data', 'Innodb_buffer_pool_size'
                )")->getResultArray();

                $statusMap = [];
                foreach ($statusRows as $sr) {
                    $statusMap[$sr['Variable_name']] = $sr['Value'];
                }

                // Variables
                $varRows = $db->query("SHOW VARIABLES WHERE Variable_name IN ('max_connections', 'innodb_buffer_pool_size')")->getResultArray();
                $varMap = [];
                foreach ($varRows as $vr) {
                    $varMap[$vr['Variable_name']] = $vr['Value'];
                }

                $mysqlMetrics['uptime_seconds'] = (int)($statusMap['Uptime'] ?? 0);
                $mysqlMetrics['threads_connected'] = (int)($statusMap['Threads_connected'] ?? 1);
                $mysqlMetrics['max_used_connections'] = (int)($statusMap['Max_used_connections'] ?? 1);
                $mysqlMetrics['max_connections'] = (int)($varMap['max_connections'] ?? 151);

                $bufDataBytes = (float)($statusMap['Innodb_buffer_pool_bytes_data'] ?? 0);
                $bufTotalBytes = (float)($varMap['innodb_buffer_pool_size'] ?? 134217728);
                $mysqlMetrics['buffer_pool_used_mb'] = round($bufDataBytes / (1024 * 1024), 2);
                $mysqlMetrics['buffer_pool_total_mb'] = round($bufTotalBytes / (1024 * 1024), 2);
                $mysqlMetrics['buffer_pool_percent'] = $bufTotalBytes > 0 ? round(($bufDataBytes / $bufTotalBytes) * 100, 1) : 0;

                // Questions per sec
                $uptime = max(1, $mysqlMetrics['uptime_seconds']);
                $questions = (int)($statusMap['Questions'] ?? 0);
                $mysqlMetrics['questions_per_sec'] = round($questions / $uptime, 1);
            } catch (\Throwable $se) {}

            // Database disk footprint from information_schema
            try {
                $dbName = $db->getDatabase();
                $sizeRow = $db->query("SELECT
                    ROUND(SUM(data_length + index_length) / (1024 * 1024), 2) AS total_mb,
                    COUNT(*) AS table_count
                    FROM information_schema.tables
                    WHERE table_schema = ?", [$dbName])->getRow();

                if ($sizeRow) {
                    $mysqlMetrics['total_size_mb'] = (float)($sizeRow->total_mb ?? 0);
                    $mysqlMetrics['tables_count'] = (int)($sizeRow->table_count ?? 10);
                }
            } catch (\Throwable $sze) {}

            // Core Forensic Tables Info
            $coreTableNames = [
                'tbl_extracted_sms'             => 'SMS Messages',
                'tbl_extracted_contacts'        => 'Contacts',
                'tbl_extracted_call_logs'       => 'Call Logs',
                'tbl_extracted_locations'       => 'Locations',
                'tbl_extracted_installed_apps'  => 'Installed Apps',
                'tbl_extracted_device_files'    => 'Device Files',
                'tbl_system_app_usage'          => 'App Usage / Activity',
                'tbl_device_profiles'           => 'Device Profiles',
                'ml_jobs'                       => 'ML Analysis Jobs',
                'ml_results'                    => 'ML Anomaly Findings',
            ];

            $coreTablesData = [];
            try {
                $tableStats = $db->query("SELECT table_name, table_rows,
                    ROUND((data_length + index_length) / 1024, 2) AS size_kb
                    FROM information_schema.tables
                    WHERE table_schema = ? AND table_name IN ('" . implode("','", array_keys($coreTableNames)) . "')", [$dbName])->getResultArray();

                $statsMap = [];
                foreach ($tableStats as $ts) {
                    $statsMap[$ts['table_name']] = $ts;
                }

                foreach ($coreTableNames as $tbl => $label) {
                    $st = $statsMap[$tbl] ?? null;
                    $coreTablesData[] = [
                        'table'    => $tbl,
                        'label'    => $label,
                        'exists'   => $st !== null,
                        'rows'     => (int)($st['table_rows'] ?? 0),
                        'size_kb'  => (float)($st['size_kb'] ?? 0),
                    ];
                }
            } catch (\Throwable $te) {
                foreach ($coreTableNames as $tbl => $label) {
                    $coreTablesData[] = [
                        'table'    => $tbl,
                        'label'    => $label,
                        'exists'   => true,
                        'rows'     => 0,
                        'size_kb'  => 0,
                    ];
                }
            }
            $mysqlMetrics['core_tables'] = $coreTablesData;
        } catch (\Throwable $e) {
            $mysqlMetrics['status'] = 'offline';
            $mysqlMetrics['error'] = $e->getMessage();
        }

        // -------------------------------------------------------------
        // 3. PYTHON ML BACKEND CONTAINER METRICS
        // -------------------------------------------------------------
        $anomaliesModel = new AnomaliesModel();
        $pyResult = $anomaliesModel->testPythonConnection(null, 4);

        $pythonMetrics = [
            'status'                   => $pyResult['success'] ? 'healthy' : 'offline',
            'version'                  => $pyResult['version'] ?? '2.5.0',
            'latency_ms'               => $pyResult['latency_ms'] ?? 0,
            'tested_url'               => $pyResult['tested_url'] ?? '',
            'memory_used_mb'           => (float)($pyResult['memory']['used'] ?? 0),
            'memory_total_mb'          => (float)($pyResult['memory']['total'] ?? 0),
            'cpu_percent'              => (float)($pyResult['cpu_percent'] ?? 0.0),
            'models_count'             => $pyResult['models_count'] ?? count($pyResult['models'] ?? []),
            'models'                   => $pyResult['models'] ?? [],
            'database_status'          => $pyResult['database'] ?? 'unknown',
            'database_latency_ms'      => $pyResult['database_latency_ms'] ?? 0.0,
            'database_tables_verified' => $pyResult['database_tables_verified'] ?? 0,
            'database_total_tables'    => $pyResult['database_total_tables'] ?? 10,
            'cache_entries'            => $pyResult['cache'] ?? 0,
            'uptime_seconds'           => $pyResult['uptime'] ?? 0,
            'cuda_available'           => $pyResult['cuda'] ?? false,
            'modules'                  => $pyResult['modules'] ?? [],
            'message'                  => $pyResult['message'] ?? '',
        ];

        // -------------------------------------------------------------
        // 4. REDIS METRICS
        // -------------------------------------------------------------
        $redisMetrics = [
            'status'         => 'offline',
            'memory_used_mb' => 0.0,
            'clients'        => 0,
            'queue_length'   => 0
        ];
        try {
            $redis = ConfigServices::redis();
            if ($redis->ping()) {
                $redisInfo = $redis->info();
                $redisMetrics['status'] = 'healthy';
                // Parse used_memory_human or used_memory
                if (isset($redisInfo['used_memory'])) {
                    $redisMetrics['memory_used_mb'] = round($redisInfo['used_memory'] / (1024 * 1024), 2);
                }
                $redisMetrics['clients'] = $redisInfo['connected_clients'] ?? 0;
                
                // Try to get length of main queues (fallback to 0)
                $queueLen = 0;
                try {
                    $queueLen += $redis->llen('upload_queue') ?: 0;
                    $queueLen += $redis->llen('ml_queue') ?: 0;
                } catch (Throwable $e) {}
                $redisMetrics['queue_length'] = $queueLen;
            }
        } catch (Throwable $e) {
            $redisMetrics['error'] = $e->getMessage();
        }

        // -------------------------------------------------------------
        // 5. CRON DAEMON METRICS
        // -------------------------------------------------------------
        $cronMetrics = [
            'status'          => 'offline',
            'last_run'        => 'Never',
            'seconds_since'   => -1
        ];
        try {
            $cronFile = WRITEPATH . 'logs/cron_heartbeat.txt';
            if (file_exists($cronFile)) {
                $mtime = filemtime($cronFile);
                $diff = time() - $mtime;
                $cronMetrics['seconds_since'] = $diff;
                $cronMetrics['last_run'] = date('Y-m-d H:i:s', $mtime);
                $cronMetrics['status'] = ($diff <= 120) ? 'healthy' : 'delayed';
            }
        } catch (Throwable $e) {}

        // -------------------------------------------------------------
        // 6. STORAGE METRICS
        // -------------------------------------------------------------
        $storageMetrics = [
            'status'         => 'healthy',
            'uploads_mb'     => 0.0,
            'total_space_mb' => 0.0,
            'free_space_mb'  => 0.0
        ];
        try {
            $storageMetrics['uploads_mb'] = $this->getDirectorySizeMb(WRITEPATH . 'uploads');
            $storageMetrics['total_space_mb'] = round(disk_total_space(WRITEPATH) / (1024 * 1024), 2);
            $storageMetrics['free_space_mb'] = round(disk_free_space(WRITEPATH) / (1024 * 1024), 2);
            
            $usedPct = $storageMetrics['total_space_mb'] > 0 
                ? (($storageMetrics['total_space_mb'] - $storageMetrics['free_space_mb']) / $storageMetrics['total_space_mb']) * 100 
                : 0;
                
            if ($usedPct > 90) {
                $storageMetrics['status'] = 'critical';
            } elseif ($usedPct > 75) {
                $storageMetrics['status'] = 'warning';
            }
        } catch (Throwable $e) {}

        // Overall Collection Summary
        $collectionDurationMs = round((microtime(true) - $t0) * 1000, 1);

        return $this->response->setJSON([
            'success'                => true,
            'timestamp'              => date('Y-m-d H:i:s'),
            'collection_duration_ms' => $collectionDurationMs,
            'webapp'                 => $webappMetrics,
            'mysql'                  => $mysqlMetrics,
            'python'                 => $pythonMetrics,
            'redis'                  => $redisMetrics,
            'cron'                   => $cronMetrics,
            'storage'                => $storageMetrics,
        ]);
    }

    /**
     * Executes an end-to-end tri-tier latency waterfall benchmark.
     */
    public function runBenchmark()
    {
        $t0 = microtime(true);
        $db = $this->getDb();

        // Hop 1: WebApp Compute & PHP Execution
        $tWeb0 = microtime(true);
        $dummy = 0;
        for ($i = 0; $i < 10000; $i++) {
            $dummy += $i;
        }
        $webComputeMs = round((microtime(true) - $tWeb0) * 1000, 2);

        // Hop 2: WebApp -> MySQL
        $tDb0 = microtime(true);
        $db->query('SELECT 1');
        $mysqlPingMs = round((microtime(true) - $tDb0) * 1000, 2);

        // Hop 3: WebApp -> Python ML
        $anomaliesModel = new AnomaliesModel();
        $pyResult = $anomaliesModel->testPythonConnection(null, 5);
        $pythonPingMs = $pyResult['latency_ms'] ?? 0.0;

        // Total
        $totalMs = round((microtime(true) - $t0) * 1000, 2);

        return $this->response->setJSON([
            'success'        => true,
            'total_ms'       => $totalMs,
            'hops'           => [
                [
                    'name'        => 'PHP WebApp Engine',
                    'latency_ms'  => $webComputeMs,
                    'status'      => 'optimal',
                    'description' => 'Local in-process compute overhead',
                ],
                [
                    'name'        => 'MySQL Container Link',
                    'latency_ms'  => $mysqlPingMs,
                    'status'      => $mysqlPingMs < 20 ? 'optimal' : ($mysqlPingMs < 100 ? 'normal' : 'slow'),
                    'description' => 'Database connection & query execution',
                ],
                [
                    'name'        => 'Python ML Microservice',
                    'latency_ms'  => $pythonPingMs,
                    'status'      => ($pyResult['success'] ?? false) ? ($pythonPingMs < 100 ? 'optimal' : 'normal') : 'failed',
                    'description' => ($pyResult['success'] ?? false) ? 'FastAPI container response' : 'Microservice offline (Failover ready)',
                ],
            ],
            'timestamp'      => date('H:i:s'),
        ]);
    }

    /**
     * Parse human-readable memory string (e.g., '512M', '1G') to bytes.
     */
    private function parseSizeToBytes(?string $sizeStr): int
    {
        if (!$sizeStr || $sizeStr === '-1') {
            return -1;
        }
        $unit = strtoupper(substr($sizeStr, -1));
        $val = (int)substr($sizeStr, 0, -1);
        return match ($unit) {
            'G'     => $val * 1024 * 1024 * 1024,
            'M'     => $val * 1024 * 1024,
            'K'     => $val * 1024,
            default => (int)$sizeStr,
        };
    }

    /**
     * Compute folder size in Megabytes.
     */
    private function getDirectorySizeMb(string $path): float
    {
        if (!is_dir($path)) {
            return 0.0;
        }
        $totalBytes = 0;
        try {
            $files = new \RecursiveIteratorIterator(
                new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS)
            );
            foreach ($files as $file) {
                $totalBytes += $file->getSize();
            }
        } catch (\Throwable $e) {
            return 0.0;
        }
        return round($totalBytes / (1024 * 1024), 2);
    }
}
