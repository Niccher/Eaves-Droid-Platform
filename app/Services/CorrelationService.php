<?php

namespace App\Services;

use CodeIgniter\Database\BaseConnection;
use CodeIgniter\Config\Services;

/**
 * CorrelationService — Cross-category relationship graph linking contacts,
 * calls, SMS, location co-occurrence, and shared app usage.
 */
class CorrelationService
{
    private BaseConnection $db;

    private int $locationToleranceSec;
    private int $maxNodes;
    private int $maxEdges;
    private float $edgeThreshold;

    public function __construct(?BaseConnection $db = null)
    {
        $this->db = $db ?? Services::database();
        $this->locationToleranceSec = (int)setting('correlation.location_tolerance_sec', 600);
        $this->maxNodes = (int)setting('correlation.max_nodes', 25);
        $this->maxEdges = (int)setting('correlation.max_edges', 60);
        $this->edgeThreshold = (float)setting('correlation.edge_threshold', 0.1);
    }

    /**
     * Build a relationship graph: nodes (contacts) and edges (relationships).
     */
    public function buildGraph(int $userId, array $opts = []): array
    {
        $limit = (int)($opts['limit'] ?? $this->maxNodes);
        $maxEdges = (int)($opts['max_edges'] ?? $this->maxEdges);
        $threshold = (float)($opts['threshold'] ?? $this->edgeThreshold);

        $seeds = $this->getSocialGraphSeeds($userId, $limit);
        if (empty($seeds)) {
            return ['nodes' => [], 'edges' => [], 'stats' => ['total_contacts' => 0, 'total_edges' => 0]];
        }

        $nodes = [];
        $nodeIndex = [];
        foreach ($seeds as $i => $seed) {
            $nodeId = 'c' . $i;
            $nodeIndex[$seed['number']] = $nodeId;
            $nodes[] = [
                'id' => $nodeId,
                'label' => $seed['name'] ?? $seed['number'],
                'number' => $seed['number'],
                'score' => (int)$seed['score'],
                'sms_count' => (int)$seed['sms'],
                'call_count' => (int)$seed['calls'],
            ];
        }

        $edges = [];
        $edgeSet = [];

        $contactNumbers = array_keys($nodeIndex);
        $coLocationPairs = $this->getCoLocationPairs($userId, $contactNumbers, $limit);
        $coAppPairs = $this->getCoAppPairs($userId, $contactNumbers, $limit);

        foreach ($seeds as $i => $seedA) {
            foreach ($seeds as $j => $seedB) {
                if ($i >= $j) {
                    continue;
                }

                $numA = $seedA['number'];
                $numB = $seedB['number'];

                $smsWeight = 0;
                $callWeight = 0;
                $cooccurrenceWeight = 0;
                $coactivityWeight = 0;
                $appWeight = 0;

                $smsWeight = ($seedA['sms'] + $seedB['sms']) > 0 ? min($seedA['sms'], $seedB['sms']) : 0;
                $callWeight = ($seedA['calls'] + $seedB['calls']) > 0 ? min($seedA['calls'], $seedB['calls']) : 0;

                $pairKey = $numA . '|' . $numB;
                if (isset($coLocationPairs[$pairKey])) {
                    $cooccurrenceWeight = $coLocationPairs[$pairKey];
                }
                if (isset($coAppPairs[$pairKey])) {
                    $appWeight = $coAppPairs[$pairKey];
                }

                $coactivityWeight = $this->getCoActivityScore($userId, $numA, $numB);

                $totalWeight = $smsWeight * 1 + $callWeight * 5 + $cooccurrenceWeight * 3 + $coactivityWeight * 2 + $appWeight * 2;

                if ($totalWeight < $threshold) {
                    continue;
                }

                $edgeId = $nodeIndex[$numA] . '-' . $nodeIndex[$numB];
                if (isset($edgeSet[$edgeId])) {
                    continue;
                }
                $edgeSet[$edgeId] = true;

                $edges[] = [
                    'source' => $nodeIndex[$numA],
                    'target' => $nodeIndex[$numB],
                    'weight' => (int)$totalWeight,
                    'sms' => (int)$smsWeight,
                    'calls' => (int)$callWeight,
                    'cooccurrence' => (int)$cooccurrenceWeight,
                    'coactivity' => (int)$coactivityWeight,
                    'app_usage' => (int)$appWeight,
                ];

                if (count($edges) >= $maxEdges) {
                    break 2;
                }
            }
        }

        usort($edges, fn($a, $b) => $b['weight'] <=> $a['weight']);

        return [
            'nodes' => $nodes,
            'edges' => $edges,
            'stats' => [
                'total_contacts' => count($nodes),
                'total_edges' => count($edges),
            ],
        ];
    }

