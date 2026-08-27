<?php

namespace App\Models;

use CodeIgniter\Model;

class FinderModel extends Model
{
    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $useAutoIncrement = true;
    protected $returnType = 'array';
    protected $useSoftDeletes = false;
    protected $allowedFields = [];
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    public $pager;
    public $total_timeline = 0;

    public ?string $deviceId = null;

    private ?\App\Models\Finder\FinderComms $comms = null;
    private ?\App\Models\Finder\FinderSystem $system = null;
    private ?\App\Models\Finder\FinderEnvironment $environment = null;
    private ?\App\Models\Finder\FinderUser $userModel = null;

    private function comms(): \App\Models\Finder\FinderComms
    {
        if ($this->comms === null) {
            $this->comms = new \App\Models\Finder\FinderComms($this);
        }
        return $this->comms;
    }

    private function system(): \App\Models\Finder\FinderSystem
    {
        if ($this->system === null) {
            $this->system = new \App\Models\Finder\FinderSystem($this);
        }
        return $this->system;
    }

    private function environment(): \App\Models\Finder\FinderEnvironment
    {
        if ($this->environment === null) {
            $this->environment = new \App\Models\Finder\FinderEnvironment($this);
        }
        return $this->environment;
    }

    private function userModel(): \App\Models\Finder\FinderUser
    {
        if ($this->userModel === null) {
            $this->userModel = new \App\Models\Finder\FinderUser($this);
        }
        return $this->userModel;
    }

    public function setDeviceId(?string $deviceId): void
    {
        $this->deviceId = $deviceId;
    }

    public function applyOwnerDeviceFilter($builder, int $user_id)
    {
        $builder->where('owner_id', $user_id);
        if (!empty($this->deviceId) && $this->deviceId !== 'all') {
            $builder->where('device_id', $this->deviceId);
        }
        return $builder;
    }

    public function fq(string $table, int $userId)
    {
        $builder = $this->db->table($table);
        $builder->where('owner_id', $userId);
        if (!empty($this->deviceId) && $this->deviceId !== 'all') {
            $builder->where('device_id', $this->deviceId);
        }
        return $builder;
    }

    /**
     * Count records for a user in a given table, using the same owner/device filter.
     */
    public function cq(string $table, int $userId): int
    {
        try {
            $result = $this->fq($table, $userId)->countAllResults();
            return $result ?: 0;
        } catch (\Exception $e) {
            log_message('error', "count query error for {$table}: " . $e->getMessage());
            return 0;
        }
    }

