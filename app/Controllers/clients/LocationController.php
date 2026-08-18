<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class LocationController extends BaseClientController
{
    use ResponseTrait;

    /**
     * Redirect to simplified timeline.
     * Route: /location
     */
    public function index()
    {
        return $this->simplified();
    }

    /**
     * Display merged location + activity simplified timeline.
     */
    public function simplified()
    {
        $paired = $this->getPairedEntities($this->userId, $this->perPage);

        $commonData  = $this->getLocationCommonData('location');
        $data = array_merge($commonData, [
            'timeline'          => $paired['timeline'],
            'locations'         => $paired['locations'],
            'activities'        => $paired['activities'],
            'pager'             => $this->finderModel->getPager(),
            'totalLocations'    => $this->finderModel->get_count_paired($this->userId),
            'totalActivities'   => $this->finderModel->get_count_paired($this->userId),
            'current_type'      => 'location',
            'has_coords_filter' => true,
            'currentPage'       => (int)(service('request')->getGet('page') ?? 1),
            'perPage'           => $this->perPage,
        ]);

        return $this->renderAppView('users/location_simplified', $data);
    }

    /**
     * Display locations on Leaflet interactive map.
     */
    public function map()
    {
        $paired = $this->getPairedEntities($this->userId, $this->perPage);

        $commonData = $this->getLocationCommonData('location');
        $data = array_merge($commonData, [
            'locations'      => $paired['locations'],
            'pager'          => $this->finderModel->getPager(),
            'totalLocations' => $this->finderModel->get_count_paired($this->userId),
            'current_type'   => 'map',
        ]);

        return $this->renderAppView('users/location_map', $data);
    }

    /**
     * Display detailed activity recognition timeline & charts.
     */
    public function activities()
    {
        $paired = $this->getPairedEntities($this->userId, $this->perPage);

        $db = \Config\Database::connect();
        
        $typesQuery = $db->table('tbl_extracted_activities a')
            ->select('a.activity_type, COUNT(*) as count')
            ->join('tbl_extracted_locations l', 'l.fetched_at = a.fetched_at AND l.owner_id = a.owner_id')
            ->where('a.owner_id', $this->userId)
            ->where('l.latitude IS NOT NULL')
            ->where('l.longitude IS NOT NULL')
            ->where('l.latitude !=', 0)
            ->where('l.longitude !=', 0);

        if (!empty($this->finderModel->deviceId) && $this->finderModel->deviceId !== 'all') {
            $typesQuery->where('a.device_id', $this->finderModel->deviceId);
        }
        $typesQuery = $typesQuery->groupBy('a.activity_type')
            ->get()
            ->getResultArray();

        $avgBatteryQuery = $db->table('tbl_extracted_activities a')
            ->selectAvg('a.battery_level', 'avg_batt')
            ->join('tbl_extracted_locations l', 'l.fetched_at = a.fetched_at AND l.owner_id = a.owner_id')
            ->where('a.owner_id', $this->userId)
            ->where('l.latitude IS NOT NULL')
            ->where('l.longitude IS NOT NULL')
            ->where('l.latitude !=', 0)
            ->where('l.longitude !=', 0);

        if (!empty($this->finderModel->deviceId) && $this->finderModel->deviceId !== 'all') {
            $avgBatteryQuery->where('a.device_id', $this->finderModel->deviceId);
        }
        $avgBattery = $avgBatteryQuery->get()->getRowArray();

        $commonData = $this->getLocationCommonData('activities');
        $data = array_merge($commonData, [
            'activities'      => $paired['activities'],
            'pager'           => $this->finderModel->getPager(),
            'totalActivities' => $this->finderModel->get_count_paired($this->userId),
            'activity_stats'  => [
                'types'       => $typesQuery,
                'avg_battery' => round($avgBattery['avg_batt'] ?? 0),
            ],
            'current_type'    => 'activities',
        ]);

        return $this->renderAppView('users/activity_details', $data);
    }

    /**
     * Helper to unpack paired location & activity entities.
     */
    private function getPairedEntities(int $user_id, int $perPage): array
    {
        $pairedData = $this->finderModel->get_paired_location_activities($user_id, $perPage);
        $locations = [];
        $activities = [];
        $timeline = [];

        foreach ($pairedData as $row) {
            $loc = [
                'counter'           => $row['loc_counter'],
                'latitude'          => $row['latitude'],
                'longitude'         => $row['longitude'],
                'altitude'          => $row['altitude'],
                'accuracy'          => $row['accuracy'],
                'speed'             => $row['speed'],
                'bearing'           => $row['bearing'],
                'provider'          => $row['provider'],
                'location_time'     => $row['location_time'],
                'fetched_at'        => $row['fetched_at'],
                'satellite_count'   => $row['satellite_count'],
                'hdop'              => $row['hdop'],
                'vdop'              => $row['vdop'],
                'pdop'              => $row['pdop'],
                'gnss_status'       => $row['gnss_status'],
                'speed_kmh'         => $row['speed_kmh'],
                'vertical_accuracy' => $row['vertical_accuracy'],
                'floor_level'       => $row['floor_level'],
                'building_id'       => $row['building_id'],
                'indoor_level'      => $row['indoor_level'],
            ];
            $act = [
                'counter'         => $row['act_counter'],
                'activity_type'   => $row['activity_type'],
                'confidence'      => $row['confidence'],
                'battery_level'   => $row['battery_level'],
                'charging_status' => $row['charging_status'],
                'network_type'    => $row['network_type'],
                'screen_on'       => $row['screen_on'],
                'activity_time'   => $row['activity_time'],
                'extracted_at'    => $row['act_extracted_at'],
                'fetched_at'      => $row['fetched_at'],
            ];
            $timeline[] = [
                'type'    => 'merged',
                'loc'     => $loc,
                'act'     => $act,
                'sort_ts' => (int)($loc['location_time'] ?? $loc['fetched_at'] ?? 0),
            ];
            $locations[] = $loc;
            $activities[] = $act;
        }

        return [
            'timeline'   => $timeline,
            'locations'  => $locations,
            'activities' => $activities,
        ];
    }

    /**
     * Get common data for LocationController/Activity views.
     */
    private function getLocationCommonData(string $type): array
    {
        return array_merge($this->getUserDataCounts(), [
            'pag'   => $type === 'location' ? 'location' : 'activities',
            'title' => $type === 'location' ? 'Location & Activity' : 'Device Activity',
        ]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_location((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'LocationController entry deleted successfully.']);
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
