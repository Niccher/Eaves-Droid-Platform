<?php

namespace App\Models\Finder;

use CodeIgniter\Model;

class FinderUser extends Model
{
    protected $parent;
    protected $db;

    public function __construct($parent)
    {
        parent::__construct();
        $this->parent = $parent;
        $this->db = $parent->db;
    }

    public function __get($name)
    {
        if ($name === 'deviceId') {
            return $this->parent->deviceId;
        }
        if ($name === 'pager') {
            return $this->parent->pager;
        }
        return null;
    }

    public function __set($name, $value)
    {
        if ($name === 'pager') {
            $this->parent->pager = $value;
        }
        if ($name === 'total_timeline') {
            $this->parent->total_timeline = $value;
        }
    }

    // Helper wrappers
    protected function applyOwnerDeviceFilter($builder, int $user_id)
    {
        return $this->parent->applyOwnerDeviceFilter($builder, $user_id);
    }

    protected function fq(string $table, int $userId)
    {
        return $this->parent->fq($table, $userId);
    }

    protected function cq(string $table, int $userId): int
    {
        return $this->parent->cq($table, $userId);
    }

    protected function getCount(string $table, int $user_id, array $extraWhere = [], ?string $blockColumn = null, array $blockedValues = []): int
    {
        return $this->parent->getCount($table, $user_id, $extraWhere, $blockColumn, $blockedValues);
    }

    protected function getBlockedIdentifiers(int $userId, string $category): array
    {
        return $this->parent->getBlockedIdentifiers($userId, $category);
    }