    /**
     * Gets basic user data if logged in.
     *
     * @return array|false
     */
    public function basic_user()
    {
        try {
            if (auth()->loggedIn()) {
                $user = auth()->user();
                $userArray = $user->toArray();
                $userArray['email'] = $user->getEmail();
                $userArray['id'] = $user->id; // Ensure ID is present

                // Join with user_profiles to get the avatar and other settings
                $profile = $this->db->table('user_profiles')
                    ->where('user_id', $user->id)
                    ->get()
                    ->getRowArray();

                if ($profile) {
                    $userArray['profile_image'] = $profile['profile_image'] ?? null;
                    $userArray['bio'] = $profile['bio'] ?? null;
                    $userArray['language'] = $profile['language'] ?? 'en';
                    $userArray['timezone'] = $profile['timezone'] ?? 'UTC';
                    $userArray['theme'] = $profile['theme'] ?? 'light';
                    $userArray['email_notifications'] = $profile['email_notifications'] ?? 1;
                } else {
                    $userArray['profile_image'] = null;
                    $userArray['email_notifications'] = 1;
                }

                return $userArray;
            }
            log_message('error', 'User not logged in');
            return false;
        } catch (\Exception $e) {
            log_message('error', 'basic_user error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generalized count query.
     *
     * @param string $table
     * @param int $user_id
     * @param array $extraWhere Optional extra where conditions
     * @param string|null $blockColumn Optional column for exclusions
     * @param array $blockedValues Optional values to exclude
     * @return int
     */
    public function getCount(string $table, int $user_id, array $extraWhere = [], ?string $blockColumn = null, array $blockedValues = []): int
    {
        try {
            $builder = $this->db->table($table);

            $this->applyOwnerDeviceFilter($builder, $user_id);

            if (!empty($extraWhere)) {
                $builder->where($extraWhere);
            }
            if ($blockColumn && !empty($blockedValues)) {
                $builder->whereNotIn($blockColumn, $blockedValues);
            }
            return $builder->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'getCount error for ' . $table . ': ' . $e->getMessage());
            return 0;
        }
    }

    public function getBlockedIdentifiers(int $userId, string $category): array
    {
        try {
            if (!$this->db->tableExists('tbl_user_blocklists')) return [];
            
            $blocks = $this->db->table('tbl_user_blocklists')
                           ->select('identifier')
                           ->where('owner_id', $userId)
                           ->where('category', $category)
                           ->get()
                           ->getResultArray();
            return array_column($blocks, 'identifier');
        } catch (\Exception $e) {
            return [];
        }
    }

    public function decode_sms_body(string $body): string
    {
        $text = $body;
        // Only decode if it looks like base64 or if it's long enough to be an encoded msg
        if (strlen($body) > 4 && preg_match('/^[a-zA-Z0-9\/\+=]+$/', $body)) {
            $decoded = base64_decode($body, true);
            if ($decoded !== false && mb_check_encoding($decoded, 'UTF-8')) {
                $text = $decoded;
            }
        }
        return $text;
    }

    /* ---------------------------------------------------------
     * COMM DELEGATIONS (FinderComms)
     * --------------------------------------------------------- */

    public function deleteCallsByUser(int $user_id): bool
    {
        return $this->comms()->deleteCallsByUser($user_id);
    }

    public function deleteContactsByUser(int $user_id): bool
    {
        return $this->comms()->deleteContactsByUser($user_id);
    }

    public function deleteSmsByUser(int $user_id): bool
    {
        return $this->comms()->deleteSmsByUser($user_id);
    }

    public function get_count_Sms(int $user_id): int
    {
        return $this->comms()->get_count_Sms($user_id);
    }

    public function get_count_Sms_category(int $user_id, string $category): int
    {
        return $this->comms()->get_count_Sms_category($user_id, $category);
    }

    public function get_count_Contacts(int $user_id): int
    {
        return $this->comms()->get_count_Contacts($user_id);
    }

    public function get_count_Calls(int $user_id): int
    {
        return $this->comms()->get_count_Calls($user_id);
    }

    public function get_count_Calls_by_type(int $user_id, string $callType): int
    {
        return $this->comms()->get_count_Calls_by_type($user_id, $callType);
    }

    public function get_contact(string $nom)
    {
        return $this->comms()->get_contact($nom);
    }

    public function get_contact_info($contactNumber1)
    {
        return $this->comms()->get_contact_info($contactNumber1);
    }

    public function get_contacts1(int $userId, int $perPage = 25): array
    {
        return $this->comms()->get_contacts1($userId, $perPage);
    }

    public function get_contacts(int $userId, int $perPage = 25): array
    {
        return $this->comms()->get_contacts($userId, $perPage);
    }

    public function get_sms_type(int $user_id, string $sms_type, int $perPage = 25): array
    {
        return $this->comms()->get_sms_type($user_id, $sms_type, $perPage);
    }

    public function get_sms(int $user_id, int $perPage = 25): array
    {
        return $this->comms()->get_sms($user_id, $perPage);
    }

    public function get_sms_active(int $user_id, int $perPage = 15): array
    {
        return $this->comms()->get_sms_active($user_id, $perPage);
    }

    public function get_call_logs(int $user_id, int $perPage = 25): array
    {
        return $this->comms()->get_call_logs($user_id, $perPage);
    }

    public function get_calls_limited(int $user_id, string $category, int $perPage = 25): array
    {
        return $this->comms()->get_calls_limited($user_id, $category, $perPage);
    }

    public function get_calls_active(int $user_id, int $perPage = 15): array
    {
        return $this->comms()->get_calls_active($user_id, $perPage);
    }

    public function get_sms_from_sender(int $user_id, $sender, int $perPage = 20): array
    {
        return $this->comms()->get_sms_from_sender($user_id, $sender, $perPage);
    }

    public function get_categorized_sms_counts(int $userId): array
    {
        return $this->comms()->get_categorized_sms_counts($userId);
    }

    public function get_categorized_call_counts(int $userId): array
    {
        return $this->comms()->get_categorized_call_counts($userId);
    }

    public function get_categorized_sms(int $userId, string $category, int $perPage = 20, int $page = 1): array
    {
        return $this->comms()->get_categorized_sms($userId, $category, $perPage, $page);
    }

    public function get_categorized_calls(int $userId, string $category, int $perPage = 20, int $page = 1): array
    {
        return $this->comms()->get_categorized_calls($userId, $category, $perPage, $page);
    }

    public function search_sms(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        return $this->comms()->search_sms($userId, $query, $limit, $offset);
    }

    public function search_sms_count(int $userId, string $query): int
    {
        return $this->comms()->search_sms_count($userId, $query);
    }

    public function search_calls(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        return $this->comms()->search_calls($userId, $query, $limit, $offset);
    }

    public function search_calls_count(int $userId, string $query): int
    {
        return $this->comms()->search_calls_count($userId, $query);
    }

    public function search_contacts(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        return $this->comms()->search_contacts($userId, $query, $limit, $offset);
    }

    public function search_contacts_count(int $userId, string $query): int
    {
        return $this->comms()->search_contacts_count($userId, $query);
    }

    public function get_financial_transactions(int $userId): array
    {
        return $this->comms()->get_financial_transactions($userId);
    }

    public function get_social_graph(int $userId, int $limit = 20): array
    {
        return $this->comms()->get_social_graph($userId, $limit);
    }

    public function get_communication_quality(int $userId): array
    {
        return $this->comms()->get_communication_quality($userId);
    }

    public function get_scam_sms_audit(int $userId): array
    {
        return $this->comms()->get_scam_sms_audit($userId);
    }

    public function get_subscription_forecast(int $userId): array
    {
        return $this->comms()->get_subscription_forecast($userId);
    }

    public function get_sentiment_profile(int $userId): array
    {
        return $this->comms()->get_sentiment_profile($userId);
    }

    public function get_circadian_sleep_profile(int $userId): array
    {
        return $this->environment()->get_circadian_sleep_profile($userId);
    }

    public function get_daily_travel_distances(int $userId): array
    {
        return $this->environment()->get_daily_travel_distances($userId);
    }

    public function get_cell_tower_fallbacks(int $userId): array
    {
        return $this->environment()->get_cell_tower_fallbacks($userId);
    }

    public function get_geofence_events(int $userId): array
    {
        return $this->environment()->get_geofence_events($userId);
    }

    public function get_paired_bluetooth_colocation(int $userId): array
    {
        return $this->environment()->get_paired_bluetooth_colocation($userId);
    }

    public function get_cross_channel_contact_matrix(int $userId, string $contactNumber): array
    {
        return $this->comms()->get_cross_channel_contact_matrix($userId, $contactNumber);
    }

    public function get_contact_response_metrics(int $userId): array
    {
        return $this->comms()->get_contact_response_metrics($userId);
    }

    public function get_first_last_contact_timestamps(int $userId): array
    {
        return $this->comms()->get_first_last_contact_timestamps($userId);
    }

    public function get_app_bandwidth_usage(int $userId): array
    {
        return $this->userModel()->get_app_bandwidth_usage($userId);
    }

    public function get_app_crash_analytics(int $userId): array
    {
        return $this->userModel()->get_app_crash_analytics($userId);
    }

    public function get_unused_bloatware_apps(int $userId): array
    {
        return $this->userModel()->get_unused_bloatware_apps($userId);
    }


    public function get_clipboard_privacy_monitor(int $userId): array
    {
        return $this->environment()->get_clipboard_privacy_monitor($userId);
    }

    public function get_sideloaded_app_audit(int $userId): array
    {
        return $this->environment()->get_sideloaded_app_audit($userId);
    }

    public function get_accessibility_abuse_audit(int $userId): array
    {
        return $this->environment()->get_accessibility_abuse_audit($userId);
    }

    public function get_silent_hardware_captures(int $userId): array
    {
        return $this->environment()->get_silent_hardware_captures($userId);
    }



    public function delete_call_log(int $id, int $userId): bool
    {
        return $this->comms()->delete_call_log($id, $userId);
    }

    public function delete_sms(int $id, int $userId): bool
    {
        return $this->comms()->delete_sms($id, $userId);
    }

    public function delete_contact(int $id, int $userId): bool
    {
        return $this->comms()->delete_contact($id, $userId);
    }

    public function get_basic_timeline(int $userId, int $limit = 200, int $sinceDays = 0): array
    {
        return $this->comms()->get_basic_timeline($userId, $limit, $sinceDays);
    }

    public function deleteNotificationsByUser(int $user_id): bool
    {
        return $this->comms()->deleteNotificationsByUser($user_id);
    }

    public function delete_notifications_by_app(int $user_id, string $packageName): bool
    {
        return $this->comms()->delete_notifications_by_app($user_id, $packageName);
    }

    public function get_count_Notifications(int $user_id): int
    {
        return $this->comms()->get_count_Notifications($user_id);
    }

    public function export_notifications(int $user_id, int $limit = 1000): array
    {
        return $this->comms()->export_notifications($user_id, $limit);
    }

    public function get_notifications(int $user_id, int $perPage = 50): array
    {
        return $this->comms()->get_notifications($user_id, $perPage);
    }

    public function get_count_notification_groups(int $user_id): int
    {
        return $this->comms()->get_count_notification_groups($user_id);
    }

    public function get_count_notification_packages(int $user_id): int
    {
        return $this->comms()->get_count_notification_packages($user_id);
    }

    public function get_notifications_grouped(int $user_id, int $perPage = 50): array
    {
        return $this->comms()->get_notifications_grouped($user_id, $perPage);
    }

    public function get_count_notifications_for_group(int $user_id, string $group_key): int
    {
        return $this->comms()->get_count_notifications_for_group($user_id, $group_key);
    }

    public function get_count_notifications_for_package(int $user_id, string $package_name): int
    {
        return $this->comms()->get_count_notifications_for_package($user_id, $package_name);
    }

    public function get_notifications_for_group(int $user_id, string $group_key, int $perPage = 50): array
    {
        return $this->comms()->get_notifications_for_group($user_id, $group_key, $perPage);
    }

    public function get_notifications_for_package(int $user_id, string $package_name, int $perPage = 50): array
    {
        return $this->comms()->get_notifications_for_package($user_id, $package_name, $perPage);
    }

    public function get_notifications_group_summary(int $user_id, string $group_key): array
    {
        return $this->comms()->get_notifications_group_summary($user_id, $group_key);
    }

    public function get_notifications_package_summary(int $user_id, string $package_name): array
    {
        return $this->comms()->get_notifications_package_summary($user_id, $package_name);
    }

    public function delete_notification(int $id, int $userId): bool
    {
        return $this->comms()->delete_notification($id, $userId);
    }

    /* ---------------------------------------------------------
     * SYSTEM DELEGATIONS (FinderSystem)
     * --------------------------------------------------------- */

    public function deleteAppsByUser(int $user_id): bool
    {
        return $this->system()->deleteAppsByUser($user_id);
    }

    public function deleteDeviceContextByUser(int $user_id): bool
    {
        return $this->system()->deleteDeviceContextByUser($user_id);
    }

    public function deleteNetworkInfoByUser(int $user_id): bool
    {
        return $this->system()->deleteNetworkInfoByUser($user_id);
    }

    public function deleteAccountsByUser(int $user_id): bool
    {
        return $this->system()->deleteAccountsByUser($user_id);
    }

    public function deleteAccessibilityByUser(int $user_id): bool
    {
        return $this->system()->deleteAccessibilityByUser($user_id);
    }

    public function deleteInputMethodsByUser(int $user_id): bool
    {
        return $this->system()->deleteInputMethodsByUser($user_id);
    }

    public function deleteSecurityAuditByUser(int $user_id): bool
    {
        return $this->system()->deleteSecurityAuditByUser($user_id);
    }

    public function deleteProcInfoByUser(int $user_id): bool
    {
        return $this->system()->deleteProcInfoByUser($user_id);
    }

    public function deleteDefaultAppsByUser(int $user_id): bool
    {
        return $this->system()->deleteDefaultAppsByUser($user_id);
    }

    public function deleteAlarmsByUser(int $user_id): bool
    {
        return $this->system()->deleteAlarmsByUser($user_id);
    }

    public function deleteAppSecurityByUser(int $user_id): bool
    {
        return $this->system()->deleteAppSecurityByUser($user_id);
    }

    public function deleteNetworkSecurityByUser(int $user_id): bool
    {
        return $this->system()->deleteNetworkSecurityByUser($user_id);
    }

    public function deleteTelephonyNetworkByUser(int $user_id): bool
    {
        return $this->system()->deleteTelephonyNetworkByUser($user_id);
    }

    public function deleteSystemLocaleByUser(int $user_id): bool
    {
        return $this->system()->deleteSystemLocaleByUser($user_id);
    }

    public function deleteHardwareGraphicsByUser(int $user_id): bool
    {
        return $this->system()->deleteHardwareGraphicsByUser($user_id);
    }

    public function deleteHardwareNetworkByUser(int $user_id): bool
    {
        return $this->system()->deleteHardwareNetworkByUser($user_id);
    }

    public function deleteCameraInfoByUser(int $user_id): bool
    {
        return $this->system()->deleteCameraInfoByUser($user_id);
    }

    public function deleteBatteryStatsByUser(int $user_id): bool
    {
        return $this->system()->deleteBatteryStatsByUser($user_id);
    }

    public function deleteDisplayInfoByUser(int $user_id): bool
    {
        return $this->system()->deleteDisplayInfoByUser($user_id);
    }

    public function deleteProcessesByUser(int $user_id): bool
    {
        return $this->system()->deleteProcessesByUser($user_id);
    }

    public function deleteCrashLogsByUser(int $user_id): bool
    {
        return $this->system()->deleteCrashLogsByUser($user_id);
    }

    public function deleteDozeStandbyByUser(int $user_id): bool
    {
        return $this->system()->deleteDozeStandbyByUser($user_id);
    }

    public function deleteEmailAccountsByUser(int $user_id): bool
    {
        return $this->system()->deleteEmailAccountsByUser($user_id);
    }

    public function deleteKeyboardInputByUser(int $user_id): bool
    {
        return $this->system()->deleteKeyboardInputByUser($user_id);
    }

    public function deleteKeyguardEventsByUser(int $user_id): bool
    {
        return $this->system()->deleteKeyguardEventsByUser($user_id);
    }

    public function deleteVpnConfigByUser(int $user_id): bool
    {
        return $this->system()->deleteVpnConfigByUser($user_id);
    }

    public function deleteRunningProcessesDetailedByUser(int $user_id): bool
    {
        return $this->system()->deleteRunningProcessesDetailedByUser($user_id);
    }

    public function deleteAudioDevicesByUser(int $user_id): bool
    {
        return $this->system()->deleteAudioDevicesByUser($user_id);
    }

    public function deleteBiometricByUser(int $user_id): bool
    {
        return $this->system()->deleteBiometricByUser($user_id);
    }

    public function deletePowerRailsByUser(int $user_id): bool
    {
        return $this->system()->deletePowerRailsByUser($user_id);
    }

    public function deleteUsbDevicesByUser(int $user_id): bool
    {
        return $this->system()->deleteUsbDevicesByUser($user_id);
    }

    public function deleteVibrationByUser(int $user_id): bool
    {
        return $this->system()->deleteVibrationByUser($user_id);
    }

    public function deleteMlJobsByUser(int $user_id): bool
    {
        return $this->system()->deleteMlJobsByUser($user_id);
    }

    public function deleteMlResultsByUser(int $user_id): bool
    {
        return $this->system()->deleteMlResultsByUser($user_id);
    }

    public function deleteMlAnalysisTrackingByUser(int $user_id): bool
    {
        return $this->system()->deleteMlAnalysisTrackingByUser($user_id);
    }

    public function get_count_Apps(int $user_id): int
    {
        return $this->system()->get_count_Apps($user_id);
    }

    public function get_count_Apps_category(int $user_id, int $is_system): int
    {
        return $this->system()->get_count_Apps_category($user_id, $is_system);
    }

    public function get_apps(int $user_id, int $perPage = 20): array
    {
        return $this->system()->get_apps($user_id, $perPage);
    }

    public function get_count_DeviceContext(int $user_id): int
    {
        return $this->system()->get_count_DeviceContext($user_id);
    }

    public function get_count_NetworkInfo(int $user_id): int
    {
        return $this->system()->get_count_NetworkInfo($user_id);
    }

    public function get_count_Accounts(int $user_id): int
    {
        return $this->system()->get_count_Accounts($user_id);
    }

    public function get_count_SecurityAudit(int $user_id): int
    {
        return $this->system()->get_count_SecurityAudit($user_id);
    }

    public function get_count_HardwareGraphics(int $user_id): int
    {
        return $this->system()->get_count_HardwareGraphics($user_id);
    }

    public function get_count_CameraInfo(int $user_id): int
    {
        return $this->system()->get_count_CameraInfo($user_id);
    }

    public function get_count_BatteryStats(int $user_id): int
    {
        return $this->system()->get_count_BatteryStats($user_id);
    }

    public function get_count_Accessibility(int $user_id): int
    {
        return $this->system()->get_count_Accessibility($user_id);
    }

    public function get_count_InputMethods(int $user_id): int
    {
        return $this->system()->get_count_InputMethods($user_id);
    }

    public function get_count_Processes(int $user_id): int
    {
        return $this->system()->get_count_Processes($user_id);
    }

    public function get_count_ProcInfo(int $user_id): int
    {
        return $this->system()->get_count_ProcInfo($user_id);
    }

    public function get_count_SimConfig(int $user_id): int
    {
        return $this->system()->get_count_SimConfig($user_id);
    }

    public function export_device_context(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_device_context($user_id, $limit);
    }

    public function export_network_info(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_network_info($user_id, $limit);
    }

    public function export_accounts(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_accounts($user_id, $limit);
    }

    public function export_security_audit(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_security_audit($user_id, $limit);
    }

    public function export_accessibility(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_accessibility($user_id, $limit);
    }

    public function export_input_methods(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_input_methods($user_id, $limit);
    }

    public function export_proc_info(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_proc_info($user_id, $limit);
    }

    public function export_default_apps(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_default_apps($user_id, $limit);
    }

    public function export_alarms(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_alarms($user_id, $limit);
    }

    public function export_app_security(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_app_security($user_id, $limit);
    }

    public function export_network_security(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_network_security($user_id, $limit);
    }

    public function export_telephony_network(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_telephony_network($user_id, $limit);
    }

    public function export_system_locale(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_system_locale($user_id, $limit);
    }

    public function export_hardware_graphics(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_hardware_graphics($user_id, $limit);
    }

    public function export_hardware_network(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_hardware_network($user_id, $limit);
    }

    public function export_camera_info(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_camera_info($user_id, $limit);
    }

    public function export_battery_stats(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_battery_stats($user_id, $limit);
    }

    public function export_display_info(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_display_info($user_id, $limit);
    }

    public function export_processes(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_processes($user_id, $limit);
    }

    public function export_crash_logs(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_crash_logs($user_id, $limit);
    }

    public function export_doze_standby(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_doze_standby($user_id, $limit);
    }

    public function export_email_accounts(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_email_accounts($user_id, $limit);
    }

    public function export_keyboard_input(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_keyboard_input($user_id, $limit);
    }

    public function export_keyguard_events(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_keyguard_events($user_id, $limit);
    }

    public function export_vpn_config(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_vpn_config($user_id, $limit);
    }

    public function export_running_processes_detailed(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_running_processes_detailed($user_id, $limit);
    }

    public function export_audio_devices(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_audio_devices($user_id, $limit);
    }

    public function export_biometric(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_biometric($user_id, $limit);
    }

    public function export_power_rails(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_power_rails($user_id, $limit);
    }

    public function export_usb_devices(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_usb_devices($user_id, $limit);
    }

    public function export_vibration(int $user_id, int $limit = 1000): array
    {
        return $this->system()->export_vibration($user_id, $limit);
    }

    public function get_device_context(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_device_context($user_id, $perPage);
    }

    public function get_network_info(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_network_info($user_id, $perPage);
    }

    public function get_accounts(int $user_id, int $perPage = 50): array
    {
        return $this->system()->get_accounts($user_id, $perPage);
    }

    public function getAccountsQuery(int $user_id): \CodeIgniter\Database\BaseBuilder
    {
        return $this->system()->getAccountsQuery($user_id);
    }

    public function tableQuery(string $table, int $userId, string $orderCol = 'extracted_at', string $orderDir = 'DESC'): \CodeIgniter\Database\BaseBuilder
    {
        return $this->system()->tableQuery($table, $userId, $orderCol, $orderDir);
    }

    public function get_app_detail_by_package(int $user_id, string $package_name): array
    {
        return $this->system()->get_app_detail_by_package($user_id, $package_name);
    }

    public function get_security_audit(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_security_audit($user_id, $perPage);
    }

    public function get_hardware_graphics(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_hardware_graphics($user_id, $perPage);
    }

    public function get_camera_info(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_camera_info($user_id, $perPage);
    }

    public function get_battery_stats(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_battery_stats($user_id, $perPage);
    }

    public function get_accessibility(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_accessibility($user_id, $perPage);
    }

    public function get_input_methods(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_input_methods($user_id, $perPage);
    }

    public function get_proc_info(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_proc_info($user_id, $perPage);
    }

    public function get_processes(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_processes($user_id, $perPage);
    }

    public function get_display_info(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_display_info($user_id, $perPage);
    }

    public function get_count_DisplayInfo(int $user_id): int
    {
        return $this->system()->get_count_DisplayInfo($user_id);
    }

    public function get_default_apps(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_default_apps($user_id, $perPage);
    }

    public function get_count_DefaultApps(int $user_id): int
    {
        return $this->system()->get_count_DefaultApps($user_id);
    }

    public function get_alarms(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_alarms($user_id, $perPage);
    }

    public function get_count_Alarms(int $user_id): int
    {
        return $this->system()->get_count_Alarms($user_id);
    }

    public function search_apps(int $userId, string $query, int $limit = 0, int $offset = 0): array
    {
        return $this->system()->search_apps($userId, $query, $limit, $offset);
    }

    public function search_apps_count(int $userId, string $query): int
    {
        return $this->system()->search_apps_count($userId, $query);
    }

    public function get_device_health(int $userId): array
    {
        return $this->system()->get_device_health($userId);
    }

    public function get_app_category_dist(int $userId): array
    {
        return $this->system()->get_app_category_dist($userId);
    }

    public function get_mobility_aggregates(int $userId): array
    {
        return $this->system()->get_mobility_aggregates($userId);
    }

    public function get_geospatial_clusters(int $userId): array
    {
        return $this->system()->get_geospatial_clusters($userId);
    }

    public function get_storage_forensics(int $userId): array
    {
        return $this->system()->get_storage_forensics($userId);
    }

    public function delete_app(int $id, int $userId): bool
    {
        return $this->system()->delete_app($id, $userId);
    }

    public function delete_device_context_row(int $id, int $userId): bool
    {
        return $this->system()->delete_device_context_row($id, $userId);
    }

    public function delete_network_info_row(int $id, int $userId): bool
    {
        return $this->system()->delete_network_info_row($id, $userId);
    }

    public function delete_accounts_row(int $id, int $userId): bool
    {
        return $this->system()->delete_accounts_row($id, $userId);
    }

    public function delete_proc_info_row(int $id, int $userId): bool
    {
        return $this->system()->delete_proc_info_row($id, $userId);
    }

    public function delete_processes_row(int $id, int $userId): bool
    {
        return $this->system()->delete_processes_row($id, $userId);
    }

    public function delete_security_audit_row(int $id, int $userId): bool
    {
        return $this->system()->delete_security_audit_row($id, $userId);
    }

    public function delete_accessibility_row(int $id, int $userId): bool
    {
        return $this->system()->delete_accessibility_row($id, $userId);
    }

    public function delete_input_methods_row(int $id, int $userId): bool
    {
        return $this->system()->delete_input_methods_row($id, $userId);
    }

    public function delete_display_info_row(int $id, int $userId): bool
    {
        return $this->system()->delete_display_info_row($id, $userId);
    }

    public function delete_default_apps_row(int $id, int $userId): bool
    {
        return $this->system()->delete_default_apps_row($id, $userId);
    }

    public function delete_alarms_row(int $id, int $userId): bool
    {
        return $this->system()->delete_alarms_row($id, $userId);
    }

    public function get_count_HardwareNetwork(int $user_id): int
    {
        return $this->system()->get_count_HardwareNetwork($user_id);
    }

    public function get_hardware_network(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_hardware_network($user_id, $perPage);
    }

    public function delete_hardware_network_row(int $id, int $userId): bool
    {
        return $this->system()->delete_hardware_network_row($id, $userId);
    }

    public function get_count_AppSecurity(int $user_id): int
    {
        return $this->system()->get_count_AppSecurity($user_id);
    }

    public function get_app_security(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_app_security($user_id, $perPage);
    }

    public function delete_app_security_row(int $id, int $userId): bool
    {
        return $this->system()->delete_app_security_row($id, $userId);
    }

    public function get_count_NetworkSecurity(int $user_id): int
    {
        return $this->system()->get_count_NetworkSecurity($user_id);
    }

    public function get_network_security(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_network_security($user_id, $perPage);
    }

    public function delete_network_security_row(int $id, int $userId): bool
    {
        return $this->system()->delete_network_security_row($id, $userId);
    }

    public function get_count_TelephonyNetwork(int $user_id): int
    {
        return $this->system()->get_count_TelephonyNetwork($user_id);
    }

    public function get_telephony_network(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_telephony_network($user_id, $perPage);
    }

    public function delete_telephony_network_row(int $id, int $userId): bool
    {
        return $this->system()->delete_telephony_network_row($id, $userId);
    }

    public function get_count_SystemLocale(int $user_id): int
    {
        return $this->system()->get_count_SystemLocale($user_id);
    }

    public function get_system_locale(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_system_locale($user_id, $perPage);
    }

    public function delete_system_locale_row(int $id, int $userId): bool
    {
        return $this->system()->delete_system_locale_row($id, $userId);
    }

    public function get_count_AudioDevices(int $user_id): int
    {
        return $this->system()->get_count_AudioDevices($user_id);
    }

    public function get_audio_devices(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_audio_devices($user_id, $perPage);
    }

    public function delete_audio_devices_row(int $id, int $userId): bool
    {
        return $this->system()->delete_audio_devices_row($id, $userId);
    }

    public function get_count_Biometric(int $user_id): int
    {
        return $this->system()->get_count_Biometric($user_id);
    }

    public function get_biometric(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_biometric($user_id, $perPage);
    }

    public function delete_biometric_row(int $id, int $userId): bool
    {
        return $this->system()->delete_biometric_row($id, $userId);
    }

    public function get_count_GnssHardware(int $user_id): int
    {
        return $this->system()->get_count_GnssHardware($user_id);
    }

    public function get_gnss_hardware(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_gnss_hardware($user_id, $perPage);
    }

    public function delete_gnss_hardware_row(int $id, int $userId): bool
    {
        return $this->system()->delete_gnss_hardware_row($id, $userId);
    }

    public function get_count_PowerRails(int $user_id): int
    {
        return $this->system()->get_count_PowerRails($user_id);
    }

    public function get_power_rails(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_power_rails($user_id, $perPage);
    }

    public function delete_power_rails_row(int $id, int $userId): bool
    {
        return $this->system()->delete_power_rails_row($id, $userId);
    }

    public function get_count_UsbDevices(int $user_id): int
    {
        return $this->system()->get_count_UsbDevices($user_id);
    }

    public function get_usb_devices(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_usb_devices($user_id, $perPage);
    }

    public function delete_usb_devices_row(int $id, int $userId): bool
    {
        return $this->system()->delete_usb_devices_row($id, $userId);
    }

    public function get_count_Vibration(int $user_id): int
    {
        return $this->system()->get_count_Vibration($user_id);
    }

    public function get_vibration(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_vibration($user_id, $perPage);
    }

    public function delete_vibration_row(int $id, int $userId): bool
    {
        return $this->system()->delete_vibration_row($id, $userId);
    }

    public function get_count_AppPermissions(int $user_id): int
    {
        return $this->system()->get_count_AppPermissions($user_id);
    }

    public function get_app_permissions(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_app_permissions($user_id, $perPage);
    }

    public function delete_app_permissions_row(int $id, int $userId): bool
    {
        return $this->system()->delete_app_permissions_row($id, $userId);
    }

    public function get_count_CrashLogs(int $user_id): int
    {
        return $this->system()->get_count_CrashLogs($user_id);
    }

    public function get_crash_logs(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_crash_logs($user_id, $perPage);
    }

    public function delete_crash_logs_row(int $id, int $userId): bool
    {
        return $this->system()->delete_crash_logs_row($id, $userId);
    }

    public function get_count_DozeStandby(int $user_id): int
    {
        return $this->system()->get_count_DozeStandby($user_id);
    }

    public function get_doze_standby(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_doze_standby($user_id, $perPage);
    }

    public function delete_doze_standby_row(int $id, int $userId): bool
    {
        return $this->system()->delete_doze_standby_row($id, $userId);
    }

    public function get_count_EmailAccounts(int $user_id): int
    {
        return $this->system()->get_count_EmailAccounts($user_id);
    }

    public function get_email_accounts(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_email_accounts($user_id, $perPage);
    }

    public function delete_email_accounts_row(int $id, int $userId): bool
    {
        return $this->system()->delete_email_accounts_row($id, $userId);
    }

    public function get_count_KeyboardInput(int $user_id): int
    {
        return $this->system()->get_count_KeyboardInput($user_id);
    }

    public function get_keyboard_input(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_keyboard_input($user_id, $perPage);
    }

    public function delete_keyboard_input_row(int $id, int $userId): bool
    {
        return $this->system()->delete_keyboard_input_row($id, $userId);
    }

    public function get_count_KeyguardEvents(int $user_id): int
    {
        return $this->system()->get_count_KeyguardEvents($user_id);
    }

    public function get_keyguard_events(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_keyguard_events($user_id, $perPage);
    }

    public function delete_keyguard_events_row(int $id, int $userId): bool
    {
        return $this->system()->delete_keyguard_events_row($id, $userId);
    }

    public function get_count_VpnConfig(int $user_id): int
    {
        return $this->system()->get_count_VpnConfig($user_id);
    }

    public function get_vpn_config(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_vpn_config($user_id, $perPage);
    }

    public function delete_vpn_config_row(int $id, int $userId): bool
    {
        return $this->system()->delete_vpn_config_row($id, $userId);
    }

    public function get_count_RunningProcessesDetailed(int $user_id): int
    {
        return $this->system()->get_count_RunningProcessesDetailed($user_id);
    }

    public function get_running_processes_detailed(int $user_id, int $perPage = 25): array
    {
        return $this->system()->get_running_processes_detailed($user_id, $perPage);
    }

    public function delete_running_processes_detailed_row(int $id, int $userId): bool
    {
        return $this->system()->delete_running_processes_detailed_row($id, $userId);
    }

    public function delete_battery_stats_row(int $id, int $userId): bool
    {
        return $this->system()->delete_battery_stats_row($id, $userId);
    }

    public function delete_camera_info_row(int $id, int $userId): bool
    {
        return $this->system()->delete_camera_info_row($id, $userId);
    }

    public function delete_device_profile_row(int $id, int $userId): bool
    {
        return $this->system()->delete_device_profile_row($id, $userId);
    }

    /* ---------------------------------------------------------
     * ENVIRONMENT DELEGATIONS (FinderEnvironment)
     * --------------------------------------------------------- */

    public function deleteLocationsByUser(int $user_id): bool
    {
        return $this->environment()->deleteLocationsByUser($user_id);
    }

    public function deleteWifiTrafficByUser(int $user_id): bool
    {
        return $this->environment()->deleteWifiTrafficByUser($user_id);
    }

    public function deleteBluetoothDevicesByUser(int $user_id): bool
    {
        return $this->environment()->deleteBluetoothDevicesByUser($user_id);
    }

    public function deleteWifiNeighboursByUser(int $user_id): bool
    {
        return $this->environment()->deleteWifiNeighboursByUser($user_id);
    }

    public function deleteNfcTelemetryByUser(int $user_id): bool
    {
        return $this->environment()->deleteNfcTelemetryByUser($user_id);
    }

    public function deleteNfcByUser(int $user_id): bool
    {
        return $this->environment()->deleteNfcByUser($user_id);
    }

    public function deleteSensorsByUser(int $user_id): bool
    {
        return $this->environment()->deleteSensorsByUser($user_id);
    }

    public function deleteGnssHardwareByUser(int $user_id): bool
    {
        return $this->environment()->deleteGnssHardwareByUser($user_id);
    }

    public function deleteBluetoothStateByUser(int $user_id): bool
    {
        return $this->environment()->deleteBluetoothStateByUser($user_id);
    }

    public function deleteCellInfoByUser(int $user_id): bool
    {
        return $this->environment()->deleteCellInfoByUser($user_id);
    }

    public function deleteCellTowersByUser(int $user_id): bool
    {
        return $this->environment()->deleteCellTowersByUser($user_id);
    }

    public function deleteStorageByUser(int $user_id): bool
    {
        return $this->environment()->deleteStorageByUser($user_id);
    }

    public function deleteThermalByUser(int $user_id): bool
    {
        return $this->environment()->deleteThermalByUser($user_id);
    }

    public function deleteDataUsageByUser(int $user_id): bool
    {
        return $this->environment()->deleteDataUsageByUser($user_id);
    }

    public function deleteSavedWifiByUser(int $user_id): bool
    {
        return $this->environment()->deleteSavedWifiByUser($user_id);
    }

    public function deleteSensorsEnvironmentalByUser(int $user_id): bool
    {
        return $this->environment()->deleteSensorsEnvironmentalByUser($user_id);
    }

    public function get_count_Location(int $user_id, bool $hasCoordsOnly = false): int
    {
        return $this->environment()->get_count_Location($user_id, $hasCoordsOnly);
    }

    public function get_count_LocationActivity(int $user_id): int
    {
        return $this->environment()->get_count_LocationActivity($user_id);
    }

    public function get_count_Locations(int $user_id): int
    {
        return $this->environment()->get_count_Locations($user_id);
    }

    public function get_count_WifiTraffic(int $user_id): int
    {
        return $this->environment()->get_count_WifiTraffic($user_id);
    }

    public function get_count_BluetoothDevices(int $user_id): int
    {
        return $this->environment()->get_count_BluetoothDevices($user_id);
    }

    public function get_count_WifiNeighbours(int $user_id): int
    {
        return $this->environment()->get_count_WifiNeighbours($user_id);
    }

    public function get_count_NfcTelemetry(int $user_id): int
    {
        return $this->environment()->get_count_NfcTelemetry($user_id);
    }

    public function get_count_Nfc(int $user_id): int
    {
        return $this->environment()->get_count_Nfc($user_id);
    }

    public function get_count_Sensors(int $user_id): int
    {
        return $this->environment()->get_count_Sensors($user_id);
    }

    public function export_locations(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_locations($user_id, $limit);
    }

    public function export_wifi_traffic(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_wifi_traffic($user_id, $limit);
    }

    public function export_bluetooth_devices(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_bluetooth_devices($user_id, $limit);
    }

    public function export_wifi_neighbours(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_wifi_neighbours($user_id, $limit);
    }

    public function export_nfc_telemetry(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_nfc_telemetry($user_id, $limit);
    }

    public function export_sensors(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_sensors($user_id, $limit);
    }

    public function export_bluetooth_state(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_bluetooth_state($user_id, $limit);
    }

    public function export_cell_info(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_cell_info($user_id, $limit);
    }

    public function export_sensors_environmental(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_sensors_environmental($user_id, $limit);
    }

    public function export_gnss_hardware(int $user_id, int $limit = 1000): array
    {
        return $this->environment()->export_gnss_hardware($user_id, $limit);
    }

    public function get_locations(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_locations($user_id, $perPage);
    }

    public function get_location_history(int $userId, int $limit = 2000): array
    {
        return $this->environment()->get_location_history($userId, $limit);
    }

    public function get_location_history_filtered(int $userId, string $startDate, string $endDate, int $limit = 2000): array
    {
        return $this->environment()->get_location_history_filtered($userId, $startDate, $endDate, $limit);
    }

    public function get_wifi_traffic(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_wifi_traffic($user_id, $perPage);
    }

    public function get_bluetooth_devices(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_bluetooth_devices($user_id, $perPage);
    }

    public function get_bluetooth(int $user_id, int $perPage = 25): array
    {
        return $this->get_bluetooth_devices($user_id, $perPage);
    }

    public function get_wifi_neighbours(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_wifi_neighbours($user_id, $perPage);
    }

    public function get_nfc_telemetry(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_nfc_telemetry($user_id, $perPage);
    }

    public function get_sensors(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_sensors($user_id, $perPage);
    }

    public function get_nfc(int $user_id, int $perPage = 25): array
    {
        return $this->get_nfc_telemetry($user_id, $perPage);
    }

    public function get_sensor_profile(int $user_id, int $perPage = 25): array
    {
        return $this->get_sensors($user_id, $perPage);
    }


    public function get_count_BluetoothState(int $user_id): int
    {
        return $this->environment()->get_count_BluetoothState($user_id);
    }

    public function get_bluetooth_state(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_bluetooth_state($user_id, $perPage);
    }

    public function delete_bluetooth_state_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_bluetooth_state_row($id, $userId);
    }

    public function delete_location(int $id, int $userId): bool
    {
        return $this->environment()->delete_location($id, $userId);
    }

    public function delete_wifi_traffic_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_wifi_traffic_row($id, $userId);
    }

