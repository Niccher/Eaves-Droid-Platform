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
    /**
     * Display merged location + activity simplified timeline.
     */
    public function simplified()
    {
        $locations = $this->finderModel->get_locations($this->userId, $this->perPage, false);
        $activities = $this->finderModel->get_activities($this->userId, $this->perPage * 2);

        $timeline = $this->mergeTimeline($locations, $activities);

        $commonData = $this->getLocationCommonData('location');

        $data = array_merge($commonData, [
            'timeline'           => $timeline,
            'pager'              => $this->finderModel->getPager(),
            'totalLocations'     => $this->finderModel->get_count_Location($this->userId, false),
            'totalActivities'    => $this->finderModel->get_count_Activity($this->userId),
            'current_type'       => 'location',
            'has_coords_filter'  => false,
        ]);

        return $this->renderAppView('users/location_simplified', $data);
    }

    /**
     * Display locations on Leaflet interactive map.
     */
    public function map()
    {
        $locations = $this->finderModel->get_locations($this->userId, $this->perPage, true);
        $activities = $this->finderModel->get_activities($this->userId, $this->perPage * 3);

        $actsByFetched = [];
        foreach ($activities as $act) {
            $fetched = (string)($act['fetched_at'] ?? '');
            if ($fetched !== '') {
                $actsByFetched[$fetched] = $act;
            }
        }

        $filteredLocations = [];
        foreach ($locations as $loc) {
            $locFetched = (string)($loc['fetched_at'] ?? '');
            if ($locFetched !== '' && isset($actsByFetched[$locFetched])) {
                $filteredLocations[] = $loc;
            }
        }

        $commonData = $this->getLocationCommonData('location');

        $data = array_merge($commonData, [
            'locations'         => $filteredLocations,
            'pager'             => $this->finderModel->getPager(),
            'totalLocations'    => count($filteredLocations),
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
        $locations = $this->finderModel->get_locations($this->userId, $this->perPage * 3, false);

        $locsByFetched = [];
        foreach ($locations as $loc) {
            $fetched = (string)($loc['fetched_at'] ?? '');
            if ($fetched !== '') {
                $locsByFetched[$fetched] = $loc;
            }
        }

        $filteredActivities = [];
        foreach ($activities as $act) {
            $actFetched = (string)($act['fetched_at'] ?? '');
            if ($actFetched !== '' && isset($locsByFetched[$actFetched])) {
                $filteredActivities[] = $act;
            }
        }

        $db = \Config\Database::connect();
        $typesQuery = $db->table('tbl_extracted_activities')
            ->select('activity_type, COUNT(*) as count')
            ->where('owner_id', $this->userId)
            ->groupBy('activity_type')
            ->get()
            ->getResultArray();

        $avgBattery = $db->table('tbl_extracted_activities')
            ->selectAvg('battery_level', 'avg_batt')
            ->where('owner_id', $this->userId)
            ->get()
            ->getRowArray();

        $commonData = $this->getLocationCommonData('activities');
        $data = array_merge($commonData, [
            'activities'         => $filteredActivities,
            'pager'              => $this->finderModel->getPager(),
            'totalActivities'    => count($filteredActivities),
            'activity_stats'     => [
                'types'          => $typesQuery,
                'avg_battery'    => round($avgBattery['avg_batt'] ?? 0),
            ],
            'current_type'       => 'activities',
        ]);

        return $this->renderAppView('users/activity_details', $data);
    }

    /**
     * Merge locations and activities into unified cards strictly by identical
     * fetched_at values. Treats unpaired records as nulls / filters them.
     *
     * @return array[] list of unified cards
     */
    private function mergeTimeline(array $locations, array $activities): array
    {
        $actsByFetched = [];
        foreach ($activities as $act) {
            $fetched = (string)($act['fetched_at'] ?? '');
            if ($fetched !== '') {
                $actsByFetched[$fetched] = $act;
            }
        }

        $cards = [];
        foreach ($locations as $loc) {
            $locFetched = (string)($loc['fetched_at'] ?? '');
            if ($locFetched !== '' && isset($actsByFetched[$locFetched])) {
                $cards[] = [
                    'type'    => 'merged',
                    'loc'     => $loc,
                    'act'     => $actsByFetched[$locFetched],
                    'sort_ts' => (int)($loc['location_time'] ?? $loc['extracted_at'] ?? 0),
                ];
            }
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

    public function deletePaired($fetchedAt = null)
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if (empty($fetchedAt)) {
            return $this->response->setJSON(['success' => false, 'message' => 'Fetched timestamp is required.']);
        }
        if ($this->finderModel->delete_paired_location_activity($fetchedAt, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Entry deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete entry.']);
    }
}
