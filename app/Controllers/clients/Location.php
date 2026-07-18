<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Location extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display all locations history.
     * Route: /location
     */
    public function index()
    {
        $hasCoords = $this->request->getGet('has_coords') === '1';
        $locations = $this->finderModel->get_locations($this->userId, $this->perPage, $hasCoords);
        $commonData = $this->getLocationCommonData('location');

        $data = array_merge($commonData, [
            'location_dump' => $locations,
            'pager' => $this->finderModel->getPager(),
            'totalLocations' => $this->finderModel->get_count_Location($this->userId, $hasCoords),
            'current_type' => 'location',
            'has_coords_filter' => $hasCoords,
        ]);

        return $this->renderAppView('users/location_all', $data);
    }

    /**
     * Display all device activities history.
     * Route: /activities
     */
    public function activities()
    {
        $activities = $this->finderModel->get_activities($this->userId, $this->perPage);
        $commonData = $this->getLocationCommonData('activity');

        $data = array_merge($commonData, [
            'activity_dump' => $activities,
            'pager' => $this->finderModel->getPager(),
            'totalActivities' => $this->finderModel->get_count_Activity($this->userId),
            'current_type' => 'activity',
        ]);

        return $this->renderAppView('users/activity_all', $data);
    }

    /**
     * Get common data for Location/Activity views.
     */
    private function getLocationCommonData(string $type): array
    {
        return array_merge($this->getUserDataCounts(), [
            'pag' => $type === 'location' ? 'location' : 'activities',
            'title' => $type === 'location' ? 'Location History' : 'Device Activity',
        ]);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_location((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Location entry deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete location entry.']);
    }

    public function deleteActivity($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_activity((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'Activity entry deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete activity entry.']);
    }
}
