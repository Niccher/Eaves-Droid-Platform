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
        $locations = $this->finderModel->get_locations($this->userId, $this->perPage);
        $commonData = $this->getLocationCommonData('location');

        $data = array_merge($commonData, [
            'location_dump' => $locations,
            'pager' => $this->finderModel->getPager(),
            'totalLocations' => $this->finderModel->get_count_Location($this->userId),
            'current_type' => 'location'
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
            'current_type' => 'activity'
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

}