    /**
     * Flat ranked list of strongest relationships with explainable reason strings.
     */
    public function topLinks(int $userId, int $limit = 20): array
    {
        $graph = $this->buildGraph($userId, ['limit' => 50, 'max_edges' => 200, 'threshold' => 0]);
        $edges = $graph['edges'];
        usort($edges, fn($a, $b) => $b['weight'] <=> $a['weight']);
        return array_slice($edges, 0, $limit);
    }

    /**
     * Detect unexpected clusters of contacts using simple co-occurrence density.
     */
    public function clusterContacts(int $userId): array
    {
        $graph = $this->buildGraph($userId, ['limit' => 50, 'max_edges' => 200, 'threshold' => 0]);
        $edges = $graph['edges'];
        $nodes = $graph['nodes'];

        $adjacency = [];
        foreach ($nodes as $n) {
            $adjacency[$n['id']] = [];
        }
        foreach ($edges as $e) {
            $adjacency[$e['source']][] = $e['target'];
            $adjacency[$e['target']][] = $e['source'];
        }

        $visited = [];
        $clusters = [];

        foreach ($nodes as $n) {
            if (isset($visited[$n['id']])) {
                continue;
            }
            $cluster = [];
            $queue = [$n['id']];
            while (!empty($queue)) {
                $current = array_shift($queue);
                if (isset($visited[$current])) {
                    continue;
                }
                $visited[$current] = true;
                $cluster[] = $current;
                foreach ($adjacency[$current] ?? [] as $neighbor) {
                    if (!isset($visited[$neighbor])) {
                        $queue[] = $neighbor;
                    }
                }
            }
            if (count($cluster) >= 2) {
                $clusters[] = $cluster;
            }
        }

        usort($clusters, fn($a, $b) => count($b) <=> count($a));

        $result = [];
        foreach ($clusters as $cluster) {
            $nodeMap = [];
            foreach ($nodes as $n) {
                if (in_array($n['id'], $cluster)) {
                    $nodeMap[$n['id']] = $n;
                }
            }
            $result[] = [
                'size' => count($cluster),
                'contacts' => array_values($nodeMap),
            ];
        }

        return $result;
    }

    private function getSocialGraphSeeds(int $userId, int $limit): array
    {
        return $this->db->table('tbl_sms')
            ->select('address, COUNT(*) as count')
            ->where('owner_id', $userId)
            ->groupBy('address')
            ->get()->getResultArray();
    }

    private function getCoLocationPairs(int $userId, array $contactNumbers, int $limit): array
    {
        $pairs = [];
        $sinceDays = 30;
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$sinceDays} days"));

        $smsTimes = $this->db->table('tbl_sms')
            ->select('address, sms_date')
            ->where('owner_id', $userId)
            ->where('sms_date >=', strtotime($cutoff) * 1000)
            ->get()->getResultArray();

        $callTimes = $this->db->table('tbl_logs')
            ->select('phone_number, call_date')
            ->where('owner_id', $userId)
            ->where('call_date >=', strtotime($cutoff) * 1000)
            ->get()->getResultArray();

        $contactTimestamps = [];
        foreach ($smsTimes as $s) {
            $contactTimestamps[$s['address']][] = (int)$s['sms_date'];
        }
        foreach ($callTimes as $c) {
            $contactTimestamps[$c['phone_number']][] = (int)$c['call_date'];
        }

        $locations = $this->db->table('tbl_location')
            ->select('latitude, longitude, location_time')
            ->where('owner_id', $userId)
            ->where('location_time >=', strtotime($cutoff) * 1000)
            ->get()->getResultArray();

        $toleranceMs = $this->locationToleranceSec * 1000;

        foreach ($contactNumbers as $numA) {
            foreach ($contactNumbers as $numB) {
                if ($numA >= $numB) {
                    continue;
                }
                $timesA = $contactTimestamps[$numA] ?? [];
                $timesB = $contactTimestamps[$numB] ?? [];
                if (empty($timesA) || empty($timesB)) {
                    continue;
                }

                $coLocCount = 0;
                foreach ($timesA as $tA) {
                    foreach ($timesB as $tB) {
                        if (abs($tA - $tB) <= $toleranceMs) {
                            $coLocCount++;
                            break;
                        }
                    }
                }

                if ($coLocCount > 0) {
                    $key = $numA . '|' . $numB;
                    $pairs[$key] = $coLocCount;
                }
            }
        }