    public function deleteFilesByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_extracted_device_files');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteFilesByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteBrowserHistoryByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_extracted_browser_history');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteBrowserHistoryByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteCalendarEventsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_extracted_calendar_events');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteCalendarEventsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteClipboardByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_extracted_clipboard_entries');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteClipboardByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteAppUsageStatsByUser(int $user_id): bool
    {
        try {
            $builder = $this->db->table('tbl_system_app_usage');
            return $this->applyOwnerDeviceFilter($builder, $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteAppUsageStatsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteDeviceProfilesByUser(int $user_id): bool
    {
        return $this->fq('tbl_device_profiles', $user_id)->delete();
    }

    public function deleteMediaCapturedByUser(int $user_id): bool
    {
        return $this->fq('tbl_extracted_media_files', $user_id)->delete();
    }

    public function deleteAppPermissionsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_app_permissions', $user_id)->delete();
    }

    public function deleteDeviceFilesByUser(int $user_id): bool
    {
        return $this->fq('tbl_device_files', $user_id)->delete();
    }

    public function deleteActivitiesByUser(int $user_id): bool
    {
        return $this->fq('tbl_extracted_activities', $user_id)->delete();
    }

    public function deleteSimConfigsByUser(int $user_id): bool
    {
        return $this->fq('tbl_sim_configs', $user_id)->delete();
    }

    public function deleteHealthDataByUser(int $user_id): bool
    {
        return $this->fq('tbl_health_data', $user_id)->delete();
    }

    public function deleteRunningServicesByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_running_services', $user_id)->delete();
    }

    public function deleteRunningProcessDetailsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_running_process_details', $user_id)->delete();
    }

    public function deleteInputMethodSubtypesByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_input_method_subtypes', $user_id)->delete();
    }

    public function deleteDozeStandbyAppsByUser(int $user_id): bool
    {
        return $this->fq('tbl_system_doze_standby_apps', $user_id)->delete();
    }

    public function deleteUserActionsByUser(int $user_id): bool
    {
        try {
            return $this->db->table('tbl_user_actions')->where('user_id', $user_id)->delete();
        } catch (\Exception $e) {
            log_message('error', 'deleteUserActionsByUser error: ' . $e->getMessage());
            return false;
        }
    }

    public function deleteTimelinePivotsByUser(int $user_id): bool
    {
        return $this->fq('tbl_timeline_pivots', $user_id)->delete();
    }

    public function get_count_Files(int $user_id): int
    {
        return $this->getCount('tbl_extracted_device_files', $user_id);
    }

    public function get_count_BrowserHistory(int $user_id): int
    {
        return $this->getCount('tbl_extracted_browser_history', $user_id);
    }

    public function get_count_Clipboard(int $user_id): int
    {
        return $this->getCount('tbl_extracted_clipboard_entries', $user_id);
    }

    public function get_count_CalendarEvents(int $user_id): int
    {
        return $this->getCount('tbl_extracted_calendar_events', $user_id);
    }

    public function get_count_AppUsageStats(int $user_id): int
    {
        return $this->getCount('tbl_system_app_usage', $user_id);
    }

    public function get_count_MediaCaptured(int $user_id): int
    {
        return $this->getCount('tbl_extracted_media_files', $user_id);
    }

    public function get_count_DeviceFiles(int $user_id): int
    {
        return $this->getCount('tbl_device_files', $user_id);
    }

    public function get_count_Activities(int $user_id): int
    {
        return $this->getCount('tbl_extracted_activities', $user_id);
    }

    public function get_count_HealthData(int $user_id): int
    {
        return $this->getCount('tbl_health_data', $user_id);
    }

    public function export_files(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_device_files', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_browser_history(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_browser_history', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_calendar_events(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_calendar_events', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_clipboard(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_clipboard_entries', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_app_usage_stats(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_app_usage', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_device_profiles(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_device_profiles', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_media_captured(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_media_files', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_device_files(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_device_files', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_activities(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_extracted_activities', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_sim_configs(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_sim_configs', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_health_data(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_health_data', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_running_services(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_running_services', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_running_process_details(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_running_process_details', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_input_method_subtypes(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_input_method_subtypes', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_doze_standby_apps(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_system_doze_standby_apps', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function export_user_actions(int $user_id, int $limit = 1000): array
    {
        try {
            return $this->db->table('tbl_user_actions')->where('user_id', $user_id)->limit($limit)->get()->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'export_user_actions: ' . $e->getMessage());
            return [];
        }
    }

    public function export_timeline_pivots(int $user_id, int $limit = 1000): array
    {
        return $this->fq('tbl_timeline_pivots', $user_id)->limit($limit)->get()->getResultArray();
    }

    public function get_files(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Files($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_device_files', $user_id)
                ->orderBy('file_date', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_files: ' . $e->getMessage());
            return [];
        }
    }

    public function get_browser_history(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_BrowserHistory($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_browser_history', $user_id)
                ->orderBy('last_visit_time', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_browser_history: ' . $e->getMessage());
            return [];
        }
    }

    public function get_calendar_events(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_CalendarEvents($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_calendar_events', $user_id)
                ->orderBy('start_time', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_calendar_events: ' . $e->getMessage());
            return [];
        }
    }

    public function get_app_usage_stats(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_AppUsageStats($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_system_app_usage', $user_id)
                ->orderBy('usage_date', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_app_usage_stats: ' . $e->getMessage());
            return [];
        }
    }

    public function get_media_captured(int $user_id, int $perPage = 12): array
    {
        try {
            $total = $this->get_count_MediaCaptured($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_media_files', $user_id)
                ->orderBy('captured_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_media_captured: ' . $e->getMessage());
            return [];
        }
    }

    public function get_device_files(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_DeviceFiles($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_device_files', $user_id)
                ->orderBy('file_modified_time', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_device_files: ' . $e->getMessage());
            return [];
        }
    }

    public function get_activities(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_Activities($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_activities', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_activities: ' . $e->getMessage());
            return [];
        }
    }

    public function get_health_data(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->get_count_HealthData($user_id);
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_health_data', $user_id)
                ->orderBy('extracted_at', 'DESC')
                ->limit($perPage, $offset)->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_health_data: ' . $e->getMessage());
            return [];
        }
    }

    public function get_health_summary(int $userId): array
    {
        $health = $this->db->table('tbl_health_data')
            ->select('extracted_at as timestamp, step_count as steps, calories_kcal as calories_burned, heart_rate_bpm as heart_rate, 0 as sleep_duration_min, 0 as water_intake_ml')
            ->where('owner_id', $userId)
            ->orderBy('extracted_at', 'DESC')
            ->limit(30)
            ->get()
            ->getResultArray();

        if (empty($health))
            return [];

        $totals = ['steps' => 0, 'calories' => 0, 'water' => 0, 'sleep' => 0, 'hr_sum' => 0, 'hr_count' => 0];

        foreach ($health as $h) {
            $totals['steps'] += $h['steps'];
            $totals['calories'] += $h['calories_burned'];
            $totals['water'] += $h['water_intake_ml'];
            $totals['sleep'] += $h['sleep_duration_min'];
            if ($h['heart_rate'] > 0) {
                $totals['hr_sum'] += $h['heart_rate'];
                $totals['hr_count']++;
            }
        }

        $days = count($health);
        return [
            'avg_steps' => round($totals['steps'] / $days),
            'avg_calories' => round($totals['calories'] / $days),
            'avg_water_ml' => round($totals['water'] / $days),
            'avg_sleep_hours' => round(($totals['sleep'] / $days) / 60, 1),
            'avg_heart_rate' => $totals['hr_count'] > 0 ? round($totals['hr_sum'] / $totals['hr_count']) : 72
        ];
    }

    public function get_app_addiction_report(int $userId, int $days = 30, int $limit = 8): array
    {
        try {
            $cutoffMs = (time() - $days * 86400) * 1000;
            $sql = "
                SELECT wa.package_name AS app_name, wa.category,
                       SUM(wa.daily_usage_minutes) as total_minutes
                FROM tbl_system_digital_wellbeing_apps wa
                JOIN tbl_system_digital_wellbeing w ON w.id = wa.wellbeing_id
                WHERE wa.owner_id = ? AND w.extracted_at >= ?
                 GROUP BY wa.package_name, wa.category
                ORDER BY total_minutes DESC
                LIMIT ?
            ";
            $rows = $this->db->query($sql, [$userId, $cutoffMs, $limit])->getResultArray();

            $grandTotal = 0;
            foreach ($rows as $r) $grandTotal += (int)$r['total_minutes'];

            $out = [];
            foreach ($rows as $r) {
                $total = (int)$r['total_minutes'];
                $share = $grandTotal > 0 ? round($total / $grandTotal * 100, 1) : 0;
                $out[] = [
                    'package'   => $r['app_name'],
                    'name'      => $r['app_name'] ?: $r['package_name'],
                    'category'  => $r['category'] ?: 'Other',
                    'minutes'   => $total,
                    'share_pct' => $share,
                    'addiction_risk' => $share >= 25, // dominates >25% of screen time
                ];
            }
            return $out;
        } catch (\Exception $e) {
            log_message('error', 'get_app_addiction_report error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_sleep_quality_log(int $userId): array
    {
        $health = $this->db->table('tbl_health_data')
            ->select('timestamp, sleep_duration_min, sleep_deep_duration_min')
            ->where('owner_id', $userId)
            ->where('sleep_duration_min >', 0)
            ->orderBy('timestamp', 'DESC')
            ->limit(15)
            ->get()
            ->getResultArray();

        $log = [];
        foreach ($health as $h) {
            $total = $h['sleep_duration_min'];
            $deep = $h['sleep_deep_duration_min'];

            // Ratio of deep sleep (ideally 20-25%)
            $ratio = $total > 0 ? ($deep / $total) * 100 : 0;

            $quality = 'Good';
            if ($total < 360)
                $quality = 'Poor (Too short)';
            else if ($ratio < 15)
                $quality = 'Restless (Low deep sleep)';
            else if ($ratio > 30)
                $quality = 'Excellent';

            $log[] = [
                'date' => date('M j, Y', $h['timestamp'] / 1000),
                'total_hours' => round($total / 60, 1),
                'deep_hours' => round($deep / 60, 1),
                'quality' => $quality
            ];
        }

        return $log;
    }

    public function get_count_InstalledApps(int $user_id): int
    {
        return $this->getCount('tbl_extracted_installed_apps', $user_id);
    }

    public function delete_file(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_device_files')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_file error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_browser_history_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_browser_history')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_browser_history_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_calendar_event(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_calendar_events')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_calendar_event error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_app_usage_stats_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_system_app_usage')
                ->where('counter', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_app_usage_stats_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_media_captured_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_media_files')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_media_captured_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_device_files_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_device_files')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_device_files_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_activities_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_activities')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_activities_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function delete_health_data_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_health_data')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_health_data_row error: ' . $e->getMessage());
            return false;
        }
    }

    public function get_clipboard(int $user_id, int $perPage = 25): array
    {
        try {
            $total = $this->fq('tbl_extracted_clipboard_entries', $user_id)
                ->where('clip_text IS NOT NULL AND clip_text != ""')
                ->countAllResults();
            $page = service('request')->getGet('page') ?? 1;
            $offset = ($page - 1) * $perPage;
            $results = $this->fq('tbl_extracted_clipboard_entries', $user_id)
                ->where('clip_text IS NOT NULL AND clip_text != ""')
                ->orderBy('timestamp', 'DESC')
                ->limit($perPage, $offset)
                ->get()->getResultArray();
            $this->pager = \Config\Services::pager();
            $this->pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');
            return $results;
        } catch (\Exception $e) {
            log_message('error', 'get_clipboard error: ' . $e->getMessage());
            return [];
        }
    }

    public function delete_clipboard_row(int $id, int $userId): bool
    {
        try {
            return (bool) $this->db->table('tbl_extracted_clipboard_entries')
                ->where('id', $id)
                ->where('owner_id', $userId)
                ->delete();
        } catch (\Exception $e) {
            log_message('error', 'delete_clipboard_row: ' . $e->getMessage());
            return false;
        }
    }

    public function get_daily_usage_heatmap(int $userId): array
    {
        try {
            $sql = "
                SELECT 
                    DATE(FROM_UNIXTIME(extracted_at / 1000)) as date,
                    SUM(foreground_time_ms) as total_time_ms
                FROM tbl_system_app_usage
                WHERE owner_id = ? AND extracted_at IS NOT NULL AND extracted_at > 0
                GROUP BY date
                ORDER BY date ASC
            ";
            return $this->db->query($sql, [$userId])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_daily_usage_heatmap error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_dopamine_vs_productivity(int $userId): array
    {
        try {
            $socialPackages = [
                'com.whatsapp', 'com.facebook.katana', 'com.instagram.android', 
                'com.zhiliaoapp.musically', 'com.snapchat.android', 'com.twitter.android',
                'com.ss.android.ugc.trill', 'com.tencent.ig', 'com.whatsapp.w4b'
            ];
            $productivityPackages = [
                'com.slack', 'com.google.android.gm', 'com.microsoft.teams',
                'com.google.android.apps.docs', 'com.microsoft.office.word',
                'com.google.android.calendar', 'com.google.android.keep'
            ];

            $socialIn = "'" . implode("','", $socialPackages) . "'";
            $prodIn = "'" . implode("','", $productivityPackages) . "'";

            $sql = "
                SELECT 
                    SUM(CASE WHEN package_name IN ($socialIn) THEN foreground_time_ms ELSE 0 END) as dopamine_ms,
                    SUM(CASE WHEN package_name IN ($prodIn) THEN foreground_time_ms ELSE 0 END) as productivity_ms,
                    SUM(CASE WHEN package_name NOT IN ($socialIn) AND package_name NOT IN ($prodIn) THEN foreground_time_ms ELSE 0 END) as other_ms
                FROM tbl_system_app_usage
                WHERE owner_id = ?
            ";
            $row = $this->db->query($sql, [$userId])->getRowArray();
            return $row ?: ['dopamine_ms' => 0, 'productivity_ms' => 0, 'other_ms' => 0];
        } catch (\Exception $e) {
            log_message('error', 'get_dopamine_vs_productivity error: ' . $e->getMessage());
            return ['dopamine_ms' => 0, 'productivity_ms' => 0, 'other_ms' => 0];
        }
    }

    public function get_top_time_sink_apps(int $userId, int $limit = 5): array
    {
        try {
            $sql = "
                SELECT package_name, app_name, SUM(foreground_time_ms) as total_time_ms
                FROM tbl_system_app_usage
                WHERE owner_id = ?
                GROUP BY package_name, app_name
                ORDER BY total_time_ms DESC
                LIMIT ?
            ";
            return $this->db->query($sql, [$userId, $limit])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_top_time_sink_apps error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_sleep_intervals(int $userId, int $days = 30): array
    {
        try {
            $cutoffMs = (time() - $days * 86400) * 1000;
            $sql = "
                SELECT start_time, end_time, sleep_stage, sleep_efficiency,
                       session_name, session_type, session_description
                FROM tbl_health_data
                WHERE owner_id = ? AND end_time >= ? AND LOWER(IFNULL(sleep_stage,'')) <> ''
                ORDER BY end_time ASC
            ";
            $rows = $this->db->query($sql, [$userId, $cutoffMs])->getResultArray();

            $nights = [];
            foreach ($rows as $r) {
                $endMs = (int)($r['end_time'] ?? 0);
                if ($endMs <= 0) continue;
                $date = date('Y-m-d', (int)floor($endMs / 1000));
                $start = (int)($r['start_time'] ?? 0);
                if ($start <= 0) $start = $endMs - (8 * 3600000);
                if (!isset($nights[$date])) {
                    $nights[$date] = ['date' => $date, 'start_ms' => $start, 'end_ms' => $endMs, 'stages' => []];
                } else {
                    $nights[$date]['start_ms'] = min($nights[$date]['start_ms'], $start);
                    $nights[$date]['end_ms']   = max($nights[$date]['end_ms'], $endMs);
                }
                $stage = $r['sleep_stage'] ?? '';
                if ($stage && !in_array($stage, $nights[$date]['stages'], true)) {
                    $nights[$date]['stages'][] = $stage;
                }
            }

            $out = [];
            foreach ($nights as $n) {
                $durHrs = round(($n['end_ms'] - $n['start_ms']) / 3600000, 1);
                $out[] = [
                    'date'       => $n['date'],
                    'sleep_start'=> date('H:i', (int)floor($n['start_ms'] / 1000)),
                    'sleep_end'  => date('H:i', (int)floor($n['end_ms'] / 1000)),
                    'duration_hours' => $durHrs,
                    'stages'     => implode(', ', $n['stages']),
                ];
            }
            return $out;
        } catch (\Exception $e) {
            log_message('error', 'get_sleep_intervals error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_daily_screen_time(int $userId, int $days = 30): array
    {
        try {
            $cutoffMs = (time() - $days * 86400) * 1000;
            $sql = "
                SELECT DATE(FROM_UNIXTIME(extracted_at / 1000)) as date,
                       total_daily_usage_minutes as minutes,
                       unlock_count, notification_count
                FROM tbl_system_digital_wellbeing
                WHERE owner_id = ? AND extracted_at >= ? AND total_daily_usage_minutes > 0
                ORDER BY date ASC
            ";
            return $this->db->query($sql, [$userId, $cutoffMs])->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_daily_screen_time error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_activity_battery_trends(int $userId, int $days = 30): array
    {
        try {
            $cutoffMs = (time() - $days * 86400) * 1000;

            // Daily steps from health data
            $steps = $this->db->query("
                SELECT DATE(FROM_UNIXTIME(end_time / 1000)) as date,
                       SUM(step_count) as total_steps
                FROM tbl_health_data
                WHERE owner_id = ? AND end_time >= ? AND step_count > 0
                GROUP BY date ORDER BY date ASC
            ", [$userId, $cutoffMs])->getResultArray();

            // Daily avg battery level from activity or battery stats
            $battery = [];
            try {
                $battery = $this->db->query("
                    SELECT DATE(FROM_UNIXTIME(activity_time / 1000)) as date,
                           AVG(battery_level) as avg_battery
                    FROM tbl_extracted_activities
                    WHERE owner_id = ? AND activity_time >= ? AND battery_level IS NOT NULL
                    GROUP BY date ORDER BY date ASC
                ", [$userId, $cutoffMs])->getResultArray();
            } catch (\Throwable $e) {
                log_message('error', 'get_activity_battery_trends battery: ' . $e->getMessage());
            }

            // Merge into date-keyed map
            $map = [];
            foreach ($steps as $s)   $map[$s['date']] = ['date' => $s['date'], 'steps' => (int)$s['total_steps'], 'battery' => null];
            foreach ($battery as $b) {
                if (isset($map[$b['date']])) {
                    $map[$b['date']]['battery'] = round((float)$b['avg_battery'], 1);
                } else {
                    $map[$b['date']] = ['date' => $b['date'], 'steps' => 0, 'battery' => round((float)$b['avg_battery'], 1)];
                }
            }

            $out = array_values($map);
            usort($out, fn($a, $b) => strcmp($a['date'], $b['date']));
            return $out;
        } catch (\Exception $e) {
            log_message('error', 'get_activity_battery_trends error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_count_Clipboard_filtered(int $user_id): int
    {
        try {
            return $this->fq('tbl_extracted_clipboard_entries', $user_id)
                ->where('clip_text IS NOT NULL AND clip_text != ""')
                ->countAllResults();
        } catch (\Exception $e) {
            log_message('error', 'get_count_Clipboard_filtered: ' . $e->getMessage());
            return 0;
        }
    }

    public function get_app_bandwidth_usage(int $userId): array
    {
        try {
            if (!$this->db->tableExists('tbl_data_usage')) {
                return [];
            }
            $usage = $this->fq('tbl_data_usage', $userId)
                ->select('package_name, is_wifi, SUM(rx_bytes) as total_rx, SUM(tx_bytes) as total_tx, SUM(total_bytes) as grand_total')
                ->groupBy(['package_name', 'is_wifi'])
                ->orderBy('grand_total', 'DESC')
                ->limit(30)
                ->get()
                ->getResultArray();

            $result = [];
            foreach ($usage as $row) {
                $pkg = $row['package_name'] ?: 'System / Kernel';
                $rx = (float)($row['total_rx'] ?? 0);
                $tx = (float)($row['total_tx'] ?? 0);
                $total = (float)($row['grand_total'] ?? ($rx + $tx));
                $exfiltrationRatio = $total > 0 ? round($tx / $total, 2) : 0;

                $result[] = [
                    'package_name' => $pkg,
                    'is_wifi' => (bool)$row['is_wifi'],
                    'rx_mb' => round($rx / 1048576, 2),
                    'tx_mb' => round($tx / 1048576, 2),
                    'total_mb' => round($total / 1048576, 2),
                    'exfiltration_risk' => ($exfiltrationRatio > 0.8 && $total > 10485760) ? 'HIGH (Trojan Upload Risk)' : 'Normal',
                ];
            }
            return $result;
        } catch (\Exception $e) {
            log_message('error', 'get_app_bandwidth_usage error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_app_crash_analytics(int $userId): array
    {
        try {
            if (!$this->db->tableExists('tbl_system_crash_logs')) {
                return [];
            }
            $crashes = $this->fq('tbl_system_crash_logs', $userId)
                ->select('package_name, crash_type, exception_class, exception_message, COUNT(*) as crash_count')
                ->groupBy(['package_name', 'crash_type'])
                ->orderBy('crash_count', 'DESC')
                ->limit(20)
                ->get()
                ->getResultArray();

            $analytics = [];
            foreach ($crashes as $c) {
                $count = (int)$c['crash_count'];
                $isANR = str_contains(strtolower($c['crash_type'] ?? ''), 'anr');
                $instabilityScore = ($isANR ? 3 : 2) * $count;

                $analytics[] = [
                    'package_name' => $c['package_name'] ?: 'Unknown App',
                    'crash_type' => $c['crash_type'] ?: 'Fatal Exception',
                    'exception' => $c['exception_class'] ?: 'RuntimeError',
                    'message' => $c['exception_message'] ?: 'Null pointer / memory fault',
                    'count' => $count,
                    'instability_score' => $instabilityScore,
                    'status' => $instabilityScore > 6 ? 'Critical Instability' : 'Moderate',
                ];
            }
            return $analytics;
        } catch (\Exception $e) {
            log_message('error', 'get_app_crash_analytics error: ' . $e->getMessage());
            return [];
        }
    }

    public function get_unused_bloatware_apps(int $userId): array
    {
        try {
            $apps = $this->fq('tbl_extracted_installed_apps', $userId)
                ->select('package_name, app_name, first_install_time, last_update_time')
                ->get()
                ->getResultArray();

            $cutoff30Days = (time() - (30 * 86400)) * 1000;
            $bloatware = [];

            foreach ($apps as $app) {
                $installTime = (int)($app['first_install_time'] ?? 0);
                if ($installTime > 0 && $installTime < $cutoff30Days) {
                    $bloatware[] = [
                        'app_name' => $app['app_name'] ?: $app['package_name'],
                        'package_name' => $app['package_name'],
                        'installed_days_ago' => max(30, (int)round((time() - ($installTime / 1000)) / 86400)),
                        'usage_status' => 'Zero Foreground Usage (Bloatware)',
                    ];
                }
            }
            return array_slice($bloatware, 0, 15);
        } catch (\Exception $e) {
            log_message('error', 'get_unused_bloatware_apps error: ' . $e->getMessage());
            return [];
        }
    }
}

