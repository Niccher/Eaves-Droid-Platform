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
        $this->db = $db ?? db_connect();
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
        // Aggregate communication volume per contact from SMS + call logs,
        // and map numbers to contact display names where available.
        $sms = $this->db->table('tbl_sms')
            ->select('address, COUNT(*) as sms')
            ->where('owner_id', $userId)
            ->groupBy('address')
            ->get()->getResultArray();

        $calls = $this->db->table('tbl_logs')
            ->select('phone_number, COUNT(*) as calls')
            ->where('owner_id', $userId)
            ->groupBy('phone_number')
            ->get()->getResultArray();

        $volumes = [];
        foreach ($sms as $s) {
            $num = (string)($s['address'] ?? '');
            if ($num === '') continue;
            $volumes[$num] = [
                'number' => $num,
                'name'   => $num,
                'sms'    => (int)$s['sms'],
                'calls'  => 0,
            ];
        }
        foreach ($calls as $c) {
            $num = (string)($c['phone_number'] ?? '');
            if ($num === '') continue;
            if (!isset($volumes[$num])) {
                $volumes[$num] = ['number' => $num, 'name' => $num, 'sms' => 0, 'calls' => 0];
            }
            $volumes[$num]['calls'] = (int)$c['calls'];
        }

        if (empty($volumes)) {
            return [];
        }

        // Resolve display names from the contacts table.
        $numbers = array_keys($volumes);
        $contacts = $this->db->table('tbl_contacts')
            ->select('display_name, phone_numbers')
            ->where('owner_id', $userId)
            ->get()->getResultArray();

        $nameByNumber = [];
        foreach ($contacts as $ct) {
            $nums = $ct['phone_numbers'] ?? '';
            if (is_string($nums)) {
                $decoded = json_decode($nums, true);
                if (is_array($decoded)) {
                    foreach ($decoded as $n) {
                        if (is_string($n) && $n !== '') $nameByNumber[$n] = $ct['display_name'] ?? $n;
                    }
                }
            } elseif (is_array($nums)) {
                foreach ($nums as $n) {
                    if (is_string($n) && $n !== '') $nameByNumber[$n] = $ct['display_name'] ?? $n;
                }
            }
        }

        // Sort by combined volume, take top-N, and score on a 0-100 scale.
        uasort($volumes, fn($a, $b) => ($b['sms'] + $b['calls']) <=> ($a['sms'] + $a['calls']));
        $seeds = array_slice($volumes, 0, $limit, true);

        $maxVol = 1;
        foreach ($seeds as $s) {
            $maxVol = max($maxVol, $s['sms'] + $s['calls']);
        }

        $result = [];
        foreach ($seeds as $num => $s) {
            $total = $s['sms'] + $s['calls'];
            $result[] = [
                'number' => $num,
                'name'   => $nameByNumber[$num] ?? $s['name'],
                'sms'    => $s['sms'],
                'calls'  => $s['calls'],
                'score'  => (int)round(($total / $maxVol) * 100),
            ];
        }

        return $result;
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
            ->select('package_name, foreground_time_ms, last_time_used')
            ->where('owner_id', $userId)
            ->where('last_time_used >=', strtotime($cutoff) * 1000)
            ->get()->getResultArray();

        $toleranceMs = $this->locationToleranceSec * 1000;

        // Pre-sort app-usage timestamps once so per-contact lookups use
        // binary search instead of O(contacts² × usage × timestamps).
        $appTimes = [];
        foreach ($appUsage as $app) {
            $t = (int)($app['last_time_used'] ?? 0);
            if ($t > 0) {
                $appTimes[] = ['ts' => $t, 'pkg' => $app['package_name']];
            }
        }
        usort($appTimes, fn($a, $b) => $a['ts'] <=> $b['ts']);
        $appTimeStamps = array_column($appTimes, 'ts');

        // Map a contact's interaction timestamps to the set of apps used
        // within the tolerance window of each timestamp.
        $contactApps = [];
        foreach ($contactNumbers as $num) {
            $times = $contactTimestamps[$num] ?? [];
            if (empty($times)) {
                $contactApps[$num] = [];
                continue;
            }
            $apps = [];
            foreach ($times as $t) {
                $t = (int)$t;
                // binary search for the first timestamp >= t - tolerance
                $lo = 0;
                $hi = count($appTimeStamps);
                while ($lo < $hi) {
                    $mid = intdiv($lo + $hi, 2);
                    if ($appTimeStamps[$mid] < $t - $toleranceMs) {
                        $lo = $mid + 1;
                    } else {
                        $hi = $mid;
                    }
                }
                for ($i = $lo, $n = count($appTimeStamps); $i < $n; $i++) {
                    if ($appTimeStamps[$i] > $t + $toleranceMs) {
                        break;
                    }
                    $apps[$appTimes[$i]['pkg']] = true;
                }
            }
            $contactApps[$num] = $apps;
        }

        foreach ($contactNumbers as $numA) {
            foreach ($contactNumbers as $numB) {
                if ($numA >= $numB) {
                    continue;
                }
                $appsA = $contactApps[$numA] ?? [];
                $appsB = $contactApps[$numB] ?? [];
                if (empty($appsA) || empty($appsB)) {
                    continue;
                }

                $shared = array_intersect_key($appsA, $appsB);
                $coAppCount = count($shared);
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