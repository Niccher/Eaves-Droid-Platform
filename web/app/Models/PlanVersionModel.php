<?php

namespace App\Models;

use CodeIgniter\Model;

class PlanVersionModel extends Model
{
    protected $table = 'plan_versions';
    protected $primaryKey = 'id';
    protected $allowedFields = [
        'plan_id', 'version', 'price_monthly_cents', 'price_yearly_cents', 'currency',
        'max_devices', 'history_days', 'features', 'ml_algorithms',
        'alert_email', 'alert_push', 'wellbeing_depth', 'support_tier',
        'stripe_price_id_monthly', 'stripe_price_id_yearly',
        'paddle_price_id_monthly', 'paddle_price_id_yearly',
        'effective_from', 'effective_until', 'change_reason', 'changed_by',
    ];
    protected $useTimestamps = true;
    protected $dateFormat = 'datetime';
    protected $createdField = 'created_at';
    protected $updatedField = '';

    /**
     * Create a new version (superadmin only).
     * Auto-increments version, expires previous current version.
     */
    public function createVersion(int $planId, array $data, int $adminId): array
    {
        // Get latest version
        $latest = $this->where('plan_id', $planId)
            ->orderBy('version', 'DESC')
            ->limit(1)
            ->first();
        
        $newVersion = ($latest['version'] ?? 0) + 1;
        $now = date('Y-m-d H:i:s');

        // Expire current version
        if ($latest) {
            $this->update($latest['id'], ['effective_until' => $now]);
        }

        // Normalize JSON fields
        if (isset($data['features']) && is_array($data['features'])) {
            $data['features'] = json_encode($data['features']);
        }
        if (isset($data['ml_algorithms']) && is_array($data['ml_algorithms'])) {
            $data['ml_algorithms'] = json_encode($data['ml_algorithms']);
        }
        if (isset($data['price_monthly'])) {
            $data['price_monthly_cents'] = (int)($data['price_monthly'] * 100);
            unset($data['price_monthly']);
        }
        if (isset($data['price_yearly'])) {
            $data['price_yearly_cents'] = (int)($data['price_yearly'] * 100);
            unset($data['price_yearly']);
        }

        // Create new version
        $insertData = array_merge($data, [
            'plan_id' => $planId,
            'version' => $newVersion,
            'effective_from' => $data['effective_from'] ?? date('Y-m-d H:i:s'),
            'changed_by' => $adminId,
        ]);

        $this->insert($insertData);
        $newId = $this->insertID();

        return $this->find($newId);
    }

    /**
     * Preview what a version change would look like.
     */
    public function previewVersion(int $planId, array $proposedData): array
    {
        $current = $this->where('plan_id', $planId)
            ->where('effective_from <=', date('Y-m-d H:i:s'))
            ->groupStart()
                ->where('effective_until IS NULL')
                ->orWhere('effective_until >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->orderBy('version', 'DESC')
            ->first();

        return [
            'current' => $current,
            'proposed' => array_merge($current ?? [], $proposedData),
            'diff' => $this->diffVersions($current, $proposedData),
        ];
    }

    private function diffVersions(?array $old, array $new): array
    {
        if (!$old) return ['created' => $new];
        $diff = [];
        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $diff[$key] = ['from' => $old[$key] ?? null, 'to' => $value];
            }
        }
        return $diff;
    }

    public function getCurrentVersion(int $planId): ?array
    {
        return $this->where('plan_id', $planId)
            ->where('effective_from <=', date('Y-m-d H:i:s'))
            ->groupStart()
                ->where('effective_until IS NULL')
                ->orWhere('effective_until >', date('Y-m-d H:i:s'))
            ->groupEnd()
            ->orderBy('version', 'DESC')
            ->first();
    }

    public function getVersionHistory(int $planId): array
    {
        return $this->where('plan_id', $planId)
            ->orderBy('version', 'DESC')
            ->findAll();
    }

    /**
     * Build a human-readable diff between two version rows.
     *
     * @return array<int,array{field:string,label:string,from:string,to:string}>
     */
    public function buildVersionDiff(array $old, array $new): array
    {
        $diff = [];

        $priceMonthlyOld = (int)($old['price_monthly_cents'] ?? 0) / 100;
        $priceMonthlyNew = (int)($new['price_monthly_cents'] ?? 0) / 100;
        if ($priceMonthlyOld !== $priceMonthlyNew) {
            $diff[] = ['field' => 'monthly', 'label' => 'Monthly Price', 'from' => '$' . number_format($priceMonthlyOld, 2), 'to' => '$' . number_format($priceMonthlyNew, 2)];
        }

        $priceYearlyOld = (int)($old['price_yearly_cents'] ?? 0) / 100;
        $priceYearlyNew = (int)($new['price_yearly_cents'] ?? 0) / 100;
        if ($priceYearlyOld !== $priceYearlyNew) {
            $diff[] = ['field' => 'yearly', 'label' => 'Yearly Price', 'from' => '$' . number_format($priceYearlyOld, 2), 'to' => '$' . number_format($priceYearlyNew, 2)];
        }

        $fields = [
            'max_devices' => 'Max Devices',
            'history_days' => 'History Days',
            'alert_email' => 'Email Alerts',
            'alert_push' => 'Push Alerts',
            'wellbeing_depth' => 'Wellbeing Depth',
            'support_tier' => 'Support Tier',
        ];
        foreach ($fields as $f => $label) {
            if (($old[$f] ?? null) !== ($new[$f] ?? null)) {
                $val = fn($r) => match ($f) {
                    'alert_email', 'alert_push' => !empty($r[$f]) ? 'Enabled' : 'Disabled',
                    'wellbeing_depth' => ($r[$f] ?? '') === 'all' ? 'All Data' : (($r[$f] ?? '') . ' Days'),
                    'support_tier' => ucfirst($r[$f] ?? 'standard'),
                    default => (string)($r[$f] ?? '—'),
                };
                $diff[] = ['field' => $f, 'label' => $label, 'from' => $val($old), 'to' => $val($new)];
            }
        }

        $featuresOld = json_decode($old['features'] ?? '[]', true) ?: [];
        $featuresNew = json_decode($new['features'] ?? '[]', true) ?: [];
        $featureKeys = array_unique(array_merge(array_keys($featuresOld), array_keys($featuresNew)));
        foreach ($featureKeys as $k) {
            $oldV = $featuresOld[$k] ?? false;
            $newV = $featuresNew[$k] ?? false;
            if ($oldV !== $newV) {
                $diff[] = ['field' => 'feat_' . $k, 'label' => 'Feature: ' . ucfirst(str_replace('_', ' ', $k)), 'from' => $oldV ? 'Enabled' : 'Disabled', 'to' => $newV ? 'Enabled' : 'Disabled'];
            }
        }

        $algosOld = json_decode($old['ml_algorithms'] ?? '[]', true) ?: [];
        $algosNew = json_decode($new['ml_algorithms'] ?? '[]', true) ?: [];
        $added = array_values(array_diff($algosNew, $algosOld));
        $removed = array_values(array_diff($algosOld, $algosNew));
        foreach ($added as $a) {
            $diff[] = ['field' => 'algo_' . $a, 'label' => 'Algorithm', 'from' => '—', 'to' => ucfirst($a)];
        }
        foreach ($removed as $a) {
            $diff[] = ['field' => 'algo_' . $a, 'label' => 'Algorithm', 'from' => ucfirst($a), 'to' => '—'];
        }

        return $diff;
    }
}