        return $pairs;
    }

    private function getCoAppPairs(int $userId, array $contactNumbers, int $limit): array
    {
        $pairs = [];
        $sinceDays = 30;
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$sinceDays} days"));

        $smsTimes = $this->db->table('tbl_sms')
            ->select('address, sms_date')
            ->where('owner_id', $userId)
            ->where('sms_date >=', strtotime($cutoff) * 1000)
            ->get()->getResultArray();

        $callTimes = $this->db->table('tbl_logs')
            ->select('phone_number, call_date')
            ->where('owner_id', $userId)
            ->where('call_date >=', strtotime($cutoff) * 1000)
            ->get()->getResultArray();

        $contactTimestamps = [];
        foreach ($smsTimes as $s) {
            $contactTimestamps[$s['address']][] = (int)$s['sms_date'];
        }
        foreach ($callTimes as $c) {
            $contactTimestamps[$c['phone_number']][] = (int)$c['call_date'];
        }

        $appUsage = $this->db->table('tbl_app_usage')
            ->select('package_name, foreground_time_ms, usage_date')
            ->where('owner_id', $userId)
            ->where('usage_date >=', $cutoff)
            ->get()->getResultArray();

        $toleranceMs = $this->locationToleranceSec * 1000;

        foreach ($contactNumbers as $numA) {
            foreach ($contactNumbers as $numB) {
                if ($numA >= $numB) {
                    continue;
                }
                $timesA = $contactTimestamps[$numA] ?? [];
                $timesB = $contactTimestamps[$numB] ?? [];
                if (empty($timesA) || empty($timesB)) {
                    continue;
                }

                $sharedApps = [];
                foreach ($timesA as $tA) {
                    foreach ($appUsage as $app) {
                        if (abs($tA - (int)$app['usage_date']) <= $toleranceMs) {
                            $sharedApps[$app['package_name']] = ($sharedApps[$app['package_name']] ?? 0) + 1;
                        }
                    }
                }
                foreach ($timesB as $tB) {
                    foreach ($appUsage as $app) {
                        if (abs($tB - (int)$app['usage_date']) <= $toleranceMs) {
                            $sharedApps[$app['package_name']] = ($sharedApps[$app['package_name']] ?? 0) + 1;
                        }
                    }
                }

                $coAppCount = count(array_filter($sharedApps, fn($c) => $c >= 2));
                if ($coAppCount > 0) {
                    $key = $numA . '|' . $numB;
                    $pairs[$key] = $coAppCount;
                }
            }
        }

        return $pairs;
    }

    private function getCoActivityScore(int $userId, string $numA, string $numB): int
    {
        $sinceDays = 30;
        $cutoff = date('Y-m-d H:i:s', strtotime("-{$sinceDays} days"));
        $cutoffMs = strtotime($cutoff) * 1000;

        $smsA = $this->db->table('tbl_sms')
            ->where('owner_id', $userId)
            ->where('address', $numA)
            ->where('sms_date >=', $cutoffMs)
            ->countAllResults();

        $smsB = $this->db->table('tbl_sms')
            ->where('owner_id', $userId)
            ->where('address', $numB)
            ->where('sms_date >=', $cutoffMs)
            ->countAllResults();

        if ($smsA === 0 || $smsB === 0) {
            return 0;
        }

        $daysA = $this->db->table('tbl_sms')
            ->select('DISTINCT DATE(FROM_UNIXTIME(sms_date/1000)) as day')
            ->where('owner_id', $userId)
            ->where('address', $numA)
            ->where('sms_date >=', $cutoffMs)
            ->get()->getResultArray();

        $daysB = $this->db->table('tbl_sms')
            ->select('DISTINCT DATE(FROM_UNIXTIME(sms_date/1000)) as day')
            ->where('owner_id', $userId)
            ->where('address', $numB)
            ->where('sms_date >=', $cutoffMs)
            ->get()->getResultArray();

        $daysASet = array_column($daysA, 'day');
        $daysBSet = array_column($daysB, 'day');
        $sharedDays = count(array_intersect($daysASet, $daysBSet));

        return $sharedDays;
    }
}