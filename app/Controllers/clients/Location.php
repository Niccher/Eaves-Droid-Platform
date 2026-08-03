<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Location extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display merged location + activity timeline.
     * Route: /location
     */
    public function index()
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

        return $this->renderAppView('users/location_all', $data);
    }

    /**
     * Legacy route - redirect to merged view.
     * Route: /activities
     */
    public function activities()
    {
        return redirect()->to(base_url('location'));
    }

    /**
     * Merge locations and activities into a single time-sorted array.
     */
    private function mergeTimeline(array $locations, array $activities): array
    {
        $locs = array_map(function ($loc) {
            $loc['_type'] = 'location';
            $loc['_sort_time'] = (int)($loc['activity_time'] ?? $loc['extracted_at'] ?? $loc['location_time'] ?? 0);
            return $loc;
        }, $locations);

        $acts = array_map(function ($act) {
            $act['_type'] = 'activity';
            $act['_sort_time'] = (int)($act['activity_time'] ?? $act['extracted_at'] ?? 0);
            return $act;
        }, $activities);

        $merged = array_merge($locs, $acts);

        usort($merged, function ($a, $b) {
            return $b['_sort_time'] - $a['_sort_time'];
        });

        return $merged;
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
