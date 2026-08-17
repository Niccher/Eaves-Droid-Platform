<?php

namespace App\Controllers\superadmin;

use App\Controllers\superadmin\BaseSuperadminController;
use App\Models\PlanModel;
use App\Models\PlanVersionModel;

class Plans extends BaseSuperadminController
{
    protected PlanModel $plans;
    protected PlanVersionModel $versions;

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void {
        parent::initController($request, $response, $logger);
        $this->plans = new PlanModel();
        $this->versions = new PlanVersionModel();
        helper('form');
    }

    public function index()
    {
        $plans = $this->plans->findAll();
        $currentVersions = $this->plans->getAllCurrentVersions();
        
        return $this->renderView('superadmin/plans/index', [
            'pag' => 'superadmin-plans',
            'plans' => $plans,
            'versions' => $currentVersions,
        ]);
    }

    public function editVersion(int $planId)
    {
        $plan = $this->plans->find($planId);
        if (!$plan) {
            return redirect()->to('/superadmin/plans')->with('error', 'Plan not found');
        }
        
        $current = $this->versions
            ->where('plan_id', $planId)
            ->where('effective_from <=', date('Y-m-d H:i:s'))
            ->groupStart()
                ->where('effective_until IS NULL')
                ->orWhere('effective_until >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->orderBy('version', 'DESC')
            ->first();

        return $this->renderView('superadmin/plans/edit_version', [
            'pag' => 'superadmin-plans',
            'plan' => $plan,
            'current' => $current,
            'featuresList' => $this->getFeatureDefinitions(),
            'algorithmsList' => $this->getAlgorithmDefinitions(),
            'wellbeingDepthOptions' => [
                '3' => '3 Days',
                '7' => '7 Days',
                '14' => '14 Days',
                '21' => '21 Days',
                '30' => '30 Days',
                '60' => '60 Days',
                '90' => '90 Days',
                '120' => '120 Days',
                '180' => '180 Days',
                'all' => 'All Data',
            ],
            'supportTierOptions' => ['standard' => 'Standard', 'priority' => 'Priority 24/7'],
        ]);
    }

    public function updateVersion(int $planId)
    {
        $adminId = auth()->id();
        $data = $this->request->getPost();
        
        // Normalize JSON fields
        $features = [];
        foreach ($this->getFeatureDefinitions() as $feat) {
            $features[$feat['key']] = (bool)($data['feature_' . $feat['key']] ?? false);
        }
        $features['hardware_profile'] = $data['hardware_profile'] ?? 'basic';
        $features['software_profile'] = $data['software_profile'] ?? 'basic';

        $algorithms = [];
        foreach ($this->getAlgorithmDefinitions() as $algo) {
            if (!empty($data['algo_' . $algo['key']])) {
                $algorithms[] = $algo['key'];
            }
        }

        $versionData = [
            'price_monthly_cents' => (int)($data['price_monthly'] * 100),
            'price_yearly_cents' => (int)($data['price_yearly'] * 100),
            'currency' => $data['currency'] ?? 'USD',
            'max_devices' => (int)$data['max_devices'],
            'history_days' => (int)$data['history_days'],
            'features' => json_encode($features),
            'ml_algorithms' => json_encode($algorithms),
            'alert_email' => !empty($data['alert_email']) ? 1 : 0,
            'alert_push' => !empty($data['alert_push']) ? 1 : 0,
            'wellbeing_depth' => $data['wellbeing_depth'] ?? '7day',
            'support_tier' => $data['support_tier'] ?? 'standard',
            'stripe_price_id_monthly' => $data['stripe_monthly'] ?? null,
            'stripe_price_id_yearly' => $data['stripe_yearly'] ?? null,
            'effective_from' => $data['effective_from'] ?? date('Y-m-d H:i:s'),
            'change_reason' => $data['change_reason'] ?? '',
        ];

        // Preview first
        if ($this->request->getPost('action') === 'preview') {
            $preview = $this->versions->previewVersion($planId, $versionData);
            return $this->response->setJSON($preview);
        }

        // Apply
        $result = $this->versions->createVersion($planId, $versionData, $adminId);

        $plan = $this->plans->find($planId);
        $planName = $plan['name'] ?? "Plan #{$planId}";

        return redirect()->to('/superadmin/plans')
            ->with('success', "Version {$result['version']} created for {$planName}");
    }

    public function versionHistory(int $planId)
    {
        $plan = $this->plans->find($planId);
        if (!$plan) {
            return redirect()->to('/superadmin/plans')->with('error', 'Plan not found');
        }
        
        $history = $this->versions->getVersionHistory($planId);

        // Build a readable diff between each version and the one before it
        $diffs = [];
        $prev = null;
        foreach ($history as $v) {
            $diffs[$v['id']] = $prev ? $this->versions->buildVersionDiff($prev, $v) : null;
            $prev = $v;
        }
        
        return $this->renderView('superadmin/plans/history', [
            'pag' => 'superadmin-plans',
            'plan' => $plan,
            'history' => $history,
            'diffs' => $diffs,
        ]);
    }

    private function getFeatureDefinitions(): array
    {
        return [
            ['key' => 'risk_score', 'label' => 'Device Risk Score', 'desc' => 'ML-based risk scoring'],
            ['key' => 'geofencing', 'label' => 'Location Safety & Geofencing', 'desc' => 'Places, transitions, alerts'],
            ['key' => 'push_notifications', 'label' => 'Push Notifications', 'desc' => 'Real-time FCM alerts'],
            ['key' => 'forensic_export', 'label' => 'Forensic/Audit Export', 'desc' => 'Full data export'],
            ['key' => 'wellbeing', 'label' => 'Wellbeing & Lifestyle Reports', 'desc' => 'Lifestyle insights'],
            ['key' => 'smart_timeline', 'label' => 'Smart Timeline', 'desc' => 'AI-powered timeline intelligence'],
            ['key' => 'correlation', 'label' => 'Correlation Analysis', 'desc' => 'Cross-event correlation engine'],
            ['key' => 'care_plan', 'label' => 'Care Plans', 'desc' => 'Structured care plan management'],
            
            // FCM Data Fetch Actions
            ['key' => 'fcm_fetch_contacts', 'label' => 'FCM: Fetch Contacts', 'desc' => 'Extract device phonebook contacts'],
            ['key' => 'fcm_cmd_beep', 'label' => 'FCM: Test Beep', 'desc' => 'Trigger audible test alert beep on device'],
            ['key' => 'fcm_cmd_health', 'label' => 'FCM: Check Device Health', 'desc' => 'Fetch real-time hardware diagnostics (battery, screen, network)'],
            ['key' => 'fcm_fetch_apps', 'label' => 'FCM: Fetch Apps', 'desc' => 'Extract list of installed applications'],
            ['key' => 'fcm_fetch_calls', 'label' => 'FCM: Fetch Calls', 'desc' => 'Extract call log database'],
            ['key' => 'fcm_fetch_sms', 'label' => 'FCM: Fetch SMS', 'desc' => 'Extract text message database'],
            ['key' => 'fcm_fetch_location', 'label' => 'FCM: Fetch Location & Activity', 'desc' => 'Track GPS coordinates and user motion activity'],
            ['key' => 'fcm_fetch_usage', 'label' => 'FCM: Fetch App Usage & Notifications', 'desc' => 'Extract package screen time and notification streams'],
            ['key' => 'fcm_cmd_camera', 'label' => 'FCM: Remote Camera Capture', 'desc' => 'Command remote camera snapshot'],
            ['key' => 'fcm_cmd_audio', 'label' => 'FCM: Remote Audio Record', 'desc' => 'Command remote microphone audio clip recording'],
            ['key' => 'fcm_fetch_files', 'label' => 'FCM: Fetch Files', 'desc' => 'Browse and extract device filesystem files'],
            ['key' => 'fcm_fetch_soft_misc', 'label' => 'FCM: Fetch Misc Software details', 'desc' => 'Extract accounts, calendar, and clipboard data'],
            ['key' => 'fcm_fetch_hard_misc', 'label' => 'FCM: Fetch Misc Hardware details', 'desc' => 'Extract bluetooth devices, sensors, and thermal metrics'],
            ['key' => 'fcm_fetch_all', 'label' => 'FCM: Sync All Data', 'desc' => 'Execute complete device extraction backup sync'],

            // FCM Device Management Actions
            ['key' => 'fcm_cmd_reset_app', 'label' => 'FCM: Reset App Command', 'desc' => 'Allow remote application reset to defaults'],
            ['key' => 'fcm_cmd_deactivate', 'label' => 'FCM: Deactivate Command', 'desc' => 'Allow remote lock and stealth dummy deactivation'],
            ['key' => 'fcm_cmd_logout', 'label' => 'FCM: Logout User Command', 'desc' => 'Allow remote user log out and session clear'],
            ['key' => 'fcm_cmd_uninstall_preserve', 'label' => 'FCM: Uninstall & Preserve Command', 'desc' => 'Allow remote uninstall but keep backup data'],
            ['key' => 'fcm_cmd_uninstall_wipe', 'label' => 'FCM: Uninstall & Wipe Command', 'desc' => 'Allow remote uninstall with complete data wipe'],
        ];
    }
    private function getAlgorithmDefinitions(): array
    {
        return [
            ['key' => 'core', 'label' => 'Core (8 algorithms) — Basic statistical detection', 'tier' => 'free'],
            ['key' => 'advanced', 'label' => 'Advanced (10 algorithms) — PHP-ML pattern analysis', 'tier' => 'gold'],
            ['key' => 'deep', 'label' => 'Deep (7 algorithms) — Full Python neural models', 'tier' => 'platinum'],
        ];
    }
}