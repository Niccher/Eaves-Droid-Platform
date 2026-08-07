<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\Services;

/**
 * RiskScoreService — Computes 0-100 device risk scores from ML findings.
 * 
 * Algorithm:
 * - Weight findings by severity (critical=5, high=3, medium=2, low=1)
 * - Apply recency decay (exponential, half-life configurable)
 * - Normalize to 0-100 scale
 * - Per-category breakdown + severity counts + top findings
 */
class RiskScoreService
{
    private BaseConnection $db;
    
    private int $windowDays;
    private int $halfLifeDays;
    private array $weights;
    
    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? db_connect();
        $this->loadSettings();
    }
    
    private function loadSettings(): void
    {
        $this->windowDays = (int)setting('risk.window_days', 30);
        $this->halfLifeDays = (int)setting('risk.decay_half_life_days', 14);
        $this->weights = [
            'critical' => (int)setting('risk.weights.critical', 5),
            'high'     => (int)setting('risk.weights.high', 3),
            'medium'   => (int)setting('risk.weights.medium', 2),
            'low'      => (int)setting('risk.weights.low', 1),
        ];
    }
    
    /**
     * Compute risk score for all (or specific) user/devices.
     */
    public function computeAll(
        ?int $userId = null,
        ?string $deviceId = null,
        ?int $windowDays = null
    ): int {
        $this->loadSettings();

        if ($userId && !$this->featureEnabled($userId, 'risk_score')) {
            log_message('info', "PlanGate: user #{$userId} risk_score disabled — skipping computeAll");
            return 0;
        }

        $window = $windowDays ?? $this->windowDays;
        $cutoff = date('Y-m-d', strtotime("-{$window} days"));
        
        // Get distinct user/device pairs with ML findings in window
        $builder = $this->db->table('ml_results')
            ->select('user_id, device_id')
            ->where('created_at >=', $cutoff)
            ->groupBy('user_id, device_id');
        
        if ($userId) $builder->where('user_id', $userId);
        if ($deviceId) $builder->where('device_id', $deviceId);
        
        $pairs = $builder->get()->getResultArray();
        
        $computed = 0;
        foreach ($pairs as $pair) {
            $result = $this->computeScore($pair['user_id'], $pair['device_id'], $window);
            if ($this->saveScore($result)) {
                $computed++;
            }
        }
        
        // Also handle devices with NO findings (score = 0)
        if (!$userId && !$deviceId) {
            $computed += $this->setZeroScoresForMissing($window);
        }
        
        log_message('info', "RiskScore: Computed $computed risk scores (window={$window}d)");
        return $computed;
    }
    
    /**
     * Compute risk score for a single user/device.
     */
    public function computeScore(int $userId, string $deviceId, int $windowDays): array
    {
        $this->loadSettings();
        $cutoff = date('Y-m-d', strtotime("-{$windowDays} days"));
        $lambda = log(2) / $this->halfLifeDays;
        
        $findings = $this->db->table('ml_results')
            ->where('user_id', $userId)
            ->where('device_id', $deviceId)
            ->where('created_at >=', $cutoff)
            ->get()->getResultArray();
        
        if (empty($findings)) {
            return $this->emptyScore($userId, $deviceId, $windowDays, 'No ML findings in window');
        }
        
        $categoryScores = [];
        $totalWeighted = 0.0;
        $totalWeight = 0.0;
        $severityCounts = ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0];
        $topFindings = [];
        
        foreach ($findings as $f) {
            $severity = strtolower($f['severity'] ?? 'low');
            $category = $f['category'] ?? 'general';
            $score = (float)($f['score'] ?? 1.0); // ML confidence 0-1
            
            // Recency decay
            $ageDays = (time() - strtotime($f['created_at'])) / 86400;
            $decay = exp(-$lambda * max(0, $ageDays));
            
            $weight = ($this->weights[$severity] ?? 1) * $score * $decay;
            
            $categoryScores[$category] = ($categoryScores[$category] ?? 0) + $weight;
            $totalWeighted += $weight;
            $totalWeight += ($this->weights[$severity] ?? 1) * $decay;
            $severityCounts[$severity]++;
            
            $topFindings[] = [
                'finding_id' => $f['id'] ?? 0,
                'category' => $category,
                'severity' => $severity,
                'title' => $f['title'] ?? '',
                'description' => $f['description'] ?? '',
                'weighted_score' => round($weight, 2),
                'age_days' => round($ageDays, 1),
            ];
        }
        
        // Normalize to 0-100
        $maxPossible = array_sum($this->weights) * 1.0; // perfect score * max decay
        $normalizedScore = $totalWeight > 0 
            ? min(100, (int)round(($totalWeighted / $maxPossible) * 100))
            : 0;
        
        // Per-category breakdown (0-100 each)
        $categoryBreakdown = [];
        foreach ($categoryScores as $cat => $score) {
            $categoryBreakdown[$cat] = min(100, (int)round(($score / $maxPossible) * 100));
        }
        
        // Sort top findings by weighted score
        usort($topFindings, fn($a, $b) => $b['weighted_score'] <=> $a['weighted_score']);
        $topFindings = array_slice($topFindings, 0, 10);
        
        return [
            'user_id' => $userId,
            'device_id' => $deviceId,
            'score' => $normalizedScore,
            'category_breakdown' => $categoryBreakdown,
            'severity_counts' => $severityCounts,
            'top_findings' => $topFindings,
            'computed_at' => date('Y-m-d H:i:s'),
            'window_days' => $windowDays,
            'note' => null,
        ];
    }
    
    private function emptyScore(int $userId, string $deviceId, int $windowDays, string $note): array
    {
        return [
            'user_id' => $userId,
            'device_id' => $deviceId,
            'score' => 0,
            'category_breakdown' => (object)[],
            'severity_counts' => ['critical' => 0, 'high' => 0, 'medium' => 0, 'low' => 0],
            'top_findings' => [],
            'computed_at' => date('Y-m-d H:i:s'),
            'window_days' => $windowDays,
            'note' => $note,
        ];
    }
    
    private function saveScore(array $result): bool
    {
        try {
            $this->db->table('device_risk')->insert([
                'user_id' => $result['user_id'],
                'device_id' => $result['device_id'],
                'score' => $result['score'],
                'category_breakdown' => json_encode($result['category_breakdown']),
                'severity_counts' => json_encode($result['severity_counts']),
                'top_findings' => json_encode($result['top_findings']),
                'window_days' => $result['window_days'],
                'computed_at' => $result['computed_at'],
            ], true); // true = ON DUPLICATE KEY UPDATE

            $this->db->table('device_risk_history')->insert([
                'user_id' => $result['user_id'],
                'device_id' => $result['device_id'],
                'score' => $result['score'],
                'severity_counts' => json_encode($result['severity_counts']),
                'top_findings' => json_encode($result['top_findings']),
                'computed_at' => $result['computed_at'],
            ]);
            
            return true;
        } catch (\Exception $e) {
            log_message('error', 'RiskScore: Failed to save score: ' . $e->getMessage());
            return false;
        }
    }
    
    private function setZeroScoresForMissing(int $windowDays): int
    {
        // Find devices that exist but have no ML findings in window
        $cutoff = date('Y-m-d', strtotime("-{$windowDays} days"));
        
        $devices = $this->db->table('tbl_device_profile dp')
            ->select('dp.owner_id, dp.device_id')
            ->where('dp.device_id NOT IN (
                SELECT DISTINCT device_id FROM ml_results WHERE created_at >= ?
            )', [$cutoff])
            ->get()->getResultArray();
        
        $count = 0;
        foreach ($devices as $d) {
            $result = $this->emptyScore($d['owner_id'], $d['device_id'], $windowDays, 'No ML findings in window');
            if ($this->saveScore($result)) {
                $count++;
            }
        }
        
        return $count;
    }

    /**
     * Check whether a platform feature is enabled for a user's plan.
     */
    private function featureEnabled(int $userId, string $feature): bool
    {
        try {
            $gate = new \App\Services\PlanGate();
            return $gate->hasFeature($userId, $feature);
        } catch (\Throwable $e) {
            log_message('error', 'PlanGate featureEnabled error: ' . $e->getMessage());
            return true;
        }
    }
}