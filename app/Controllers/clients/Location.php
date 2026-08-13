<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Location extends BaseClientController
{
    use ResponseTrait;

    /**
     * Redirect to simplified timeline.
     * Route: /location
     */
    public function index()
    {
        return redirect()->to(base_url('location'));
    }

    /**
     * Display merged location + activity simplified timeline.
     */
    public function simplified()
    {
        $hasCoords = $this->request->getGet('has_coords') === '1';

        $locations = $this->finderModel->get_locations($this->userId, $this->perPage, $hasCoords);
        $activities = $this->finderModel->get_activities($this->userId, $this->perPage);

        $timeline = $this->mergeTimeline($locations, $activities);

        $commonData = $this->getLocationCommonData('location');

        $data = array_merge($commonData, [
            'timeline'           => $timeline,
            'pager'              => $this->finderModel->getPager(),
            'totalLocations'     => $this->finderModel->get_count_Location($this->userId, $hasCoords),
            'totalActivities'    => $this->finderModel->get_count_Activity($this->userId),
            'current_type'       => 'location',
            'has_coords_filter'  => $hasCoords,
        ]);

        return $this->renderAppView('users/location_simplified', $data);
    }

    /**
     * Display locations on Leaflet interactive map.
     */
    public function map()
    {
        $locations = $this->finderModel->get_locations($this->userId, $this->perPage, true);

        $commonData = $this->getLocationCommonData('location');

        $data = array_merge($commonData, [
            'locations'         => $locations,
            'pager'             => $this->finderModel->getPager(),
            'totalLocations'    => $this->finderModel->get_count_Location($this->userId, true),
            'current_type'      => 'map',
        ]);

        return $this->renderAppView('users/location_map', $data);
    }

    /**
     * Display detailed activity recognition timeline & charts.
     */
    public function activities()
    {
        $activities = $this->finderModel->get_activities($this->userId, $this->perPage);

        $db = \Config\Database::connect();
        $typesQuery = $db->table('tbl_activity')
            ->select('activity_type, COUNT(*) as count')
            ->where('owner_id', $this->userId)
            ->groupBy('activity_type')
            ->get()
            ->getResultArray();

        $avgBattery = $db->table('tbl_activity')
            ->selectAvg('battery_level', 'avg_batt')
            ->where('owner_id', $this->userId)
            ->get()
            ->getRowArray();

        $commonData = $this->getLocationCommonData('activities');
        $data = array_merge($commonData, [
            'activities'         => $activities,
            'pager'              => $this->finderModel->getPager(),
            'totalActivities'    => $this->finderModel->get_count_Activity($this->userId),
            'activity_stats'     => [
                'types'          => $typesQuery,
                'avg_battery'    => round($avgBattery['avg_batt'] ?? 0),
            ],
            'current_type'       => 'activities',
        ]);

        return $this->renderAppView('users/activity_details', $data);
    }

    /**
     * Merge locations and activities into unified cards so a location and the
     * activity captured at the same moment appear together.
     *
     * Pairing priority (each activity is consumed at most once):
     *   1. exact `fetched_at` match  — the device records location + activity at
     *      the same millisecond, so this is the precise "same time" join;
     *   2. nearest activity within ±5 min of the location fix time;
     *   3. same extraction batch (`extracted_at`) fallback — highest confidence.
     *
     * Unmatched activities still render as activity-only cards.
     *
     * @return array[] list of unified cards
     */
    private function mergeTimeline(array $locations, array $activities): array
    {
        $windowMs = 5 * 60 * 1000;

        // Index activities by primary key so each is consumed at most once.
        $actsById = [];
        foreach ($activities as $act) {
            $key = (string)($act['counter'] ?? '');
            if ($key === '') {
                $key = spl_object_hash((object)$act);
            }
            $actsById[$key] = $act;
        }
        $used = [];

        $cards = [];
        foreach ($locations as $loc) {
            $bestKey   = null;
            $locFetched = (string)($loc['fetched_at'] ?? '');
            $locTime   = (int)($loc['location_time'] ?? 0);
            $locBatch  = (string)($loc['extracted_at'] ?? '');

            // Pass 1: exact same-moment match on fetched_at.
            if ($bestKey === null && $locFetched !== '') {
                foreach ($actsById as $key => $act) {
                    if (isset($used[$key])) continue;
                    if ((string)($act['fetched_at'] ?? '') === $locFetched) {
                        $bestKey = $key;
                        break;
                    }
                }
            }

            // Pass 2: nearest activity within the time window of the location fix.
            if ($bestKey === null && $locTime > 0) {
                $bestDist = PHP_INT_MAX;
                foreach ($actsById as $key => $act) {
                    if (isset($used[$key])) continue;
                    $actTime = (int)($act['activity_time'] ?? 0);
                    if ($actTime <= 0) continue;
                    $dist = abs($actTime - $locTime);
                    if ($dist <= $windowMs && $dist < $bestDist) {
                        $bestDist = $dist;
                        $bestKey  = $key;
                    }
                }
            }

            // Pass 3: same extraction batch fallback — highest confidence.
            if ($bestKey === null && $locBatch !== '') {
                $batchActs = [];
                foreach ($actsById as $key => $act) {
                    if (isset($used[$key])) continue;
                    if ((string)($act['extracted_at'] ?? '') === $locBatch) {
                        $batchActs[$key] = $act;
                    }
                }
                if (!empty($batchActs)) {
                    uasort($batchActs, function ($a, $b) {
                        return (int)($b['confidence'] ?? 0) <=> (int)($a['confidence'] ?? 0);
                    });
                    $bestKey = array_key_first($batchActs);
                }
            }

            $cards[] = [
                'type'    => 'merged',
                'loc'     => $loc,
                'act'     => $bestKey !== null ? $actsById[$bestKey] : null,
                'sort_ts' => (int)($loc['location_time'] ?? $loc['extracted_at'] ?? 0),
            ];

            if ($bestKey !== null) {
                $used[$bestKey] = true;
            }
        }

        // Activities that could not be attached to any location on this page
        // still need to be visible — render them as activity-only cards.
        foreach ($actsById as $key => $act) {
            if (isset($used[$key])) continue;
            $cards[] = [
                'type'    => 'merged',
                'loc'     => null,
                'act'     => $act,
                'sort_ts' => (int)($act['activity_time'] ?? $act['extracted_at'] ?? 0),
            ];
        }

        usort($cards, fn($a, $b) => $b['sort_ts'] - $a['sort_ts']);

        return $cards;
    }

    /**
     * Get common data for Location/Activity views.
     */
    private function getLocationCommonData(string $type): array
    {
        return array_merge($this->getUserDataCounts(), [
            'pag' => $type === 'location' ? 'location' : 'activities',
            'title' => $type === 'location' ? 'Location & Activity' : 'Device Activity',
        ]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_location((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Location entry deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete location entry.']);
    }

    public function deleteActivity($id)
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_activity((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Activity entry deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete activity entry.']);
    }
}