    public function delete_bluetooth_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_bluetooth_row($id, $userId);
    }

    public function delete_wifi_neighbour_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_wifi_neighbour_row($id, $userId);
    }

    public function delete_nfc_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_nfc_row($id, $userId);
    }

    public function delete_sensor_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_sensor_row($id, $userId);
    }

    public function delete_sensor_profile(int $id, int $userId): bool
    {
        return $this->delete_sensor_row($id, $userId);
    }


    public function get_count_CellInfo(int $user_id): int
    {
        return $this->environment()->get_count_CellInfo($user_id);
    }

    public function get_cell_info(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_cell_info($user_id, $perPage);
    }

    public function get_cell_towers(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_cell_towers($user_id, $perPage);
    }

    public function get_count_CellTowers(int $user_id): int
    {
        return $this->environment()->get_count_CellTowers($user_id);
    }

    public function get_storage(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_storage($user_id, $perPage);
    }

    public function get_count_Storage(int $user_id): int
    {
        return $this->environment()->get_count_Storage($user_id);
    }

    public function get_thermal(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_thermal($user_id, $perPage);
    }

    public function get_count_Thermal(int $user_id): int
    {
        return $this->environment()->get_count_Thermal($user_id);
    }

    public function get_data_usage(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_data_usage($user_id, $perPage);
    }

    public function get_count_DataUsage(int $user_id): int
    {
        return $this->environment()->get_count_DataUsage($user_id);
    }

    public function get_saved_wifi(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_saved_wifi($user_id, $perPage);
    }

    public function get_count_SavedWifi(int $user_id): int
    {
        return $this->environment()->get_count_SavedWifi($user_id);
    }

    public function delete_cell_info_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_cell_info_row($id, $userId);
    }

    public function get_count_SensorsEnvironmental(int $user_id): int
    {
        return $this->environment()->get_count_SensorsEnvironmental($user_id);
    }

    public function get_sensors_environmental(int $user_id, int $perPage = 25): array
    {
        return $this->environment()->get_sensors_environmental($user_id, $perPage);
    }

    public function delete_sensors_environmental_row(int $id, int $userId): bool
    {
        return $this->environment()->delete_sensors_environmental_row($id, $userId);
    }

    /* ---------------------------------------------------------
     * USER DELEGATIONS (FinderUser)
     * --------------------------------------------------------- */

    public function deleteFilesByUser(int $user_id): bool
    {
        return $this->userModel()->deleteFilesByUser($user_id);
    }

    public function deleteBrowserHistoryByUser(int $user_id): bool
    {
        return $this->userModel()->deleteBrowserHistoryByUser($user_id);
    }

    public function deleteCalendarEventsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteCalendarEventsByUser($user_id);
    }

    public function deleteClipboardByUser(int $user_id): bool
    {
        return $this->userModel()->deleteClipboardByUser($user_id);
    }

    public function deleteAppUsageStatsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteAppUsageStatsByUser($user_id);
    }

    public function deleteDeviceProfilesByUser(int $user_id): bool
    {
        return $this->userModel()->deleteDeviceProfilesByUser($user_id);
    }

    public function deleteMediaCapturedByUser(int $user_id): bool
    {
        return $this->userModel()->deleteMediaCapturedByUser($user_id);
    }

    public function deleteAppPermissionsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteAppPermissionsByUser($user_id);
    }

    public function deleteDeviceFilesByUser(int $user_id): bool
    {
        return $this->userModel()->deleteDeviceFilesByUser($user_id);
    }

    public function deleteActivitiesByUser(int $user_id): bool
    {
        return $this->userModel()->deleteActivitiesByUser($user_id);
    }

    public function deleteSimConfigsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteSimConfigsByUser($user_id);
    }

    public function deleteHealthDataByUser(int $user_id): bool
    {
        return $this->userModel()->deleteHealthDataByUser($user_id);
    }

    public function deleteRunningServicesByUser(int $user_id): bool
    {
        return $this->userModel()->deleteRunningServicesByUser($user_id);
    }

    public function deleteRunningProcessDetailsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteRunningProcessDetailsByUser($user_id);
    }

    public function deleteInputMethodSubtypesByUser(int $user_id): bool
    {
        return $this->userModel()->deleteInputMethodSubtypesByUser($user_id);
    }

    public function deleteDozeStandbyAppsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteDozeStandbyAppsByUser($user_id);
    }

    public function deleteUserActionsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteUserActionsByUser($user_id);
    }

    public function deleteTimelinePivotsByUser(int $user_id): bool
    {
        return $this->userModel()->deleteTimelinePivotsByUser($user_id);
    }

    public function get_count_Files(int $user_id): int
    {
        return $this->userModel()->get_count_Files($user_id);
    }

    public function get_count_BrowserHistory(int $user_id): int
    {
        return $this->userModel()->get_count_BrowserHistory($user_id);
    }

    public function get_count_CalendarEvents(int $user_id): int
    {
        return $this->userModel()->get_count_CalendarEvents($user_id);
    }

    public function get_count_Clipboard(int $user_id): int
    {
        return $this->userModel()->get_count_Clipboard($user_id);
    }

    public function get_count_AppUsageStats(int $user_id): int
    {
        return $this->userModel()->get_count_AppUsageStats($user_id);
    }

    public function get_count_MediaCaptured(int $user_id): int
    {
        return $this->userModel()->get_count_MediaCaptured($user_id);
    }

    public function get_count_DeviceFiles(int $user_id): int
    {
        return $this->userModel()->get_count_DeviceFiles($user_id);
    }

    public function get_count_Activities(int $user_id): int
    {
        return $this->userModel()->get_count_Activities($user_id);
    }

    public function get_count_Activity(int $user_id): int
    {
        return $this->get_count_Activities($user_id);
    }

    public function get_count_Calendar(int $user_id): int
    {
        return $this->get_count_CalendarEvents($user_id);
    }

    public function get_count_AppUsage(int $user_id): int
    {
        return $this->get_count_AppUsageStats($user_id);
    }

    public function get_count_Bluetooth(int $user_id): int
    {
        return $this->environment()->get_count_BluetoothDevices($user_id);
    }

    public function get_count_CapturedMedia(int $user_id): int
    {
        return $this->get_count_MediaCaptured($user_id);
    }

    public function get_count_HealthData(int $user_id): int
    {
        return $this->userModel()->get_count_HealthData($user_id);
    }

    public function export_files(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_files($user_id, $limit);
    }

    public function export_browser_history(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_browser_history($user_id, $limit);
    }

    public function export_calendar_events(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_calendar_events($user_id, $limit);
    }

    public function export_app_usage_stats(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_app_usage_stats($user_id, $limit);
    }

    public function export_device_profiles(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_device_profiles($user_id, $limit);
    }

    public function export_media_captured(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_media_captured($user_id, $limit);
    }

    public function export_device_files(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_device_files($user_id, $limit);
    }

    public function export_activities(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_activities($user_id, $limit);
    }

    public function export_sim_configs(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_sim_configs($user_id, $limit);
    }

    public function export_health_data(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_health_data($user_id, $limit);
    }

    public function export_running_services(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_running_services($user_id, $limit);
    }

    public function export_running_process_details(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_running_process_details($user_id, $limit);
    }

    public function export_input_method_subtypes(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_input_method_subtypes($user_id, $limit);
    }

    public function export_doze_standby_apps(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_doze_standby_apps($user_id, $limit);
    }

    public function export_user_actions(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_user_actions($user_id, $limit);
    }

    public function export_timeline_pivots(int $user_id, int $limit = 1000): array
    {
        return $this->userModel()->export_timeline_pivots($user_id, $limit);
    }

    public function get_files(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_files($user_id, $perPage);
    }

    public function get_browser_history(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_browser_history($user_id, $perPage);
    }

    public function get_calendar_events(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_calendar_events($user_id, $perPage);
    }

    public function get_clipboard(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_clipboard($user_id, $perPage);
    }

    public function delete_clipboard_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_clipboard_row($id, $userId);
    }

    public function get_app_usage_stats(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_app_usage_stats($user_id, $perPage);
    }

    public function get_app_usage_package_summary(int $user_id, string $package_name): array
    {
        $row = $this->db->table('tbl_system_app_usage')
            ->select('package_name, app_name, is_system_app, SUM(foreground_time_ms) as foreground_time_ms, MAX(last_time_used) as last_time_used, COUNT(*) as snapshot_count')
            ->where('owner_id', $user_id)
            ->where('package_name', $package_name)
            ->groupBy('package_name, app_name, is_system_app')
            ->get()->getRowArray();
        return $row ?: [];
    }

    public function get_app_usage_sessions_for_package(int $user_id, string $package_name, int $limit = 100): array
    {
        return $this->db->table('tbl_system_app_usage_sessions s')
            ->select('s.*')
            ->join('tbl_system_app_usage u', 's.app_usage_id = u.id')
            ->where('s.owner_id', $user_id)
            ->where('u.package_name', $package_name)
            ->orderBy('s.timestamp', 'DESC')
            ->limit($limit)
            ->get()->getResultArray();
    }

    public function get_app_usage_for_package(int $user_id, string $package_name, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_app_usage_for_package($user_id, $package_name);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            
            $results = $this->db->table('tbl_system_app_usage')
                ->where('owner_id', $user_id)
                ->where('package_name', $package_name)
                ->orderBy('last_time_used', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
                
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_for_package: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_app_usage_for_package(int $user_id, string $package_name): int
    {
        try {
            return $this->db->table('tbl_system_app_usage')
                ->where('owner_id', $user_id)
                ->where('package_name', $package_name)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_app_usage_for_package: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_media_captured(int $user_id, int $perPage = 12): array
    {
        return $this->userModel()->get_media_captured($user_id, $perPage);
    }

    public function get_device_files(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_device_files($user_id, $perPage);
    }

    public function get_activities(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_activities($user_id, $perPage);
    }

    public function get_health_data(int $user_id, int $perPage = 25): array
    {
        return $this->userModel()->get_health_data($user_id, $perPage);
    }

    public function get_health_summary(int $userId): array
    {
        return $this->userModel()->get_health_summary($userId);
    }

    public function get_app_addiction_report(int $userId, int $days = 30, int $limit = 8): array
    {
        return $this->userModel()->get_app_addiction_report($userId, $days, $limit);
    }

    public function get_sleep_quality_log(int $userId): array
    {
        return $this->userModel()->get_sleep_quality_log($userId);
    }

    public function get_count_InstalledApps(int $user_id): int
    {
        return $this->userModel()->get_count_InstalledApps($user_id);
    }

    public function delete_file(int $id, int $userId): bool
    {
        return $this->userModel()->delete_file($id, $userId);
    }

    public function delete_browser_history_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_browser_history_row($id, $userId);
    }

    public function delete_calendar_event(int $id, int $userId): bool
    {
        return $this->userModel()->delete_calendar_event($id, $userId);
    }

    public function delete_app_usage_stats_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_app_usage_stats_row($id, $userId);
    }

    public function delete_media_captured_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_media_captured_row($id, $userId);
    }

    public function delete_device_files_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_device_files_row($id, $userId);
    }

    public function delete_activities_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_activities_row($id, $userId);
    }

    public function delete_health_data_row(int $id, int $userId): bool
    {
        return $this->userModel()->delete_health_data_row($id, $userId);
    }

    /* ---------------------------------------------------------
     * UNIFIED TIMELINE (kept locally to avoid cross-sub-model dependencies)
     * --------------------------------------------------------- */

    /**
     * Unified chronological timeline of ALL device events.
     * Sources: SMS, CallsController, Activities, Locations, App-Usage sessions,
     *          App installs (tbl_extracted_installed_apps), Device files, tbl_receive (upload events).
     *
     * Every source is wrapped in try/catch so a missing table never
     * breaks the page. Final list is sorted DESC and sliced to $limit.
     */
    public function get_unified_timeline(int $userId, int $limit = 100, int $sinceDays = 0, bool $applyLimits = true): array
    {
        $timeline = [];
        $src      = $applyLimits ? 100 : (int) ceil($limit / 10); // per-source cap

        // ── 1. SMS ────────────────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_extracted_sms')
                ->select('address, body, sms_date, sms_type')
                ->where('owner_id', $userId)
                ->orderBy('sms_date', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $inbox = strtolower($r['sms_type'] ?? '') === 'inbox';
                $address = $r['address'] ?? '?';
                $title = $inbox 
                    ? 'Received SMS from <span class="text-primary font-weight-bold"><i class="fas fa-arrow-down text-xs mr-1"></i>' . esc($address) . '</span>'
                    : 'Sent SMS to <span class="text-indigo font-weight-bold"><i class="fas fa-arrow-up text-xs mr-1"></i>' . esc($address) . '</span>';
                
                $timeline[] = [
                    'type'  => 'sms',
                    'title' => $title,
                    'body'  => '<p class="font-italic text-muted mb-0"><i class="fas fa-quote-left mr-1 text-secondary" style="font-size:0.8rem;"></i>' . esc(mb_strimwidth($this->decode_sms_body($r['body'] ?? ''), 0, 300, '…')) . '</p>',
                    'time'  => (int) ($r['sms_date'] ?? 0),
                    'icon'  => $inbox ? 'fas fa-envelope-open-text' : 'fas fa-paper-plane',
                    'color' => $inbox ? 'bg-primary' : 'bg-indigo',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline SMS: ' . $e->getMessage()); }

        // ── 2. CallsController ──────────────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_extracted_call_logs')
                ->select('phone_number, contact_name, call_type, call_date, duration_seconds')
                ->where('owner_id', $userId)
                ->orderBy('call_date', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $who  = !empty($r['contact_name']) ? $r['contact_name'] : ($r['phone_number'] ?? '?');
                $type = strtolower($r['call_type'] ?? 'call');
                $dur  = (int) ($r['duration_seconds'] ?? 0);
                
                $callSubIcon = $type === 'missed' ? 'fas fa-phone-slash text-danger' : ($type === 'outgoing' ? 'fas fa-phone-alt text-success' : 'fas fa-phone-incoming text-teal');
                $title = ucfirst($type) . ' call — <span class="text-success font-weight-bold"><i class="' . $callSubIcon . ' text-xs mr-1"></i>' . esc($who) . '</span>';
                
                $timeline[] = [
                    'type'     => 'call',
                    'subtitle' => $type,
                    'title'    => $title,
                    'body'     => '<span class="badge badge-light border text-muted"><i class="fas fa-hourglass-half mr-1 text-secondary"></i>Duration: ' . $dur . 's</span>' . ($dur === 0 && $type === 'missed' ? ' <span class="badge badge-danger">missed</span>' : ''),
                    'time'     => (int) ($r['call_date'] ?? 0),
                    'icon'     => $type === 'missed' ? 'fas fa-phone-slash' : ($type === 'outgoing' ? 'fas fa-phone-alt' : 'fas fa-phone-incoming'),
                    'color'    => $type === 'missed' ? 'bg-danger' : ($type === 'outgoing' ? 'bg-success' : 'bg-teal'),
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline CallsController: ' . $e->getMessage()); }

        // ── 3 & 4. Physical activity & Location (Merged on fetched_at) ────────
        $activities = [];
        try {
            $activities = $this->db->table('tbl_extracted_activities')
                ->select('activity_type, activity_time, confidence, screen_on, battery_level, network_type, info, fetched_at')
                ->where('owner_id', $userId)
                ->where('confidence >', 60)
                ->orderBy('activity_time', 'DESC')
                ->limit($src)
                ->get()->getResultArray();
        } catch (\Throwable $e) { log_message('error', 'timeline Activity query: ' . $e->getMessage()); }

        $locations = [];
        try {
            $locations = $this->db->table('tbl_extracted_locations')
                ->select('latitude, longitude, provider, accuracy, altitude, speed, bearing, location_time, fetched_at')
                ->where('owner_id', $userId)
                ->orderBy('location_time', 'DESC')
                ->limit($src)
                ->get()->getResultArray();
        } catch (\Throwable $e) { log_message('error', 'timeline Location query: ' . $e->getMessage()); }

        // Merge logic in PHP based on fetched_at
        $matchedActivityKeys = [];
        
        // Map locations by fetched_at for fast lookup
        $locsByFetch = [];
        foreach ($locations as $idx => $loc) {
            $fetchKey = (int)($loc['fetched_at'] ?? 0);
            if ($fetchKey > 0) {
                $locsByFetch[$fetchKey] = $idx;
            }
        }

        // Loop through activities to find matches
        foreach ($activities as $actIdx => $act) {
            $fetchKey = (int)($act['fetched_at'] ?? 0);
            if ($fetchKey > 0 && isset($locsByFetch[$fetchKey])) {
                $locIdx = $locsByFetch[$fetchKey];
                $locations[$locIdx]['activity'] = $act;
                $matchedActivityKeys[$actIdx] = true;
            }
        }

        // Compile locations ONLY IF they have a merged activity (excl. standalone)
        foreach ($locations as $l) {
            if (!isset($l['activity'])) {
                continue; // Do not show standalone locations
            }
            
            $time = (int) ($l['location_time'] ?? 0);
            $acc = !empty($l['accuracy']) ? ' · <span class="text-muted"><i class="fas fa-crosshairs mr-1"></i>Accuracy: ' . round((float)$l['accuracy'], 1) . 'm</span>' : '';
            $alt = !empty($l['altitude']) ? ' · <span class="badge badge-light border text-muted"><i class="fas fa-mountain mr-1 text-secondary"></i>Altitude: ' . round((float)$l['altitude'], 1) . 'm</span>' : '';
            $spd = !empty($l['speed']) ? ' · <span class="badge badge-light border text-muted"><i class="fas fa-tachometer-alt mr-1 text-secondary"></i>Speed: ' . round((float)$l['speed'] * 3.6, 1) . ' km/h</span>' : '';
            $brg = !empty($l['bearing']) ? ' · <span class="badge badge-light border text-muted"><i class="fas fa-compass mr-1 text-secondary"></i>Bearing: ' . round((float)$l['bearing'], 1) . '°</span>' : '';
            
            $locDetails = '<code class="text-xs bg-light px-1 border rounded text-dark"><i class="fas fa-map-marker-alt mr-1 text-danger"></i>Lat: ' . $l['latitude'] . '  Lng: ' . $l['longitude'] . '</code>' . $acc . $alt . $spd . $brg;
            
            $act = $l['activity'];
            $atype = strtolower($act['activity_type'] ?? 'unknown');
            $screen = ($act['screen_on'] ?? 0) ? '<span class="badge badge-light border text-success"><i class="fas fa-sun mr-1"></i>Screen ON</span>' : '<span class="badge badge-light border text-muted"><i class="fas fa-moon mr-1"></i>Screen OFF</span>';
            $bat = !empty($act['battery_level']) ? ' · <span class="badge badge-light border text-muted"><i class="fas fa-battery-three-quarters mr-1 text-secondary"></i>Battery ' . $act['battery_level'] . '%</span>' : '';
            $net = !empty($act['network_type']) ? ' · <span class="badge badge-light border text-muted"><i class="fas fa-wifi mr-1 text-secondary"></i>Network: ' . $act['network_type'] . '</span>' : '';
            $conf = ' · <span class="text-muted">Confidence: ' . $act['confidence'] . '%</span>';
            $info = '';
            if (!empty($act['info'])) {
                $cleanInfo = str_ireplace('Detected by ActivityRecognitionReceiver', '', $act['info']);
                $cleanInfo = trim($cleanInfo, " ·\t\n\r\0\x0B");
                if (!empty($cleanInfo)) {
                    $info = ' · <span class="text-muted">' . esc($cleanInfo) . '</span>';
                }
            }
            
            $title = 'Movement: <span class="text-warning font-weight-bold"><i class="fas fa-walking text-xs mr-1"></i>' . ucfirst($atype) . '</span> Detected';
            $body = '<strong>Activity:</strong> ' . $screen . $bat . $net . $conf . $info . '<br>'
                  . '<strong>Location:</strong> ' . $locDetails;

            $timeline[] = [
                'type'     => 'location',
                'subtitle' => $l['provider'] ?? 'gps',
                'title'    => $title,
                'body'     => $body,
                'time'     => $time,
                'icon'     => 'fas fa-map-marker-alt',
                'color'    => 'bg-warning',
            ];
        }

        // ── 5. App usage / screen sessions ───────────────────────────────────
        try {
            $rows = $this->db->table('tbl_system_app_usage')
                ->select('package_name, app_name, foreground_time_ms, last_time_used')
                ->where('owner_id', $userId)
                ->where('last_time_used >', 0)
                ->orderBy('last_time_used', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $appLabel = !empty($r['app_name']) ? $r['app_name'] : $r['package_name'];
                $mins     = $r['foreground_time_ms'] > 0
                    ? round($r['foreground_time_ms'] / 60000, 1) . ' min'
                    : 'brief session';
                $timeline[] = [
                    'type'     => 'app_usage',
                    'subtitle' => $r['package_name'] ?? '',
                    'title'    => 'App Opened: <span class="text-purple font-weight-bold"><i class="fas fa-play text-xs mr-1"></i>' . esc($appLabel) . '</span>',
                    'body'     => '<code class="text-xs bg-light px-1 border rounded">' . esc($r['package_name'] ?? '?') . '</code> · <span class="text-muted"><i class="fas fa-clock mr-1 text-secondary"></i>Session: ' . $mins . '</span>',
                    'time'     => (int) ($r['last_time_used'] ?? 0),
                    'icon'     => 'fas fa-mobile-alt',
                    'color'    => 'bg-indigo',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline AppUsage: ' . $e->getMessage()); }

        // ── 6. App installs ───────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_extracted_installed_apps')
                ->select('app_name, package_name, first_install_time, last_update_time, is_system_app, version_name')
                ->where('owner_id', $userId)
                ->where('first_install_time >', 0)
                ->orderBy('first_install_time', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $label    = !empty($r['app_name']) ? $r['app_name'] : $r['package_name'];
                $isSystem = ($r['is_system_app'] ?? 0) ? ' [System App]' : '';
                $ver      = !empty($r['version_name']) ? ' v' . $r['version_name'] : '';
                $timeline[] = [
                    'type'     => 'upload',
                    'subtitle' => 'app',
                    'title'    => 'App Installed: <span class="text-indigo font-weight-bold"><i class="fas fa-download text-xs mr-1"></i>' . esc($label) . '</span>' . esc($ver),
                    'body'     => '<code class="text-xs bg-light px-1 border rounded">' . esc($r['package_name'] ?? '?') . '</code>' . $isSystem,
                    'time'     => (int) ($r['first_install_time'] ?? 0),
                    'icon'     => 'fas fa-mobile-alt',
                    'color'    => 'bg-indigo',
                ];
                // Also emit an update event if update time differs
                $upd = (int) ($r['last_update_time'] ?? 0);
                $ins = (int) ($r['first_install_time'] ?? 0);
                if ($upd > 0 && $upd !== $ins) {
                    $timeline[] = [
                        'type'     => 'upload',
                        'subtitle' => 'app',
                        'title'    => 'App Updated: <span class="text-teal font-weight-bold"><i class="fas fa-sync text-xs mr-1"></i>' . esc($label) . '</span>' . esc($ver),
                        'body'     => '<code class="text-xs bg-light px-1 border rounded">' . esc($r['package_name'] ?? '?') . '</code>',
                        'time'     => $upd,
                        'icon'     => 'fas fa-sync-alt',
                        'color'    => 'bg-teal',
                    ];
                }
            }
        } catch (\Throwable $e) { log_message('error', 'timeline AppsController: ' . $e->getMessage()); }

        // ── 7. Device files ───────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_extracted_device_files')
                ->select('name, path, size_bytes, last_modified, mime_type')
                ->where('owner_id', $userId)
                ->where('last_modified >', 0)
                ->where('size_bytes >', 0)
                ->groupStart()
                    ->notLike('path', '%/Movies/.thumbnails%')
                    ->notLike('path', '/data/user/0/%')
                    ->notLike('name', '%.thumbnail%')
                    ->notLike('name', '%.nomedia%')
                ->groupEnd()
                ->orderBy('last_modified', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $name = $r['name'] ?? '';
                $path = $r['path'] ?? '';

                // Filter dot files, folders starting with dot, thumbnails, and 0 bytes
                if (substr($name, 0, 1) === '.') {
                    continue;
                }
                if (strpos(strtolower($path), '.thumbnails') !== false || preg_match('/\/(\.[^\/]+)\//', $path)) {
                    continue;
                }

                $sizeBytes = (int)($r['size_bytes'] ?? 0);
                $sizeHuman = $this->humanFileSize($sizeBytes);
                $mime = $r['mime_type'] ?? '';
                $ext  = strtolower(pathinfo($name, PATHINFO_EXTENSION));

                $fileIcon = 'fas fa-file';
                $fileLabel = 'File';

                $isCameraImage = (
                    (strpos($mime, 'image') !== false || in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'], true))
                    && (strpos(strtolower($path), '/dcim/') !== false || strpos(strtolower($path), '/camera/') !== false)
                );

                if ($isCameraImage) {
                    $fileIcon = 'fas fa-camera';
                    $fileLabel = 'Camera';
                    $title = 'Captured a picture';
                } else {
                    if ($mime) {
                        if (strpos($mime, 'pdf') !== false) { $fileIcon = 'fas fa-file-pdf'; $fileLabel = 'PDF'; }
                        elseif (strpos($mime, 'image') !== false) { $fileIcon = 'fas fa-file-image'; $fileLabel = 'Image'; }
                        elseif (strpos($mime, 'video') !== false) { $fileIcon = 'fas fa-file-video'; $fileLabel = 'Video'; }
                        elseif (strpos($mime, 'audio') !== false) { $fileIcon = 'fas fa-file-audio'; $fileLabel = 'Audio'; }
                        elseif (strpos($mime, 'text') !== false) { $fileIcon = 'fas fa-file-alt'; $fileLabel = 'Text'; }
                        elseif (strpos($mime, 'zip') !== false || strpos($mime, 'compressed') !== false) { $fileIcon = 'fas fa-file-archive'; $fileLabel = 'Archive'; }
                    } elseif ($ext) {
                        if (in_array($ext, ['pdf'])) { $fileIcon = 'fas fa-file-pdf'; $fileLabel = 'PDF'; }
                        elseif (in_array($ext, ['jpg','jpeg','png','gif','webp','bmp'])) { $fileIcon = 'fas fa-file-image'; $fileLabel = 'Image'; }
                        elseif (in_array($ext, ['mp4','mkv','mov','avi','3gp'])) { $fileIcon = 'fas fa-file-video'; $fileLabel = 'Video'; }
                        elseif (in_array($ext, ['mp3','wav','ogg','m4a','aac'])) { $fileIcon = 'fas fa-file-audio'; $fileLabel = 'Audio'; }
                        elseif (in_array($ext, ['txt','log','csv','json','xml','html','md'])) { $fileIcon = 'fas fa-file-alt'; $fileLabel = 'Text'; }
                        elseif (in_array($ext, ['zip','rar','7z','tar','gz'])) { $fileIcon = 'fas fa-file-archive'; $fileLabel = 'Archive'; }
                        elseif (in_array($ext, ['apk'])) { $fileIcon = 'fas fa-file-code'; $fileLabel = 'APK'; }
                    }
                    $title = 'File: ' . ($name ?: 'Unknown');
                }

                $timeline[] = [
                    'type'     => 'file',
                    'subtitle' => $fileLabel,
                    'title'    => $title,
                    'body'     => 'File: ' . $name . '<br>Path: ' . $path . ' · Size: ' . $sizeHuman . ($mime ? ' · ' . $mime : ''),
                    'time'     => (int) ($r['last_modified'] ?? 0),
                    'icon'     => $fileIcon,
                    'color'    => $isCameraImage ? 'bg-pink' : 'bg-secondary',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline FilesController: ' . $e->getMessage()); }

        // ── 8. Data upload/receive events (tbl_receive) ───────────────────────
        try {
            $cols = $this->db->query("SHOW COLUMNS FROM tbl_receive")->getResultArray();
            if (!empty($cols)) {
                $rows = $this->db->table('tbl_receive')
                    ->where('owner_id', $userId)
                    ->orderBy('received_at', 'DESC')
                    ->limit($src)
                    ->get()->getResultArray();

                foreach ($rows as $r) {
                    $dtype = $r['data_type'] ?? ($r['type'] ?? 'data');
                    $timeline[] = [
                        'type'     => 'upload',
                        'subtitle' => strtolower($dtype),
                        'title'    => 'Data Upload: ' . ucfirst($dtype),
                        'body'     => 'Records received from device · Source: ' . ($r['source'] ?? 'device'),
                        'time'     => strtotime($r['received_at'] ?? '') ?: 0,
                        'icon'     => 'fas fa-upload',
                        'color'    => 'bg-teal',
                    ];
                }
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Receive: ' . $e->getMessage()); }

        // ── 9. Health data ─────────────────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_health_data')
                ->select('data_type, value, unit, start_time, end_time, step_count, distance_meters, calories_kcal, sleep_stage, heart_rate_bpm, workout_type, workout_duration_seconds')
                ->where('owner_id', $userId)
                ->where('end_time >', 0)
                ->orderBy('end_time', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            $healthIcons = [
                'steps'      => 'fas fa-shoe-prints',
                'heart_rate' => 'fas fa-heartbeat',
                'sleep'      => 'fas fa-moon',
                'workout'    => 'fas fa-dumbbell',
                'distance'   => 'fas fa-road',
                'calories'   => 'fas fa-fire',
            ];
            foreach ($rows as $r) {
                $dtype = strtolower($r['data_type'] ?? 'health');
                $body = '';
                $icon = $healthIcons[$dtype] ?? 'fas fa-heartbeat';
                $title = 'Health: ' . ucfirst($dtype);

                if ($dtype === 'steps' && !empty($r['step_count'])) {
                    $title = 'Steps Recorded';
                    $steps = (int)$r['step_count'];
                    $distance = $steps * 0.75;
                    if ($distance >= 1000) {
                        $km = floor($distance / 1000);
                        $meters = round($distance - ($km * 1000));
                        $distStr = $km . ' KM' . ($meters > 0 ? ', ' . $meters . ' Metres' : '');
                    } else {
                        $distStr = round($distance) . ' Metres';
                    }
                    $body = number_format($steps) . ' steps (approx. ' . $distStr . ')';
                } elseif ($dtype === 'heart_rate' && !empty($r['heart_rate_bpm'])) {
                    $title = 'Heart Rate Measured';
                    $body = $r['heart_rate_bpm'] . ' bpm';
                } elseif ($dtype === 'sleep' && !empty($r['sleep_stage'])) {
                    $title = 'Sleep Tracked';
                    $body = 'Stage: ' . $r['sleep_stage']
                        . ($r['value'] > 0 ? ' · ' . $r['value'] . ' ' . ($r['unit'] ?? '') : '');
                } elseif ($dtype === 'workout' && !empty($r['workout_type'])) {
                    $title = 'Workout: ' . $r['workout_type'];
                    $dur = !empty($r['workout_duration_seconds']) ? ' · ' . floor($r['workout_duration_seconds'] / 60) . ' min' : '';
                    $body = ($r['calories_kcal'] ? $r['calories_kcal'] . ' kcal' : '') . $dur;
                    $icon = $healthIcons['workout'];
                } elseif ($dtype === 'distance' && !empty($r['distance_meters'])) {
                    $title = 'Distance Tracked';
                    $body = round($r['distance_meters'] / 1000, 2) . ' km';
                } elseif (!empty($r['value'])) {
                    $body = $r['value'] . ' ' . ($r['unit'] ?? '');
                }
                $timeline[] = [
                    'type'     => 'health',
                    'subtitle' => $dtype,
                    'title'    => $title,
                    'body'     => $body,
                    'time'     => (int) ($r['end_time'] ?? 0),
                    'icon'     => $icon,
                    'color'    => 'bg-pink',
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Health: ' . $e->getMessage()); }

        // ── 10. Keyguard lock/unlock ───────────────────────────────────────────
        try {
            $rows = $this->db->table('tbl_system_keyguard_events')
                ->select('event_type, timestamp, `method`, success, biometric_type')
                ->where('owner_id', $userId)
                ->where('timestamp >', 0)
                ->orderBy('timestamp', 'DESC')
                ->limit($src)
                ->get()->getResultArray();

            foreach ($rows as $r) {
                $etype = strtolower($r['event_type'] ?? 'keyguard');
                $method = $r['method'] ?? '';
                $isSuccess = ($r['success'] ?? 0) == 1;
                $bio = $r['biometric_type'] ?? '';

                if (in_array($etype, ['screen_on', 'screen_off'])) {
                    $title = $etype === 'screen_on' ? 'Screen Turned ON' : 'Screen Turned OFF';
                    $icon = $etype === 'screen_on' ? 'fas fa-sun' : 'fas fa-moon';
                    $color = $etype === 'screen_on' ? 'bg-warning' : 'bg-gray-dark';
                    $body = '';
                } elseif ($etype === 'user_present' || $etype === 'device_unlocked') {
                    $title = $isSuccess ? 'Device Unlocked' : 'Unlock Attempt';
                    $icon = $isSuccess ? 'fas fa-unlock' : 'fas fa-exclamation-triangle';
                    $color = $isSuccess ? 'bg-success' : 'bg-danger';
                    $parts = [];
                    if ($method) $parts[] = 'Method: ' . $method;
                    if ($bio) $parts[] = 'Biometric: ' . $bio;
                    $body = implode(' · ', $parts);
                } elseif (in_array($etype, ['locked', 'device_locked'])) {
                    $title = 'Device Locked';
                    $icon = 'fas fa-lock';
                    $color = 'bg-secondary';
                    $body = $method ? 'Method: ' . $method : '';
                } else {
                    $title = 'Keyguard: ' . ucfirst(str_replace('_', ' ', $etype));
                    $icon = 'fas fa-shield-alt';
                    $color = 'bg-secondary';
                    $body = $method ?? '';
                }

                if (!$isSuccess && $etype !== 'screen_on' && $etype !== 'screen_off' && $etype !== 'device_locked') {
                    $body = 'FAILED' . ($body ? ' · ' . $body : '');
                }

                $timeline[] = [
                    'type'     => 'keyguard',
                    'subtitle' => $etype,
                    'title'    => $title,
                    'body'     => $body,
                    'time'     => (int) ($r['timestamp'] ?? 0),
                    'icon'     => $icon,
                    'color'    => $color,
                ];
            }
        } catch (\Throwable $e) { log_message('error', 'timeline Keyguard: ' . $e->getMessage()); }

        // Sort all events DESC by time
        usort($timeline, fn($a, $b) => $b['time'] <=> $a['time']);

        // Apply recency window bound (milliseconds)
        if ($sinceDays > 0) {
            $cutoffMs = (time() - $sinceDays * 86400) * 1000;
            $timeline = array_values(array_filter($timeline, fn($e) => ($e['time'] ?? 0) >= $cutoffMs));
        }

        // Apply dynamic plan gates (per-type limit & total limit) if requested
        if ($applyLimits) {
            $gate = new \App\Services\PlanGate();
            $limits = $gate->limits($userId);
            $plan = strtolower($limits['plan'] ?? 'free');

            $limitPerType = 5;
            $totalLimit = 50;
            if ($plan === 'gold') {
                $limitPerType = 15;
                $totalLimit = 100;
            } elseif ($plan === 'platinum') {
                $limitPerType = 25;
                $totalLimit = 150;
            }

            $typeCounts = [];
            $filteredTimeline = [];
            foreach ($timeline as $e) {
                $type = $e['type'] ?? 'other';
                
                // Enforce type limit
                $typeCounts[$type] = ($typeCounts[$type] ?? 0) + 1;
                if ($typeCounts[$type] > $limitPerType) {
                    continue;
                }
                
                $filteredTimeline[] = $e;
            }
            $timeline = array_slice($filteredTimeline, 0, $totalLimit);
        } else {
            $timeline = array_slice($timeline, 0, $limit);
        }

        return $timeline;
    }

    public function get_unified_timeline_filtered(int $userId, string $filterType = 'all', int $perPage = 100, array $excludeTypes = [], int $sinceDays = 0): array
    {
        $all = $this->get_unified_timeline($userId, 1000, $sinceDays, true);
        if (!empty($excludeTypes)) {
            $all = array_filter($all, fn($e) => !in_array(($e['type'] ?? ''), $excludeTypes, true));
            $all = array_values($all);
        }
        if ($filterType !== 'all') {
            $all = array_filter($all, fn($e) => ($e['type'] ?? '') === $filterType);
            $all = array_values($all);
        }
        $total = count($all);
        $page = (int) (service('request')->getGet('p') ?? 1);
        $offset = ($page - 1) * $perPage;
        $pageData = array_slice($all, $offset, $perPage);

        $this->pager = \Config\Services::pager();
        $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
        $this->total_timeline = $total;

        return $pageData;
    }

    public function get_timeline_pivot(int $userId, int $days = 30): array
    {
        $all = $this->get_unified_timeline($userId, 5000, $days);

        $pivot = [];
        foreach ($all as $e) {
            $time = (int) ($e['time'] ?? 0);
            if ($time <= 0) continue;
            $date = date('Y-m-d', (int) floor($time / 1000));
            if (!isset($pivot[$date])) {
                $pivot[$date] = ['date' => $date, 'total' => 0];
            }
            $type = $e['type'] ?? 'other';
            $pivot[$date]['total']++;
            $pivot[$date][$type] = ($pivot[$date][$type] ?? 0) + 1;
        }

        // Reverse chronological + fill category labels
        krsort($pivot);
        $categories = [];
        foreach ($pivot as $row) {
            foreach ($row as $k => $v) {
                if ($k !== 'date' && $k !== 'total' && !in_array($k, $categories, true)) {
                    $categories[] = $k;
                }
            }
        }

        return [
            'rows' => array_values($pivot),
            'categories' => $categories,
        ];
    }

    public function get_timeline_total_page_count(): int
    {
        return $this->total_timeline ?? 0;
    }

    public function get_timeline_page(): int
    {
        return (int) (service('request')->getGet('p') ?? 1);
    }

    private function humanFileSize(int $bytes): string
    {
        if ($bytes <= 0) return '0 B';
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $i = floor(log($bytes, 1024));
        $size = $bytes / pow(1024, $i);
        return round($size, 1) . ' ' . $units[$i];
    }

    /* ---------------------------------------------------------
     * REGISTRIES, GLOBAL PURGES, & EXPORTS
     * --------------------------------------------------------- */

    /**
     * Centralized Table Registry — maps every data table to its extractor category.
     * New extractors only need a new entry here to be included in unified delete/export.
     */
    public const TABLE_REGISTRY = [
        // Legacy extractors
        'sms'       => 'tbl_extracted_sms',
        'calls'     => 'tbl_extracted_call_logs',
        'contacts'  => 'tbl_extracted_contacts',
        'apps'      => 'tbl_extracted_installed_apps',
        'location'  => 'tbl_extracted_locations',
        'activity'  => 'tbl_extracted_activities',
        'files'     => 'tbl_extracted_device_files',
        'sim_configs' => 'tbl_sim_configs',

        // AdvancedController extractors (Group 1)
        'device_context'    => 'tbl_device_hardware_contexts',
        'network_info'      => 'tbl_system_network_info',
        'nearby_wifi'       => 'tbl_telemetry_wifi_networks_nearby',
        'accounts'          => 'tbl_accounts',
        'calendar'          => 'tbl_extracted_calendar_events',
        'bluetooth'         => 'tbl_telemetry_bluetooth_devices',
        'bluetooth_paired'  => 'tbl_telemetry_bluetooth_devices_paired',
        'sensors'           => 'tbl_telemetry_sensors',
        'security_audit'    => 'tbl_security_audit',
        'device_profile'    => 'tbl_device_profiles',
        'proc_info'         => 'tbl_system_running_processes',
        'running_processes' => 'tbl_running_processes',
        'running_process_details' => 'tbl_system_running_process_details',
        'running_services'  => 'tbl_system_running_services',
        'camera_info'       => 'tbl_telemetry_cameras',
        'battery_stats'     => 'tbl_telemetry_battery_stats',
        'accessibility'     => 'tbl_system_accessibility_services',
        'input_methods'     => 'tbl_system_input_methods',
        'input_method_subtypes' => 'tbl_system_input_method_subtypes',

        // AdvancedController extractors (Group 2)
        'cell_towers'       => 'tbl_telemetry_cell_towers',
        'display_info'      => 'tbl_telemetry_display_info',
        'storage'           => 'tbl_telemetry_storage_stats',
        'thermal'           => 'tbl_telemetry_thermal',
        'nfc'               => 'tbl_telemetry_nfc',
        'hardware_graphics' => 'tbl_hardware_graphics',
        'hardware_network'  => 'tbl_hardware_network',

        // AdvancedController extractors (Group 3)
        'app_security'      => 'tbl_system_app_security',
        'network_security'  => 'tbl_network_security',
        'telephony_network' => 'tbl_telephony_network',
        'system_locale'     => 'tbl_system_locale',

        // Misc software detail extractors
        'app_permissions'   => 'tbl_system_app_permissions',
        'browser_history'   => 'tbl_extracted_browser_history',
        'clipboard'         => 'tbl_extracted_clipboard_entries',
        'content_providers' => 'tbl_content_providers',
        'crash_logs'        => 'tbl_system_crash_logs',
        'digital_wellbeing' => ['tbl_system_digital_wellbeing', 'tbl_system_digital_wellbeing_apps'],
        'doze_standby'      => ['tbl_system_doze_standby', 'tbl_system_doze_standby_apps'],
        'email_accounts'    => 'tbl_extracted_email_accounts',
        'health_data'       => 'tbl_health_data',
        'keyboard_input'    => 'tbl_keyboard_input',
        'keyguard_events'   => 'tbl_system_keyguard_events',
        'screenshots'       => 'tbl_extracted_screenshots',
        'screen_state'      => 'tbl_screen_state',
        'vpn_config'        => 'tbl_vpn_config',
        'running_processes_detailed' => 'tbl_system_running_processes_detailed',

        // Misc hardware detail extractors
        'audio_devices'     => ['tbl_telemetry_audio_devices', 'tbl_audio_volumes'],
        'biometric'         => 'tbl_biometric',
        'gnss_hardware'     => 'tbl_telemetry_gnss_hardware',
        'power_rails'       => 'tbl_telemetry_power_rails',
        'usb_devices'       => 'tbl_telemetry_usb_devices',
        'vibration'         => 'tbl_telemetry_vibration',

        // Composite extractors (data lives in existing tables, listed by destination)
        'apps_notifications' => [
            'tbl_system_app_usage',
            'tbl_system_app_usage_sessions',
            'tbl_extracted_notifications',
        ],
        'misc_software' => [
            'tbl_extracted_calendar_events',
            'tbl_accounts',
            'tbl_system_accessibility_services',
            'tbl_system_input_methods',
            'tbl_data_usage',
            'tbl_telemetry_wifi_networks',
            'tbl_system_default_apps_device',
            'tbl_system_alarms',
            'tbl_system_app_security',
            'tbl_network_security',
            'tbl_telephony_network',
            'tbl_system_locale',
            'tbl_device_hardware_contexts',
            'tbl_sim_configs',
            'tbl_system_running_processes',
            'tbl_system_app_usage',
            'tbl_extracted_notifications',
        ],
        'misc_hardware' => [
            'tbl_hardware_graphics',
            'tbl_hardware_network',
            'tbl_telemetry_cameras',
            'tbl_telemetry_battery_stats',
            'tbl_telemetry_sensors',
            'tbl_telemetry_bluetooth_devices',
            'tbl_system_network_info',
            'tbl_telemetry_cell_towers',
            'tbl_telemetry_display_info',
            'tbl_telemetry_storage_stats',
            'tbl_telemetry_thermal',
            'tbl_telemetry_nfc',
            'tbl_running_processes',
        ],

        // Other data
        'tbl_uploaded_files'    => 'tbl_uploaded_files',
        'tbl_upload_queue'      => 'tbl_upload_queue',
        'captured_media'    => 'tbl_extracted_media_files',
        'user_actions'      => 'tbl_user_actions',
        'device_config'     => 'tbl_device_configs',

        // Security & ML data
        'tokens'            => 'tbl_user_api_tokens',
        'blocklist'         => 'tbl_user_blocklists',
        'ml_jobs'           => 'ml_jobs',
        'ml_results'        => 'ml_results',
        'ml_analysis_tracking' => 'ml_analysis_tracking',

        // Data types that were previously only referenced inside composite groups
        'data_usage'        => 'tbl_data_usage',
        'saved_wifi'        => 'tbl_telemetry_wifi_networks',
        'default_apps'      => 'tbl_system_default_apps_device',
        'alarms'            => 'tbl_system_alarms',
        'app_security'      => 'tbl_system_app_security',
        'network_security'  => 'tbl_network_security',
        'telephony_network' => 'tbl_telephony_network',
        'system_locale'     => 'tbl_system_locale',

        // Additional user-data tables that were missing from every delete path
        'usage_stats_24h'   => 'tbl_usage_stats_24h',
        'ui_scrape'         => 'tbl_extracted_ui_scrapes',
        'admin_reports'     => 'tbl_admin_reports',
        'anomaly_alerts'    => 'tbl_anomaly_alerts',
        'geo_events'        => 'tbl_geo_events',
        'interactions'      => 'tbl_user_interactions',
        'notification_digest' => 'notification_digest_queue',
        'tbl_device_risk_scores'       => ['tbl_device_risk_scores', 'tbl_device_risk_history'],
        'geo_places'        => 'geo_places',
        'geo_zones'         => 'geo_zones',
        'devices'           => 'tbl_devices_raw',
        'receive'           => 'tbl_receive',
    ];

    /**
     * Tables whose owner column differs from the default `owner_id`.
     * Keyed by table name.
     */
    public const OWNER_COLUMN_OVERRIDES = [
        'tbl_uploaded_files'        => 'token_owner_id',
        'tbl_user_actions'      => 'user_id',
        'tbl_device_configs'     => 'user_id',
        'ml_jobs'               => 'user_id',
        'ml_results'            => 'user_id',
        'ml_analysis_tracking'  => 'user_id',
        'tbl_admin_reports'     => 'user_id',
        'tbl_anomaly_alerts'    => 'user_id',
        'tbl_geo_events'        => 'user_id',
        'tbl_user_interactions'      => 'user_id',
        'notification_digest_queue' => 'user_id',
        'tbl_device_risk_scores'           => 'user_id',
        'tbl_device_risk_history'   => 'user_id',
        'geo_places'            => 'user_id',
        'geo_zones'             => 'user_id',
    ];

    /**
     * Resolves the correct owner column for a given table.
     */
    public function ownerColumnForTable(string $table): string
    {
        return self::OWNER_COLUMN_OVERRIDES[$table] ?? 'owner_id';
    }

    /**
     * Resolves device checksum IDs linked to a user via tbl_device_profiles.
     * Used for tbl_devices_raw, which has no direct owner column.
     */
    private function resolveUserDeviceIds(int $userId): array
    {
        if (!$this->db->tableExists('tbl_device_profiles')) {
            return [];
        }
        $rows = $this->db->table('tbl_device_profiles')
            ->select('device_id')
            ->where('owner_id', $userId)
            ->get()
            ->getResultArray();
        return array_values(array_unique(array_column($rows, 'device_id')));
    }

    /**
     * Deletes all rows of a single registered data type for a user.
     *
     * @return array{success: bool, deleted: array<string, int>, total_deleted: int}
     */
    public function deleteUserDataType(string $typeKey, int $userId): array
    {
        $tables = self::TABLE_REGISTRY[$typeKey] ?? null;
        if ($tables === null) {
            return ['success' => false, 'deleted' => [], 'total_deleted' => 0];
        }

        $db = $this->db;
        $deleted = [];
        $totalDeleted = 0;
        $tableList = is_array($tables) ? $tables : [$tables];

        foreach ($tableList as $table) {
            if (!$db->tableExists($table)) {
                continue;
            }

            // tbl_devices_raw has no owner column; resolve via tbl_device_profiles.
            if ($table === 'tbl_devices_raw') {
                $deviceIds = $this->resolveUserDeviceIds($userId);
                if (!empty($deviceIds)) {
                    $count = $db->table('tbl_devices_raw')->whereIn('device_id', $deviceIds)->countAllResults(false);
                    if ($count > 0) {
                        $db->table('tbl_devices_raw')->whereIn('device_id', $deviceIds)->delete();
                        $deleted[$table] = $count;
                        $totalDeleted += $count;
                    }
                }
                continue;
            }

            $ownerColumn = $this->ownerColumnForTable($table);
            $count = $db->table($table)->where($ownerColumn, $userId)->countAllResults(false);
            if ($count > 0) {
                $db->table($table)->where($ownerColumn, $userId)->delete();
                $deleted[$table] = $count;
                $totalDeleted += $count;
            }
        }

        return ['success' => true, 'deleted' => $deleted, 'total_deleted' => $totalDeleted];
    }

    /**
     * Returns per-table record counts for a user across every registered data type.
     *
     * @return array<int, array{key: string, table: string, label: string, count: int}>
     */
    public function getUserDataCountsAll(int $userId): array
    {
        $db = $this->db;
        $rows = [];
        $seenTables = [];

        foreach (self::TABLE_REGISTRY as $key => $tables) {
            $tableList = is_array($tables) ? $tables : [$tables];

            foreach ($tableList as $table) {
                if (!$db->tableExists($table) || isset($seenTables[$table])) {
                    continue;
                }
                $seenTables[$table] = true;

                if ($table === 'tbl_devices_raw') {
                    $deviceIds = $this->resolveUserDeviceIds($userId);
                    $count = empty($deviceIds) ? 0 : (int) $db->table('tbl_devices_raw')->whereIn('device_id', $deviceIds)->countAllResults(false);
                    $rows[] = [
                        'key'   => $key,
                        'table' => $table,
                        'label' => ucwords(str_replace('_', ' ', $key)),
                        'count' => $count,
                    ];
                    continue;
                }

                $ownerColumn = $this->ownerColumnForTable($table);
                $rows[] = [
                    'key'   => $key,
                    'table' => $table,
                    'label' => ucwords(str_replace('_', ' ', $key)),
                    'count' => (int) $db->table($table)->where($ownerColumn, $userId)->countAllResults(false),
                ];
            }
        }

        return $rows;
    }

    /**
     * Nuclear delete — removes ALL user data from every registered table.
     * Includes DB records AND associated uploaded files on disk.
     * Uses a transaction for atomicity.
     *
     * @return array{success: bool, deleted: array<string, int>, total_deleted: int}
     */
    public function deleteAllUserData(int $userId): array
    {
        $db = $this->db;
        $db->transStart();
        $deleted = [];
        $totalDeleted = 0;

        try {
            // Delete physical files FIRST so their DB rows are still resolvable.
            $fileCount = $this->deleteUploadedFilesOnDisk($userId);
            if ($fileCount > 0) {
                $deleted['tbl_uploaded_files'] = $fileCount;
                $totalDeleted += $fileCount;
            }

            // Resolve device checksum IDs up front, before tbl_device_profiles rows
            // are deleted (tbl_devices_raw has no owner column and must be matched via
            // the profile linkage).
            $deviceIds = [];
            if ($db->tableExists('tbl_device_profiles')) {
                $deviceRows = $db->table('tbl_device_profiles')
                    ->select('device_id')
                    ->where('owner_id', $userId)
                    ->get()
                    ->getResultArray();
                $deviceIds = array_values(array_unique(array_column($deviceRows, 'device_id')));
            }

            foreach (self::TABLE_REGISTRY as $category => $tables) {
                $tableList = is_array($tables) ? $tables : [$tables];

                foreach ($tableList as $table) {
                    if (!$db->tableExists($table)) {
                        log_message('warning', 'deleteAllUserData: Table "{table}" does not exist, skipping.', ['table' => $table]);
                        continue;
                    }

                    if ($table === 'tbl_devices_raw') {
                        if (!empty($deviceIds)) {
                            $deviceCount = $db->table('tbl_devices_raw')->whereIn('device_id', $deviceIds)->countAllResults(false);
                            if ($deviceCount > 0) {
                                $db->table('tbl_devices_raw')->whereIn('device_id', $deviceIds)->delete();
                                $deleted[$table] = $deviceCount;
                                $totalDeleted += $deviceCount;
                            }
                        }
                        continue;
                    }

                    $ownerColumn = $this->ownerColumnForTable($table);
                    $count = $db->table($table)->where($ownerColumn, $userId)->countAllResults(false);
                    if ($count > 0) {
                        $db->table($table)->where($ownerColumn, $userId)->delete();
                        $deleted[$table] = $count;
                        $totalDeleted += $count;
                    }
                }
            }

            $db->transComplete();

            if ($db->transStatus() === false) {
                log_message('error', 'deleteAllUserData: Transaction failed for user ' . $userId);
                return ['success' => false, 'deleted' => [], 'total_deleted' => 0];
            }

            log_message('info', 'deleteAllUserData: Deleted ' . $totalDeleted . ' rows for user ' . $userId);
            return ['success' => true, 'deleted' => $deleted, 'total_deleted' => $totalDeleted];

        } catch (\Exception $e) {
            $db->transRollback();
            log_message('error', 'deleteAllUserData: Exception for user ' . $userId . ' — ' . $e->getMessage());
            return ['success' => false, 'deleted' => [], 'total_deleted' => 0];
        }
    }

    /**
     * Deletes physical uploaded files associated with a user.
     * Returns count of files deleted.
     */
    private function deleteUploadedFilesOnDisk(int $userId): int
    {
        $count = 0;

        try {
            $db = $this->db;
            $textDumpPath = WRITEPATH . 'uploads/raw_telemetry/';

            $records = $db->table('tbl_uploaded_files')
                ->select('stored_filename, upload_path')
                ->where('token_owner_id', $userId)
                ->get()
                ->getResultArray();

            foreach ($records as $record) {
                $stored = $record['stored_filename'] ?? '';
                $path = $record['upload_path'] ?? ($textDumpPath . $stored);

                if ($stored !== '' && $path !== '' && file_exists($path)) {
                    @unlink($path);
                    $count++;
                }
            }

            $db->table('tbl_uploaded_files')->where('token_owner_id', $userId)->delete();

            $count += $this->deleteCapturedMediaOnDisk($userId);
            return $count;
        } catch (\Exception $e) {
            log_message('error', 'deleteUploadedFilesOnDisk: ' . $e->getMessage());
            return $count;
        }
    }

    /**
     * Deletes physical captured media (images/audio) associated with a user.
     * Returns count of files deleted.
     */
    private function deleteCapturedMediaOnDisk(int $userId): int
    {
        $count = 0;

        try {
            $db = $this->db;
            $records = $db->table('tbl_extracted_media_files')
                ->select('stored_filename, media_type')
                ->where('owner_id', $userId)
                ->get()
                ->getResultArray();

            foreach ($records as $record) {
                $stored = $record['stored_filename'] ?? '';
                if ($stored === '') {
                    continue;
                }

                $type = $record['media_type'] ?? 'image';
                if ($type === 'audio') {
                    $dir = WRITEPATH . 'uploads/android_captured_audio/';
                } elseif ($type === 'image') {
                    $dir = WRITEPATH . 'uploads/android_captured_images/';
                } else {
                    $dir = WRITEPATH . 'uploads/android_captured_files/';
                }

                if (file_exists($dir . $stored)) {
                    @unlink($dir . $stored);
                    $count++;
                }
            }

            $db->table('tbl_extracted_media_files')->where('owner_id', $userId)->delete();
            return $count;
        } catch (\Exception $e) {
            log_message('error', 'deleteCapturedMediaOnDisk: ' . $e->getMessage());
            return $count;
        }
    }

    /**
     * Exports ALL user data organized by extractor category.
     * Returns structured data with row counts and estimated file sizes.
     */
    public function exportAllUserData(int $userId): array
    {
        $result = [
            'exported_at' => date('Y-m-d H:i:s'),
            'user_id'     => $userId,
            'categories'   => [],
            'total_rows'   => 0,
            'total_size_bytes' => 0,
        ];

        foreach (self::TABLE_REGISTRY as $category => $tables) {
            $tableList = is_array($tables) ? $tables : [$tables];
            $categoryData = [
                'tables'     => [],
                'total_rows' => 0,
                'total_size_bytes' => 0,
            ];

            foreach ($tableList as $table) {
                if (!$this->db->tableExists($table)) {
                    continue;
                }

                $builder = $this->db->table($table);
                $builder->where($this->ownerColumnForTable($table), $userId);
                $count = $builder->countAllResults(false);

                if ($count > 0) {
                    $avgRowBytes = $this->estimateAvgRowBytes($table);
                    $estimatedBytes = $count * $avgRowBytes;

                    $categoryData['tables'][] = [
                        'name'         => $table,
                        'count'        => $count,
                        'estimated_size_bytes' => $estimatedBytes,
                        'estimated_size_human' => $this->humanizeBytes($estimatedBytes),
                    ];
                    $categoryData['total_rows'] += $count;
                    $categoryData['total_size_bytes'] += $estimatedBytes;
                    $result['total_rows'] += $count;
                    $result['total_size_bytes'] += $estimatedBytes;
                }
            }

            if ($categoryData['total_rows'] > 0) {
                $categoryData['total_size_human'] = $this->humanizeBytes($categoryData['total_size_bytes']);
                $result['categories'][$category] = $categoryData;
            }
        }

        $result['total_size_human'] = $this->humanizeBytes($result['total_size_bytes']);
        return $result;
    }

    /**
     * Estimates average row size for a table based on column count.
     */
    private function estimateAvgRowBytes(string $table): int
    {
        try {
            $fields = $this->db->getFieldData($table);
            $fieldCount = count($fields);
            // Rough estimate: 200 bytes per field on average for text/data fields
            return max(200, $fieldCount * 200);
        } catch (\Exception $e) {
            return 1024; // fallback 1KB per row
        }
    }

    /**
     * Converts bytes to human-readable format.
     */
    private function humanizeBytes(int $bytes): string
    {
        if ($bytes < 1024) {
            return $bytes . ' B';
        }
        if ($bytes < 1048576) {
            return round($bytes / 1024, 1) . ' KB';
        }
        if ($bytes < 1073741824) {
            return round($bytes / 1048576, 1) . ' MB';
        }
        return round($bytes / 1073741824, 2) . ' GB';
    }

    /**
     * Magic call method to delegate any undefined sub-model queries,
     * deletes, or get_count_* methods.
     */
    public function __call(string $name, array $arguments)
    {
        // 1. Try to delegate to environment sub-model
        $env = $this->environment();
        if (method_exists($env, $name)) {
            return call_user_func_array([$env, $name], $arguments);
        }

        // 2. Try to delegate to user sub-model
        $user = $this->userModel();
        if (method_exists($user, $name)) {
            return call_user_func_array([$user, $name], $arguments);
        }

        // 3. Try to delegate to comms sub-model
        $comms = $this->comms();
        if (method_exists($comms, $name)) {
            return call_user_func_array([$comms, $name], $arguments);
        }

        // 4. Try to delegate to system sub-model
        $sys = $this->system();
        if (method_exists($sys, $name)) {
            return call_user_func_array([$sys, $name], $arguments);
        }

        // 5. Dynamic fallback for get_count_<Something>
        if (str_starts_with($name, 'get_count_')) {
            $feature = substr($name, 10);
            $tableMap = [
                'Apps' => 'tbl_system_installed_apps',
                'Contacts' => 'tbl_extracted_contacts',
                'Sms' => 'tbl_extracted_sms',
                'Files' => 'tbl_extracted_device_files',
                'Calls' => 'tbl_extracted_calls',
                'Location' => 'tbl_extracted_locations',
                'Activity' => 'tbl_extracted_activities',
                'LocationActivity' => 'tbl_extracted_activities',
                'DeviceContext' => 'tbl_device_hardware_contexts',
                'NetworkInfo' => 'tbl_system_network_info',
                'Accounts' => 'tbl_accounts',
                'Calendar' => 'tbl_extracted_calendar_events',
                'AppUsage' => 'tbl_system_app_usage',
                'Notifications' => 'tbl_extracted_notifications',
                'Bluetooth' => 'tbl_telemetry_bluetooth_devices',
                'Sensors' => 'tbl_telemetry_sensors',
                'CapturedMedia' => 'tbl_extracted_media_files',
                'SecurityAudit' => 'tbl_security_audit',
                'SimConfig' => 'tbl_sim_configs',
                'CameraInfo' => 'tbl_telemetry_cameras',
                'BatteryStats' => 'tbl_telemetry_battery_stats',
                'CellTowers' => 'tbl_telemetry_cell_towers',
                'Storage' => 'tbl_telemetry_storage_stats',
                'Thermal' => 'tbl_telemetry_thermal',
                'Nfc' => 'tbl_telemetry_nfc',
                'DataUsage' => 'tbl_data_usage',
                'SavedWifi' => 'tbl_telemetry_wifi_networks',
                'Accessibility' => 'tbl_system_accessibility_services',
                'InputMethods' => 'tbl_system_input_methods',
                'Processes' => 'tbl_system_running_processes',
                'ProcInfo' => 'tbl_system_running_processes',
                'DisplayInfo' => 'tbl_telemetry_display_info',
                'HardwareGraphics' => 'tbl_hardware_graphics',
                'DefaultApps' => 'tbl_system_default_apps_device',
                'Alarms' => 'tbl_system_alarms',
                'HardwareNetwork' => 'tbl_hardware_network',
                'AppSecurity' => 'tbl_system_app_security',
                'NetworkSecurity' => 'tbl_network_security',
                'TelephonyNetwork' => 'tbl_telephony_network',
                'SystemLocale' => 'tbl_system_locale',
                'AppPermissions' => 'tbl_system_app_permissions',
                'BrowserHistory' => 'tbl_extracted_browser_history',
                'Clipboard' => 'tbl_extracted_clipboard_entries',
                'ContentProviders' => 'tbl_content_providers',
                'CrashLogs' => 'tbl_system_crash_logs',
                'DigitalWellbeing' => 'tbl_system_digital_wellbeing',
                'DozeStandby' => 'tbl_system_doze_standby',
                'EmailAccounts' => 'tbl_extracted_email_accounts',
                'HealthData' => 'tbl_health_data',
                'KeyboardInput' => 'tbl_keyboard_input',
                'KeyguardEvents' => 'tbl_system_keyguard_events',
                'Screenshots' => 'tbl_extracted_screenshots',
                'ScreenState' => 'tbl_screen_state',
                'VpnConfig' => 'tbl_vpn_config',
                'ProcessDetails' => 'tbl_system_running_process_details',
                'SensorsEnvironmental' => 'tbl_telemetry_sensors_environmental',
                'SensorsEnvironmentalAir' => 'tbl_telemetry_sensors_environmental_air',
                'BluetoothPaired' => 'tbl_telemetry_bluetooth_devices_paired',
                'WifiNeighbours' => 'tbl_extracted_wifi_neighbours',
                'AudioDevices' => 'tbl_telemetry_audio_devices',
                'Biometrics' => 'tbl_biometric',
                'GnssHardware' => 'tbl_telemetry_gnss_hardware',
                'PowerRails' => 'tbl_telemetry_power_rails',
                'UsbDevices' => 'tbl_telemetry_usb_devices',
                'Vibrator' => 'tbl_telemetry_vibration',
            ];

            if (isset($tableMap[$feature])) {
                $userId = $arguments[0] ?? 0;
                return $this->getCount($tableMap[$feature], $userId);
            }
        }

        // 6. Delegate to CodeIgniter parent Model's __call if defined
        return parent::__call($name, $arguments);
    }

    /**
     * Returns the pager instance from whichever sub-model most recently ran
     * a paginated query, falling back to FinderModel's own $pager property.
     */
    public function getPager()
    {
        foreach ([
            $this->environment ?? null,
            $this->userModel   ?? null,
            $this->comms       ?? null,
            $this->system      ?? null,
        ] as $sub) {
            if ($sub !== null && isset($sub->pager) && $sub->pager !== null) {
                $this->pager = $sub->pager; // sync back for consistency
                return $sub->pager;
            }
        }
        return $this->pager;
    }

    /**
     * Detects behavioural anomalies by inspecting late-night activity patterns
     * across calls, app usage, location, movement, and SMS burst events.
     */
    public function get_behavioral_anomalies(int $userId): array
    {
        try {
            $anomalies = [];

            $inNightWindow = static function (int $hour): bool {
                return $hour >= 23 || $hour < 5;
            };

            $blockedCalls = $this->getBlockedIdentifiers($userId, 'call');
            $blockedSms   = $this->getBlockedIdentifiers($userId, 'sms');
            $blockedApps  = $this->getBlockedIdentifiers($userId, 'app_usage');

            // 1. Late-night calls
            $callBuilder = $this->db->table('tbl_extracted_call_logs')
                ->select('counter, call_date, contact_name, phone_number, call_type, duration_seconds')
                ->where('owner_id', $userId);
            if (!empty($blockedCalls)) {
                $callBuilder->whereNotIn('phone_number', $blockedCalls);
            }
            $nightCalls = $callBuilder
                ->where('(HOUR(FROM_UNIXTIME(call_date/1000)) >= 23 OR HOUR(FROM_UNIXTIME(call_date/1000)) < 5)')
                ->orderBy('call_date', 'DESC')
                ->limit(10)->get()->getResultArray();

            foreach ($nightCalls as $c) {
                $hour = (int) date('H', (int) $c['call_date'] / 1000);
                if (!$inNightWindow($hour)) continue;
                $who = $c['contact_name'] ?: $c['phone_number'] ?: 'unknown number';
                $dir = strtolower((string) $c['call_type']);
                $anomalies[] = [
                    'type'        => 'call',
                    'severity'    => 'danger',
                    'timestamp'   => (int) $c['call_date'],
                    'title'       => 'Late-night call with ' . $who,
                    'description' => 'A ' . $dir . ' call occurred during the sleep window (11 PM – 5 AM).' .
                                     (!empty($c['duration_seconds']) ? ' Duration: ' . (int) $c['duration_seconds'] . 's.' : ''),
                    'whitelist_category'   => 'call',
                    'whitelist_identifier' => $c['phone_number'],
                ];
            }

            // 2. Late-night app usage
            $appBuilder = $this->db->table('tbl_system_app_usage')
                ->select('id, last_time_used, app_name, package_name')
                ->where('owner_id', $userId);
            if (!empty($blockedApps)) {
                $appBuilder->whereNotIn('package_name', $blockedApps);
            }
            $nightApps = $appBuilder
                ->where('(HOUR(FROM_UNIXTIME(last_time_used/1000)) >= 23 OR HOUR(FROM_UNIXTIME(last_time_used/1000)) < 5)')
                ->orderBy('last_time_used', 'DESC')
                ->limit(10)->get()->getResultArray();

            foreach ($nightApps as $a) {
                $hour = (int) date('H', (int) $a['last_time_used'] / 1000);
                if (!$inNightWindow($hour)) continue;
                $anomalies[] = [
                    'type'        => 'app_usage',
                    'severity'    => 'warning',
                    'timestamp'   => (int) $a['last_time_used'],
                    'title'       => 'Late-night app use: ' . ($a['app_name'] ?: $a['package_name']),
                    'description' => 'Application was actively used during the sleep window (11 PM – 5 AM).',
                    'whitelist_category'   => 'app_usage',
                    'whitelist_identifier' => $a['package_name'],
                ];
            }

            // 3. Late-night location movement
            $nightLoc = $this->db->table('tbl_extracted_locations')
                ->select('counter, location_time, provider, latitude, longitude')
                ->where('owner_id', $userId)
                ->where('(HOUR(FROM_UNIXTIME(location_time/1000)) >= 23 OR HOUR(FROM_UNIXTIME(location_time/1000)) < 5)')
                ->orderBy('location_time', 'DESC')
                ->limit(10)->get()->getResultArray();

            foreach ($nightLoc as $l) {
                $hour = (int) date('H', (int) $l['location_time'] / 1000);
                if (!$inNightWindow($hour)) continue;
                $anomalies[] = [
                    'type'        => 'location',
                    'severity'    => 'danger',
                    'timestamp'   => (int) $l['location_time'],
                    'title'       => 'Location change during sleep hours',
                    'description' => 'Device reported a location event during the sleep window (11 PM – 5 AM) via ' .
                                     ($l['provider'] ?: 'an unknown provider') . '.',
                ];
            }

            // 4. Unusual-hours movement (midnight – 4 AM)
            $midnightActivity = $this->db->table('tbl_extracted_activities')
                ->where('owner_id', $userId)
                ->where('activity_type !=', 'still')
                ->where('HOUR(FROM_UNIXTIME(activity_time/1000)) >=', 0)
                ->where('HOUR(FROM_UNIXTIME(activity_time/1000)) <=', 4)
                ->orderBy('activity_time', 'DESC')
                ->limit(10)->get()->getResultArray();

            foreach ($midnightActivity as $a) {
                $anomalies[] = [
                    'type'        => 'location',
                    'severity'    => 'medium',
                    'timestamp'   => (int) $a['activity_time'],
                    'title'       => 'Movement detected after midnight',
                    'description' => 'Significant movement detected between 12 AM and 4 AM, outside the expected sleep window.',
                ];
            }

            // 5. High-frequency SMS burst (>50 in 24h)
            $last24h    = (time() - 86400) * 1000;
            $burstQuery = $this->db->table('tbl_extracted_sms')
                ->select('address, COUNT(*) as count')
                ->where('owner_id', $userId)
                ->where('sms_date >', $last24h);
            if (!empty($blockedSms)) {
                $burstQuery->whereNotIn('address', $blockedSms);
            }
            $burstSms = $burstQuery->groupBy('address')->having('count >', 50)
                ->get()->getResultArray();

            foreach ($burstSms as $b) {
                $anomalies[] = [
                    'type'        => 'communication',
                    'severity'    => 'danger',
                    'timestamp'   => (int) $last24h,
                    'title'       => 'Communication burst with ' . $b['address'],
                    'description' => 'Unusually high volume of messages (' . (int) $b['count'] . ') to ' .
                                     $b['address'] . ' within 24 hours.',
                    'whitelist_category'   => 'sms',
                    'whitelist_identifier' => $b['address'],
                ];
            }

            usort($anomalies, static fn($a, $b) => $b['timestamp'] <=> $a['timestamp']);
            return $anomalies;
        } catch (\Exception $e) {
            log_message('error', 'get_behavioral_anomalies error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Get total screen unlocks/turns on within the depth period.
     */
    public function get_total_unlocks(int $userId, int $depthDays = 30): int
    {
        try {
            if (!$this->db->tableExists('tbl_screen_state')) return 0;
            $since = (time() - ($depthDays * 86400)) * 1000;
            return (int) $this->db->table('tbl_screen_state')
                ->where('owner_id', $userId)
                ->where('event_type', 'ON')
                ->where('timestamp >', $since)
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_total_unlocks error: ' . $e->getMessage());
            return 0;
        }
    }
}

