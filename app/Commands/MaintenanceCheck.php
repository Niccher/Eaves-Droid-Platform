<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;
use CodeIgniter\Database\BaseConnection;

class MaintenanceCheck extends BaseCommand
{
    protected $group       = 'System';
    protected $name        = 'maintenance:check';
    protected $description = 'Check scheduled maintenance windows and activate/deactivate as needed.';

    public function run(array $params)
    {
        $db = \Config\Database::connect();

        $settings = [];
        foreach ($db->table('settings')->where('class', 'app')->get()->getResultArray() as $r) {
            $settings[$r['key']] = $r['value'];
        }

        $currentMode = $settings['maintenance_mode'] ?? '0';
        $type = $settings['maintenance_type'] ?? 'now_until_unknown';
        $startTime = $settings['maintenance_start'] ?? null;
        $endTime = $settings['maintenance_end'] ?? null;
        $now = date('Y-m-d H:i:s');

        $activated = false;
        $deactivated = false;

        if ($type === 'scheduled') {
            if ($currentMode === '0' && $startTime && $now >= $startTime) {
                $this->setMaintenanceMode($db, '1');
                $this->notifyUsers($db, 'enabled', $startTime, $endTime, 'Scheduled maintenance is in progress.');
                CLI::write(' Maintenance activated (scheduled start reached).', 'green');
                $activated = true;
            }

            if ($currentMode === '1' && $endTime && $now >= $endTime) {
                $this->setMaintenanceMode($db, '0');
                $this->notifyUsers($db, 'disabled', $startTime, $endTime, 'Maintenance has been completed. All services are now operational.');
                CLI::write(' Maintenance deactivated (scheduled end reached).', 'green');
                $deactivated = true;
            }
        } elseif ($type === 'now_until') {
            if ($currentMode === '1' && $endTime && $now >= $endTime) {
                $this->setMaintenanceMode($db, '0');
                $this->notifyUsers($db, 'disabled', $startTime, $endTime, 'Maintenance has been completed. All services are now operational.');
                CLI::write(' Maintenance deactivated (end time reached).', 'green');
                $deactivated = true;
            }
        }

        if (!$activated && !$deactivated) {
            CLI::write(' No maintenance state change needed.', 'yellow');
        }
    }

    private function setMaintenanceMode(BaseConnection $db, string $mode): void
    {
        $existing = $db->table('settings')
            ->where('class', 'app')
            ->where('key', 'maintenance_mode')
            ->get()
            ->getRow();

        if ($existing) {
            $db->table('settings')
                ->where('id', $existing->id)
                ->update(['value' => $mode, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $db->table('settings')->insert([
                'class' => 'app',
                'key' => 'maintenance_mode',
                'value' => $mode,
                'type' => 'string',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    private function notifyUsers(BaseConnection $db, string $mode, ?string $windowStart, ?string $windowEnd, string $message): void
    {
        helper('email');

        $users = $db->table('auth_identities')
            ->select('auth_identities.secret AS email, users.id, users.username')
            ->join('users', 'auth_identities.user_id = users.id')
            ->where('auth_identities.type', 'email_password')
            ->where('users.active', 1)
            ->get()
            ->getResultArray();

        $subject = $mode === 'enabled'
            ? 'Eaves Droid — Maintenance Mode Enabled'
            : 'Eaves Droid — Maintenance Mode Ended';

        foreach ($users as $user) {
            if (empty($user['email'])) continue;

            $profile = $db->table('user_profiles')
                ->select('email_notifications')
                ->where('user_id', $user['id'])
                ->get()
                ->getRowArray();
            if ($profile && isset($profile['email_notifications']) && !$profile['email_notifications']) continue;

            send_templated_email(
                $user['email'],
                $subject,
                'email/admin/maintenance_toggled',
                [
                    'mode' => $mode,
                    'initiated_by' => 'System (Scheduled)',
                    'window_start' => $windowStart,
                    'window_end' => $windowEnd,
                    'message' => $message,
                    'username' => $user['username'],
                    'securityAction' => 'Maintenance Mode ' . ucfirst($mode),
                    'securityDescription' => $mode === 'enabled'
                        ? 'The platform is entering maintenance mode. Services may be temporarily unavailable.'
                        : 'Maintenance mode has ended. All services are now fully operational.',
                    'securityStatus' => $mode === 'enabled' ? 'warning' : 'success',
                    'securityInitiatedBy' => 'System (Scheduled)',
                    'securityBrowser' => 'CLI (Scheduled Job)',
                    'securityBrowserIp' => 'N/A',
                    'securityExecutedAt' => date('Y-m-d H:i:s'),
                ]
            );
        }

        CLI::write(' Sent maintenance ' . $mode . ' notification to ' . count($users) . ' users.', 'blue');
    }
}
