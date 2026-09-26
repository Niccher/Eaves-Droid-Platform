<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use CodeIgniter\Database\BaseConnection;

class AppPermissionParser
{
    protected BaseConnection $db;

    public function __construct(BaseConnection $db)
    {
        $this->db = $db;
    }

    /**
     * Normalize a parser payload into a decoded associative array.
     */
    protected function payloadToArray(string|array $payload, string $file_name): ?array
    {
        if (is_array($payload)) {
            return $payload;
        }

        $cryptModel = new CryptModel();
        $raw = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
        if ($raw === false) {
            log_message('error', '[payloadToArray] Cannot read file: ' . $file_name);
            return null;
        }

        $decoded = $cryptModel->decrypt_file($raw);
        if ($decoded === false) {
            log_message('error', '[payloadToArray] Decryption failed: ' . $file_name);
            return null;
        }

        $json = json_decode($decoded, true);
        if ($json === null) {
            log_message('error', '[payloadToArray] JSON decode failed: ' . $file_name);
            return null;
        }

        return $json;
    }

    /**
     * Resolve whether a notification was shown on screen (heads-up / lock screen).
     *
     * @param array<string, mixed> $notif
     */
    private static function resolveScreenNotificationFlag(array $notif): int
    {
        if (!empty($notif['is_screen_notification'])) {
            return 1;
        }
        foreach (['screen', 'on_screen', 'heads_up', 'headsUp', 'lock_screen', 'lockScreen'] as $key) {
            if (!empty($notif[$key])) {
                return 1;
            }
        }

        return 0;
    }

