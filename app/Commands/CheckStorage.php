<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class CheckStorage extends BaseCommand
{
    protected $group       = 'Storage';
    protected $name        = 'storage:check';
    protected $description = 'Check disk usage and send alerts if thresholds exceeded.';

    public function run(array $params)
    {
        $settings = $this->getStorageSettings();

        CLI::write(' Checking disk usage...', 'yellow');

        $disks = $this->getDiskUsage();
        $alerts = [];

        foreach ($disks as $disk) {
            $pct = $disk['usage_percent'];
            $level = 'ok';

            if ($pct >= ($settings['threshold_critical'] ?? 90)) {
                $level = 'CRITICAL';
            } elseif ($pct >= ($settings['threshold_warning'] ?? 80)) {
                $level = 'WARNING';
            }

            $color = $level === 'CRITICAL' ? 'red' : ($level === 'WARNING' ? 'yellow' : 'green');
            CLI::write(" {$disk['mount']}: {$pct}% used ({$disk['used_formatted']} / {$disk['total_formatted']})", $color);

            if ($level !== 'ok' && ($settings['notify_admins'] ?? false)) {
                $alerts[] = ['disk' => $disk, 'level' => $level];
            }
        }

        if (!empty($alerts)) {
            foreach ($alerts as $alert) {
                $this->sendAlertEmail($alert['disk'], $alert['level'], $settings);
            }
            CLI::write(' Alert emails sent to admins.', 'yellow');
        } else {
            CLI::write(' No threshold breaches.', 'green');
        }
    }

    private function getStorageSettings(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('settings')->where('class', 'storage')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }

    private function getDiskUsage(): array
    {
        $disks = [];
        $root = disk_total_space('/');
        $free = disk_free_space('/');
        $used = $root - $free;
        $pct = $root > 0 ? round(($used / $root) * 100, 1) : 0;

        $disks[] = [
            'mount' => '/',
            'total' => $root,
            'used' => $used,
            'free' => $free,
            'usage_percent' => $pct,
            'total_formatted' => $this->formatBytes($root),
            'used_formatted' => $this->formatBytes($used),
            'free_formatted' => $this->formatBytes($free),
        ];

        return $disks;
    }

    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
        if ($bytes == 0) return '0 B';
        $pow = floor(log($bytes) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= (1024 ** $pow);
        return round($bytes, $precision) . ' ' . $units[$pow];
    }

    private function sendAlertEmail(array $disk, string $level, array $settings): void
    {
        $db = \Config\Database::connect();
        $admins = $db->table('auth_identities')
            ->select('auth_identities.secret AS email')
            ->join('users', 'auth_identities.user_id = users.id')
            ->join('auth_groups_users', 'auth_groups_users.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('auth_groups_users.group', 'admin')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();

        $subject = "Eaves Droid — Storage {$level}: {$disk['usage_percent']}% Used";
        $pct = $disk['usage_percent'];

        foreach ($admins as $admin) {
            helper('email');
            send_templated_email(
                $admin['email'],
                $subject,
                'email/admin/storage_threshold',
                [
                    'disk' => $disk,
                    'level' => $level,
                    'threshold_warning' => $settings['threshold_warning'] ?? 80,
                    'threshold_critical' => $settings['threshold_critical'] ?? 90,
                ]
            );
        }
    }
}