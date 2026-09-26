<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use App\Models\AnomaliesModel;

class SendNotificationDigest extends BaseCommand
{
    protected $group       = 'Housekeeping';
    protected $name        = 'notifications:digest';
    protected $description = 'Send batch notification digest emails to users and admins.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();
        $startTime = microtime(true);
        $command = 'notifications:digest';
        $output = '';

        $logId = $db->table('cron_execution_logs')->insert([
            'job_id' => 0,
            'command' => $command,
            'started_at' => date('Y-m-d H:i:s'),
            'status' => 'running',
        ]);
        $logId = $db->insertID();

        try {
            $settings = $this->getDigestSettings();
            $digestEnabled = !empty($settings['notification_digest_enabled']);

            if (!$digestEnabled) {
                CLI::write(' Notification digest is disabled. Skipping.', 'yellow');
                $output .= 'Notification digest is disabled. Skipping.' . PHP_EOL;
                $duration = (int)((microtime(true) - $startTime) * 1000);
                $db->table('cron_execution_logs')->where('id', $logId)->update([
                    'finished_at' => date('Y-m-d H:i:s'),
                    'status' => 'success',
                    'output' => trim($output),
                    'duration_ms' => $duration,
                ]);
                return;
            }

            CLI::write(' Collecting pending notifications for digest...', 'yellow');
            $output .= 'Collecting pending notifications for digest...' . PHP_EOL;

            $notifications = $db->table('notification_digest_queue')
                ->where('sent_at IS NULL')
                ->where('created_at <', date('Y-m-d H:i:s'))
                ->orderBy('user_id')
                ->orderBy('created_at', 'ASC')
                ->get()
                ->getResultArray();

            if (empty($notifications)) {
                CLI::write(' No pending notifications to digest.', 'green');
                $output .= 'No pending notifications to digest.' . PHP_EOL;
            } else {
                $grouped = [];
                foreach ($notifications as $notif) {
                    $uid = (int)$notif['user_id'];
                    if (!isset($grouped[$uid])) {
                        $grouped[$uid] = [];
                    }
                    $grouped[$uid][] = $notif;
                }

                $totalSent = 0;
                $totalUsers = count($grouped);

                foreach ($grouped as $userId => $userNotifs) {
                    $userRow = $db->table('auth_identities')
                        ->select('auth_identities.secret AS email, users.username')
                        ->join('users', 'auth_identities.user_id = users.id')
                        ->where('auth_identities.type', 'email_password')
                        ->where('auth_identities.user_id', $userId)
                        ->get()
                        ->getRowArray();

                    if (!$userRow || empty($userRow['email'])) {
                        CLI::error(" No email found for user #{$userId}, skipping digest.");
                        $output .= "No email found for user #{$userId}, skipping digest." . PHP_EOL;
                        continue;
                    }

                    $emailBody = $this->buildDigestHTML($userNotifs);
                    $subject = "Eaves Droid — Notification Digest (" . count($userNotifs) . " items)";

                    helper('email');
                    send_templated_email(
                        $userRow['email'],
                        $subject,
                        'email/admin/maintenance_toggled',
                        [
                            'mode' => 'digest',
                            'initiated_by' => 'System (Notification Digest)',
                            'message' => $emailBody,
                            'securityAction' => 'Notification Digest Sent',
                            'securityDescription' => 'A batch notification digest was sent to the user.',
                            'securityStatus' => 'info',
                            'securityInitiatedBy' => 'System (Notification Digest)',
                            'securityBrowser' => 'CLI (Scheduled Job)',
                            'securityBrowserIp' => 'N/A',
                            'securityExecutedAt' => date('Y-m-d H:i:s'),
                        ]
                    );

                    $sentAt = date('Y-m-d H:i:s');
                    $ids = array_column($userNotifs, 'id');
                    $db->table('notification_digest_queue')
                        ->whereIn('id', $ids)
                        ->update(['sent_at' => $sentAt]);

                    $totalSent += count($userNotifs);
                    CLI::write(" Digest sent to {$userRow['email']} (" . count($userNotifs) . " notifications)", 'green');
                    $output .= "Digest sent to {$userRow['email']} (" . count($userNotifs) . " notifications)" . PHP_EOL;
                }

                CLI::write(" Done. Sent {$totalSent} notifications to {$totalUsers} user(s).", 'green');
                $output .= "Done. Sent {$totalSent} notifications to {$totalUsers} user(s)." . PHP_EOL;
            }

            $duration = (int)((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'success',
                'output' => trim($output),
                'duration_ms' => $duration,
            ]);
        } catch (\Throwable $e) {
            $duration = (int)((microtime(true) - $startTime) * 1000);
            $db->table('cron_execution_logs')->where('id', $logId)->update([
                'finished_at' => date('Y-m-d H:i:s'),
                'status' => 'failed',
                'output' => trim($output) . PHP_EOL . $e->getMessage(),
                'duration_ms' => $duration,
            ]);
        }
    }

    private function buildDigestHTML(array $notifications): string
    {
        $html = '<table style="width:100%;border-collapse:collapse;font-size:13px;">';
        $html .= '<tr style="background:#f1f5f9;"><th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e2e8f0;">Category</th><th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e2e8f0;">Detail</th><th style="padding:8px 12px;text-align:left;border-bottom:1px solid #e2e8f0;">Time</th></tr>';

        foreach ($notifications as $n) {
            $category = esc($n['category'] ?? 'system');
            $title = esc($n['title'] ?? $n['subject'] ?? 'Notification');
            $body = esc(substr($n['body'] ?? $n['message'] ?? '', 0, 200));
            $createdAt = esc($n['created_at'] ?? date('Y-m-d H:i:s'));

            $html .= "<tr><td style=\"padding:6px 12px;border-bottom:1px solid #e2e8f0;\"><span style=\"display:inline-block;padding:2px 8px;background:#dbeafe;color:#2563eb;border-radius:12px;font-size:11px;font-weight:700;\">{$category}</span></td><td style=\"padding:6px 12px;border-bottom:1px solid #e2e8f0;\"><strong>{$title}</strong><br><span style=\"color:#64748b;font-size:12px;\">{$body}</span></td><td style=\"padding:6px 12px;border-bottom:1px solid #e2e8f0;font-size:12px;color:#94a3b8;\">{$createdAt}</td></tr>";
        }

        $html .= '</table>';
        return $html;
    }

    private function getDigestSettings(): array
    {
        $db = \Config\Database::connect();
        $rows = $db->table('settings')->where('class', 'notification_digest')->get()->getResultArray();
        $settings = [];
        foreach ($rows as $r) {
            $settings[$r['key']] = $r['value'];
        }
        return $settings;
    }
}