    /**
     * AppUsageExtractor
     * File prefix: app_usage_TIMESTAMP.enc
     */
    public function parse_app_usage(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            // Flexible detection of the data array
            $usageRows = null;
            if (isset($json['usage_list']) && is_array($json['usage_list'])) {
                $usageRows = $json['usage_list'];
            } elseif (isset($json['app_usage']) && is_array($json['app_usage'])) {
                $usageRows = $json['app_usage'];
            } elseif (isset($json['usage']) && is_array($json['usage'])) {
                $usageRows = $json['usage'];
            } elseif (is_array($json) && (isset($json[0]) || empty($json))) {
                $usageRows = $json;
            }

            if ($usageRows === null) {
                log_message('error', '[ParseAdvancedModel::parse_app_usage] Could not find usage data array in file: ' . $file_name);
                return false;
            }

            // Composite injects both 'timestamp' and 'extracted_at' from the same
            // extraction run; the extractor's own extracted_at (device ms) wins when present.
            // Server clock is only a last-resort fallback for legacy files.
            $extracted_at = $json['extracted_at'] ?? $json['timestamp'] ?? time() * 1000;
            $seenPackages = [];
            $inserted = 0;
            $skipped = 0;

            foreach ($usageRows as $app) {
                // Flexible package key detection
                $packageName = $app['package_name'] ?? $app['package'] ?? $app['name'] ?? null;
                if (!$packageName) {
                    $skipped++;
                    continue;
                }

                // Batch duplicate check
                if (in_array($packageName, $seenPackages)) {
                    $skipped++;
                    continue;
                }

                // Upsert logic: skip if identical snapshot already stored
                $exists = $this->db->table('tbl_system_app_usage')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('package_name', $packageName)
                    ->where('extracted_at', $extracted_at)
                    ->countAllResults() > 0;

                if ($exists) {
                    $skipped++;
                    continue;
                }

                $seenPackages[] = $packageName;

                $appData = [
                    'owner_id'              => $owner_id,
                    'device_id'             => $device_id,
                    'package_name'          => $packageName,
                    'app_name'              => $app['app_name']             ?? null,
                    'foreground_time_ms'    => $app['foreground_time_ms']   ?? $app['foreground_ms'] ?? $app['totalTimeInForeground'] ?? $app['total_time_in_foreground'] ?? $app['app_usage_time'] ?? $app['usage_time'] ?? $app['totalTimeVisible'] ?? $app['total_time_visible'] ?? $app['totalTime'] ?? $app['total_time'] ?? 0,
                    'foreground_time_hours' => $app['foreground_time_hours'] ?? $app['foreground_hours'] ?? 0,
                    'last_time_used'        => $app['last_time_used']       ?? $app['last_used'] ?? null,
                    'is_system_app'         => isset($app['is_system_app']) ? ($app['is_system_app'] ? 1 : 0) : 0,
                    'background_time_ms'    => $app['background_time_ms']    ?? 0,
                    'interactive_time_ms'   => $app['interactive_time_ms']   ?? $app['foreground_time_ms'] ?? null,
                    'screen_on_time_ms'     => $app['screen_on_time_ms']     ?? null,
                    'keyguard_shown_time_ms'=> $app['keyguard_shown_time_ms']?? 0,
                    'session_count'         => $app['session_count']         ?? 0,
                    'session_durations_json' => json_encode($app['session_durations'] ?? []),
                    'first_launch_of_day'  => $app['first_launch_of_day']   ?? null,
                    'last_launch_of_day'   => $app['last_launch_of_day']    ?? null,
                    'longest_session_ms'   => $app['longest_session_ms']    ?? null,
                    'shortest_session_ms'  => $app['shortest_session_ms']   ?? null,
                    'avg_session_ms'       => $app['avg_session_ms']        ?? null,
                    'distinct_days_used'   => $app['distinct_days_used']    ?? null,
                    'usage_by_hour_json'   => json_encode($app['usage_by_hour'] ?? []),
                    'usage_by_dow_json'    => json_encode($app['usage_by_dow'] ?? []),
                    'notification_seen_count' => $app['notification_seen_count'] ?? null,
                    'notification_clicked_count' => $app['notification_clicked_count'] ?? null,
                    'app_standby_bucket'   => $app['app_standby_bucket']    ?? null,
                    'app_standby_reason'   => $app['app_standby_reason']    ?? null,
                    'times_opened'         => $app['times_opened']          ?? null,
                    'time_taken_formatted' => $app['time_taken_formatted']  ?? null,
                    'extracted_at'          => $extracted_at,
                    'created_at'            => $dated,
                    'updated_at'            => $dated,
                ];

                $this->db->table('tbl_system_app_usage')->insert($appData);
                $usageId = $this->db->insertID();
                $inserted++;

                // Insert session events from sessions_list or session_durations
                if (!empty($app['session_durations']) && is_array($app['session_durations']) && $usageId > 0) {
                    $sessBatch = [];
                    foreach ($app['session_durations'] as $sd) {
                        $sessBatch[] = [
                            'app_usage_id' => $usageId,
                            'owner_id'     => $owner_id,
                            'event_type'   => 'session_duration',
                            'timestamp'    => null,
                            'session_start_ms' => $sd['start'] ?? null,
                            'session_end_ms'   => $sd['end'] ?? null,
                            'session_duration_ms' => $sd['duration_ms'] ?? null,
                            'created_at'   => $dated,
                        ];
                    }
                    if (!empty($sessBatch)) {
                        $this->db->table('tbl_system_app_usage_sessions')->insertBatch($sessBatch);
                    }
                }
            }

            log_message('info', '[parse_app_usage] Processed ' . count($usageRows) . " apps from $file_name (Inserted: $inserted, Skipped: $skipped)");
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_app_usage] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * NotificationExtractor
     * File prefix: notifications_TIMESTAMP.enc
     * NOTE: The payload is a raw JSON *array*, not an object.
     */
    public function parse_notifications(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $jsonParsed = $this->payloadToArray($payload, $file_name);
            if ($jsonParsed === null) return false;
            $extractedAt = $jsonParsed['extracted_at'] ?? $jsonParsed['timestamp'] ?? null;
            $notifications = null;
            
            if (isset($jsonParsed['notifications_list']) && is_array($jsonParsed['notifications_list'])) {
                $notifications = $jsonParsed['notifications_list'];
            } else {
                $notifications = $jsonParsed;
            }

            if ($notifications === null || !is_array($notifications)) {
                log_message('error', '[ParseAdvancedModel::parse_notifications] Invalid JSON (expected array or notifications_list): ' . $file_name);
                return false;
            }

            $batchData = [];
            $seenKeys  = [];
            foreach ($notifications as $notif) {
                if (!is_array($notif)) {
                    continue;
                }
                $notifId    = $notif['id']        ?? null;
                $notifTs    = $notif['timestamp'] ?? null;
                $action     = $notif['action']    ?? null;

                // Unique key for notifications: ID + Timestamp + Action
                $key = "{$notifId}|{$notifTs}|{$action}";
                if (in_array($key, $seenKeys)) {
                    continue;
                }

                // Database duplicate check
                $exists = $this->db->table('tbl_extracted_notifications')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('notification_id', $notifId)
                    ->where('notification_timestamp', $notifTs)
                    ->where('action', $action)
                    ->countAllResults() > 0;

                if (!$exists) {
                    $seenKeys[] = $key;
                    $batchData[] = [
                        'owner_id'               => $owner_id,
                        'device_id'              => $device_id,
                        'notification_id'        => $notifId,
                        'package_name'           => $notif['package']
                            ?? $notif['packageName']
                            ?? $notif['package_name']
                            ?? null,
                        'app_name'               => $notif['app_name'] ?? null,
                        'title'                  => $notif['raw_title'] ?? $notif['title']    ?? null,
                        'text'                   => $notif['raw_body']  ?? $notif['text']     ?? $notif['body'] ?? null,
                        'sender'                 => $notif['sender'] ?? $notif['sender_name'] ?? $notif['from'] ?? null,
                        'sub_text'               => $notif['sub_text'] ?? $notif['subText'] ?? null,
                        'channel_id'             => $notif['channel_id'] ?? null,
                        'channel_name'           => $notif['channel_name'] ?? null,
                        'channel_importance'     => $notif['channel_importance'] ?? 0,
                        'group_key'              => $notif['group_key'] ?? null,
                        'sort_key'               => $notif['sort_key'] ?? null,
                        'is_ongoing'             => isset($notif['is_ongoing']) ? ($notif['is_ongoing'] ? 1 : 0) : 0,
                        'is_local_only'          => isset($notif['is_local_only']) ? ($notif['is_local_only'] ? 1 : 0) : 0,
                        'color'                  => $notif['color'] ?? null,
                        'badge_icon'             => $notif['badge_icon'] ?? null,
                        'large_icon_base64'      => $notif['large_icon_base64'] ?? null,
                        'actions'                => json_encode($notif['actions'] ?? []),
                        'remote_input_history'   => json_encode($notif['remote_input_history'] ?? []),
                        'people'                 => json_encode($notif['people'] ?? []),
                        'shortcut_id'            => $notif['shortcut_id'] ?? null,
                        'locus_id'               => $notif['locus_id'] ?? null,
                        'bubble_metadata'        => $notif['bubble_metadata'] ?? null,
                        'settings_text'          => $notif['settings_text'] ?? null,
                        'timeout_after'          => $notif['timeout_after'] ?? null,
                        'flags'                  => $notif['flags'] ?? 0,
                        'suppressed_visual_effects' => $notif['suppressed_visual_effects'] ?? 0,
                        'category'               => $notif['category'] ?? $notif['channel'] ?? $notif['channel_id'] ?? null,
                        'visibility'             => $notif['visibility'] ?? null,
                        'is_screen_notification' => self::resolveScreenNotificationFlag($notif),
                        'notification_timestamp' => $notifTs,
                        'action'                 => $action,
                        'extracted_at'           => $extractedAt,
                        'created_at'             => $dated,
                        'updated_at'             => $dated,
                    ];
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_notifications')->insertBatch($batchData);
            }

            log_message('info', '[parse_notifications] Inserted ' . count($batchData) . ' notification rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_notifications] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AccessibilityExtractor → tbl_system_accessibility_services
     * File category: accessibility
     * Stores enabled accessibility services
     */
    public function parse_accessibility(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $services     = $json['services'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_system_accessibility_services')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($services as $svc) {
                $this->db->table('tbl_system_accessibility_services')->insert([
                    'owner_id'                    => $owner_id,
                    'device_id'                   => $device_id,
                    'service_id'                  => $svc['id'] ?? null,
                    'package_name'                => $svc['package_name'] ?? null,
                    'description'                 => $svc['description'] ?? null,
                    'feedback_type'               => $svc['feedback_type'] ?? null,
                    'capabilities'                => $svc['capabilities'] ?? null,
                    'flags'                       => $svc['flags'] ?? null,
                    'notification_timeout'        => $svc['notification_timeout'] ?? null,
                    'settings_activity_name'      => $svc['settings_activity_name'] ?? null,
                    'can_retrieve_window_content' => isset($svc['can_retrieve_window_content']) ? ($svc['can_retrieve_window_content'] ? 1 : 0) : null,
                    'extracted_at'                => $extracted_at,
                    'created_at'                  => $dated,
                ]);
            }

            log_message('info', '[parse_accessibility] Inserted ' . count($services) . ' a11y services for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_accessibility] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * InputMethodExtractor → tbl_system_input_methods + tbl_system_input_method_subtypes
     * File category: input_methods
     * Stores enabled IMEs and their subtypes
     */
    public function parse_input_methods(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated      = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $imes         = $json['input_methods'] ?? [];

            // Avoid duplicate
            $exists = $this->db->table('tbl_system_input_methods')
                ->where('device_id', $device_id)
                ->where('extracted_at', $extracted_at)
                ->countAllResults() > 0;
            if ($exists) return true;

            foreach ($imes as $ime) {
                $this->db->table('tbl_system_input_methods')->insert([
                    'owner_id'     => $owner_id,
                    'device_id'    => $device_id,
                    'ime_id'       => $ime['id'] ?? null,
                    'package_name' => $ime['package_name'] ?? null,
                    'label'        => $ime['label'] ?? null,
                    'service_name' => $ime['service_name'] ?? null,
                    'is_system'    => isset($ime['is_system']) ? ($ime['is_system'] ? 1 : 0) : 0,
                    'is_auxiliary' => isset($ime['is_auxiliary']) ? ($ime['is_auxiliary'] ? 1 : 0) : 0,
                    'extracted_at' => $extracted_at,
                    'created_at'   => $dated,
                ]);
                $imeId = $this->db->insertID();

                // Subtypes
                $subtypes = $ime['subtypes'] ?? [];
                if (!empty($subtypes) && $imeId > 0) {
                    $batch = [];
                    foreach ($subtypes as $st) {
                        $batch[] = [
                            'input_method_id'                    => $imeId,
                            'owner_id'                           => $owner_id,
                            'locale'                             => $st['locale'] ?? null,
                            'mode'                               => $st['mode'] ?? null,
                            'name'                               => $st['name'] ?? null,
                            'is_ascii_capable'                   => isset($st['is_ascii_capable']) ? ($st['is_ascii_capable'] ? 1 : 0) : 0,
                            'is_auxiliary'                       => isset($st['is_auxiliary']) ? ($st['is_auxiliary'] ? 1 : 0) : 0,
                            'overrides_implicitly_enabled_subtype' => isset($st['overrides_implicitly_enabled_subtype']) ? ($st['overrides_implicitly_enabled_subtype'] ? 1 : 0) : 0,
                            'created_at'                         => $dated,
                        ];
                    }
                    $this->db->table('tbl_system_input_method_subtypes')->insertBatch($batch);
                }
            }

            log_message('info', '[parse_input_methods] Inserted ' . count($imes) . ' IMEs for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_input_methods] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AppPermissionExtractor
     * File prefix: app_permissions_TIMESTAMP.enc
     */
    public function parse_app_permissions(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['permissions_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $perm) {
                $pkg = $perm['package_name'] ?? null;
                $name = $perm['permission_name'] ?? null;
                if (!$pkg || !$name) continue;

                $exists = $this->db->table('tbl_system_app_permissions')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('package_name', $pkg)
                    ->where('permission_name', $name)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'package_name' => $pkg,
                    'permission_name' => $name,
                    'is_requested' => isset($perm['is_requested']) ? ($perm['is_requested'] ? 1 : 0) : 1,
                    'is_granted' => isset($perm['is_granted']) ? ($perm['is_granted'] ? 1 : 0) : 0,
                    'is_runtime' => isset($perm['is_runtime']) ? ($perm['is_runtime'] ? 1 : 0) : 0,
                    'is_system_fixed' => isset($perm['is_system_fixed']) ? ($perm['is_system_fixed'] ? 1 : 0) : 0,
                    'is_revoked' => isset($perm['is_revoked']) ? ($perm['is_revoked'] ? 1 : 0) : 0,
                    'grant_time' => $perm['grant_time'] ?? null,
                    'last_used_time' => $perm['last_used_time'] ?? null,
                    'flags' => $perm['flags'] ?? null,
                    'is_one_time' => isset($perm['is_one_time']) ? ($perm['is_one_time'] ? 1 : 0) : 0,
                    'is_auto_revoke_whitelisted' => isset($perm['is_auto_revoke_whitelisted']) ? ($perm['is_auto_revoke_whitelisted'] ? 1 : 0) : 0,
                    'is_hard_restricted' => isset($perm['is_hard_restricted']) ? ($perm['is_hard_restricted'] ? 1 : 0) : 0,
                    'is_soft_restricted' => isset($perm['is_soft_restricted']) ? ($perm['is_soft_restricted'] ? 1 : 0) : 0,
                    'user_set' => isset($perm['user_set']) ? ($perm['user_set'] ? 1 : 0) : 0,
                    'fixed_policy' => isset($perm['fixed_policy']) ? ($perm['fixed_policy'] ? 1 : 0) : 0,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_system_app_permissions')->insertBatch($batch);
            }

            log_message('info', '[parse_app_permissions] Inserted ' . count($batch) . ' permissions from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_app_permissions] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * RunningProcessesDetailedExtractor
     * File prefix: running_processes_TIMESTAMP.enc
     */
    public function parse_running_processes(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['processes'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $proc) {
                $pid = $proc['pid'] ?? null;
                if ($pid === null) continue;

                $exists = $this->db->table('tbl_system_running_processes_detailed')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('pid', $pid)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'pid' => $pid,
                    'name' => $proc['name'] ?? null,
                    'ppid' => $proc['ppid'] ?? null,
                    'uid' => $proc['uid'] ?? null,
                    'importance' => $proc['importance'] ?? null,
                    'state' => $proc['state'] ?? null,
                    'tid' => $proc['tid'] ?? null,
                    'nice' => $proc['nice'] ?? null,
                    'threads' => $proc['threads'] ?? null,
                    'vsize_kb' => $proc['vsize_kb'] ?? null,
                    'vsize_peak_kb' => $proc['vsize_peak_kb'] ?? null,
                    'rss_kb' => $proc['rss_kb'] ?? null,
                    'pss_kb' => $proc['pss_kb'] ?? null,
                    'uss_kb' => $proc['uss_kb'] ?? null,
                    'swap_kb' => $proc['swap_kb'] ?? null,
                    'cpu_time_ms' => $proc['cpu_time_ms'] ?? null,
                    'cpu_time_user_ms' => $proc['cpu_time_user_ms'] ?? null,
                    'cpu_time_system_ms' => $proc['cpu_time_system_ms'] ?? null,
                    'start_time' => $proc['start_time'] ?? null,
                    'elapsed_time_ms' => $proc['elapsed_time_ms'] ?? null,
                    'processor' => $proc['processor'] ?? null,
                    'cmdline' => $proc['cmdline'] ?? null,
                    'gid' => $proc['gid'] ?? null,
                    'groups' => is_array($proc['groups'] ?? null) ? implode(',', $proc['groups']) : ($proc['groups'] ?? null),
                    'priority' => $proc['priority'] ?? null,
                    'fd_count' => $proc['fd_count'] ?? null,
                    'socket_count' => $proc['socket_count'] ?? null,
                    'wake_lock_count' => $proc['wake_lock_count'] ?? null,
                    'oom_score' => $proc['oom_score'] ?? null,
                    'oom_score_adj' => $proc['oom_score_adj'] ?? null,
                    'cgroup' => $proc['cgroup'] ?? null,
                    'selinux_context' => $proc['selinux_context'] ?? null,
                    'capabilities_eff' => $proc['capabilities_eff'] ?? null,
                    'capabilities_prm' => $proc['capabilities_prm'] ?? null,
                    'capabilities_inh' => $proc['capabilities_inh'] ?? null,
                    'capabilities_bnd' => $proc['capabilities_bnd'] ?? null,
                    'capabilities_amb' => $proc['capabilities_amb'] ?? null,
                    'seccomp_mode' => $proc['seccomp_mode'] ?? null,
                    'env_vars' => is_array($proc['env_vars'] ?? null) ? json_encode($proc['env_vars']) : ($proc['env_vars'] ?? null),
                    'signal_mask' => $proc['signal_mask'] ?? null,
                    'signal_pending' => $proc['signal_pending'] ?? null,
                    'signal_blocked' => $proc['signal_blocked'] ?? null,
                    'signal_ignored' => $proc['signal_ignored'] ?? null,
                    'signal_caught' => $proc['signal_caught'] ?? null,
                    'wake_channels' => $proc['wake_channels'] ?? null,
                    'timer_slack_ns' => $proc['timer_slack_ns'] ?? null,
                    'namespace' => $proc['namespace'] ?? null,
                    'open_files' => is_array($proc['open_files'] ?? null) ? json_encode($proc['open_files']) : ($proc['open_files'] ?? null),
                    'memory_maps' => is_array($proc['memory_maps'] ?? null) ? json_encode($proc['memory_maps']) : ($proc['memory_maps'] ?? null),
                    'stack_trace' => $proc['stack_trace'] ?? null,
                    'cputime_clock_id' => $proc['cputime_clock_id'] ?? null,
                    'cpu_percent' => $proc['cpu_percent'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_system_running_processes_detailed')->insertBatch($batch);
            }

            log_message('info', '[parse_running_processes] Inserted ' . count($batch) . ' detailed processes from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_running_processes] Exception: ' . $e->getMessage());
            return false;
        }
    }
}
