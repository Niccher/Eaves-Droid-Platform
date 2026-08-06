<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\Services;

/**
 * CarePlanService — Generates actionable care plans from risk scores,
 * ML findings, SMS/call/location data, and behavioral anomalies.
 */
class CarePlanService
{
    private BaseConnection $db;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Services::database();
    }

    /**
     * Get the latest risk score for a user's device.
     */
    public function current(int $userId, ?string $deviceId = null): ?array
    {
        $builder = $this->db->table('device_risk')
            ->where('user_id', $userId)
            ->orderBy('computed_at', 'DESC')
            ->limit(1);

        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }

        $row = $builder->get()->getRowArray();

        if ($row === null) {
            return null;
        }

        return [
            'user_id' => (int)$row['user_id'],
            'device_id' => $row['device_id'],
            'score' => (int)$row['score'],
            'category_breakdown' => json_decode($row['category_breakdown'] ?? '{}', true) ?: [],
            'severity_counts' => json_decode($row['severity_counts'] ?? '{}', true) ?: [],
            'top_findings' => json_decode($row['top_findings'] ?? '[]', true) ?: [],
            'computed_at' => $row['computed_at'],
        ];
    }

    /**
     * Get risk score trend over time for a user/device.
     */
    public function trend(int $userId, ?string $deviceId = null, int $days = 90): array
    {
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$days} days"));

        $builder = $this->db->table('device_risk_history')
            ->where('user_id', $userId)
            ->where('computed_at >=', $cutoff)
            ->orderBy('computed_at', 'ASC');

        if ($deviceId) {
            $builder->where('device_id', $deviceId);
        }

        $rows = $builder->get()->getResultArray();

        $trend = [];
        foreach ($rows as $row) {
            $trend[] = [
                'computed_at' => $row['computed_at'],
                'score' => (int)$row['score'],
                'severity_counts' => json_decode($row['severity_counts'] ?? '{}', true) ?: [],
            ];
        }

        return $trend;
    }

    /**
     * Compute the percentile of a score against the user cohort.
     */
    public function percentile(int $score, int $userId): array
    {
        $scores = $this->db->table('device_risk')
            ->select('MAX(score) as max_score')
            ->where('user_id !=', $userId)
            ->groupBy('user_id')
            ->get()->getResultArray();

        $cohortScores = array_map(fn($s) => (int)$s['max_score'], $scores);
        $total = count($cohortScores);

        if ($total === 0) {
            return ['percentile' => 50, 'cohort_size' => 0, 'score' => $score];
        }

        $below = count(array_filter($cohortScores, fn($s) => $s < $score));
        $percentile = (int)round(($below / $total) * 100);

        return [
            'percentile' => $percentile,
            'cohort_size' => $total,
            'score' => $score,
        ];
    }

    /**
     * Generate a prioritized action plan (Platinum tier).
     */
    public function actionPlan(int $userId, ?string $deviceId = null, ?array $risk = null): array
    {
        if ($risk === null) {
            $risk = $this->current($userId, $deviceId);
        }

        if ($risk === null) {
            return [];
        }

        $actions = [];
        $score = (int)$risk['score'];
        $severityCounts = $risk['severity_counts'] ?? [];
        $topFindings = $risk['top_findings'] ?? [];

        // 1. Forensic red flags from top findings
        foreach ($topFindings as $finding) {
            $severity = strtolower($finding['severity'] ?? 'low');
            if (in_array($severity, ['critical', 'high'])) {
                $actions[] = [
                    'priority' => $severity === 'critical' ? 'critical' : 'high',
                    'type' => 'forensic',
                    'title' => 'Investigate forensic finding: ' . ($finding['title'] ?? 'Unknown'),
                    'description' => $finding['description'] ?? '',
                    'evidence_count' => 1,
                    'category' => 'forensic',
                ];
            }
        }

        // 2. Recurring late-night contacts (23:00-05:00)
        $cutoffMs = strtotime(date('Y-m-d')) * 1000;
        $lateNightSms = $this->db->table('tbl_sms')
            ->select('address, COUNT(*) as count')
            ->where('owner_id', $userId)
            ->where('sms_date >=', $cutoffMs)
            ->where('HOUR(FROM_UNIXTIME(sms_date/1000)) >=', 23)
            ->where('HOUR(FROM_UNIXTIME(sms_date/1000)) <=', 5)
            ->groupBy('address')
            ->orderBy('count', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        foreach ($lateNightSms as $sms) {
            $actions[] = [
                'priority' => 'medium',
                'type' => 'late_night_contact',
                'title' => 'Recurring late-night contact with ' . $sms['address'],
                'description' => $sms['count'] . ' SMS messages between 11 PM and 5 AM',
                'evidence_count' => (int)$sms['count'],
                'category' => 'communication',
            ];
        }

        // 3. Geo exit from home zone
        $geoEvents = $this->db->table('tbl_geo_events')
            ->where('user_id', $userId)
            ->where('event_type', 'exit')
            ->orderBy('created_at', 'DESC')
            ->limit(5)
            ->get()->getResultArray();

        foreach ($geoEvents as $event) {
            $actions[] = [
                'priority' => 'medium',
                'type' => 'geo_exit',
                'title' => 'Left designated safe zone',
                'description' => 'Geo exit event detected near ' . ($event['zone_name'] ?? 'unknown zone'),
                'evidence_count' => 1,
                'category' => 'geolocation',
            ];
        }

        // 4. Behavioral anomalies
        $anomalies = $this->db->table('tbl_anomaly_alerts')
            ->where('user_id', $userId)
            ->where('created_at >=', date('Y-m-d H:i:s', strtotime('-7 days')))
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->get()->getResultArray();

        foreach ($anomalies as $anomaly) {
            $actions[] = [
                'priority' => 'low',
                'type' => 'behavioral',
                'title' => 'Behavioral anomaly detected',
                'description' => $anomaly['description'] ?? $anomaly['alert_type'] ?? 'Unknown anomaly',
                'evidence_count' => 1,
                'category' => 'behavioral',
            ];
        }

        // 5. Score-based priority summary
        if ($score >= 70) {
            $actions[] = [
                'priority' => 'critical',
                'type' => 'score_alert',
                'title' => 'Critical risk score: ' . $score . '/100',
                'description' => 'Device risk score is in the critical range. Immediate review recommended.',
                'evidence_count' => $score,
                'category' => 'score',
            ];
        } elseif ($score >= 40) {
            $actions[] = [
                'priority' => 'high',
                'type' => 'score_alert',
                'title' => 'High risk score: ' . $score . '/100',
                'description' => 'Device risk score is in the high range. Review recommended.',
                'evidence_count' => $score,
                'category' => 'score',
            ];
        } elseif ($score >= 20) {
            $actions[] = [
                'priority' => 'medium',
                'type' => 'score_alert',
                'title' => 'Moderate risk score: ' . $score . '/100',
                'description' => 'Device risk score is in the moderate range. Monitor for changes.',
                'evidence_count' => $score,
                'category' => 'score',
            ];
        }

        usort($actions, fn($a, $b) => $this->priorityValue($b['priority']) <=> $this->priorityValue($a['priority']));

        return $actions;
    }

    private function priorityValue(string $priority): int
    {
        return match ($priority) {
            'critical' => 4,
            'high' => 3,
            'medium' => 2,
            'low' => 1,
            default => 0,
        };
    }
}