<?php

namespace App\Libraries\AnomalyDetectors;

class FileDetectors
{
    /**
     * FilesController – File Creation Spike Detector
     *
     * Flags days where file creations are 3× the 30-day rolling average.
     *
     * @param  array $fileRows  [{file_name, created_at, path}, ...]
     * @return array
     */
    public function detectFilesSpike(array $fileRows): array
    {
        if (empty($fileRows)) {
            return [];
        }

        $daily = [];
        foreach ($fileRows as $row) {
            $day         = date('Y-m-d', strtotime($row['created_at'] ?? 'today'));
            $daily[$day] = ($daily[$day] ?? 0) + 1;
        }

        $counts = array_values($daily);
        $mean   = array_sum($counts) / max(1, count($counts));
        $thresh = $mean * 3;

        $findings = [];
        foreach ($daily as $day => $cnt) {
            if ($cnt > $thresh) {
                $findings[] = [
                    'category'  => 'Files',
                    'icon'      => 'fas fa-folder-open',
                    'anomaly'   => "{$cnt} files created on {$day} (" . round($cnt / max(1, $mean), 1) . '× the daily average)',
                    'severity'  => $cnt > $thresh * 2 ? 'High' : 'Medium',
                    'algorithm' => 'File Creation Spike Detector',
                    'timestamp' => $day . ' 00:00:00',
                    'engine_note' => 'Daily threshold: ' . round($thresh) . ' files (mean: ' . round($mean, 1) . '/day)',
                ];
            }
        }

        return $findings;
    }

    /**
     * FilesController – Suspicious File Metadata Scanner
     *
     * Flags encryption-related extensions, double file extensions, and binaries/scripts inside user data directories.
     */
    public function detectFilesEntropy(array $fileRows): array
    {
        if (empty($fileRows)) {
            return [];
        }

        $findings = [];
        $suspiciousExtensions = ['enc', 'crypt', 'aes', 'locked', 'key', 'ecc'];
        $dangerousExtensions = ['sh', 'bin', 'exe', 'apk', 'py', 'php', 'js'];

        foreach ($fileRows as $row) {
            $path = $row['path'] ?? '';
            $filename = strtolower(basename($path));
            
            if (empty($filename)) {
                continue;
            }

            $parts = explode('.', $filename);
            $ext = end($parts);
            
            $reasons = [];
            if (in_array($ext, $suspiciousExtensions)) {
                $reasons[] = 'encryption-related extension (.' . esc($ext) . ') - possible ransomware footprint';
            }
            if (count($parts) > 2) {
                $lastTwo = array_slice($parts, -2);
                if (in_array($lastTwo[0], ['jpg', 'png', 'txt', 'pdf', 'doc', 'docx', 'xls', 'xlsx']) && in_array($lastTwo[1], $dangerousExtensions)) {
                    $reasons[] = 'suspicious double extension (.' . esc(implode('.', $lastTwo)) . ')';
                }
            }
            if (in_array($ext, $dangerousExtensions)) {
                if (strpos(strtolower($path), '/sdcard/download') !== false || strpos(strtolower($path), 'android/data') !== false) {
                    $reasons[] = 'executable or script in sensitive user data folder';
                }
            }

            if (!empty($reasons)) {
                $findings[] = [
                    'category'  => 'Files',
                    'icon'      => 'fas fa-folder-open',
                    'anomaly'   => 'Suspicious file metadata found in path: "' . esc(substr($path, 0, 100)) . '"',
                    'severity'  => 'Medium',
                    'algorithm' => 'Suspicious File Metadata Scanner',
                    'timestamp' => $row['created_at'] ?? date('Y-m-d H:i:s'),
                    'engine_note'=> 'PHP heuristic - flagged: ' . implode(', ', $reasons),
                ];
            }
        }

        return array_slice($findings, 0, 5);
    }
}
