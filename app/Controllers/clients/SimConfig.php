<?php

namespace App\Controllers\clients;

use App\Models\Mod_SimConfig;
use App\Models\Mod_Log_User_Action;

class SimConfig extends BaseClientController
{
    public function index()
    {
        $model = new Mod_SimConfig();
        $deviceId = $this->request->getGet('device');

        $result = $model->getSimConfigs($this->userId, $deviceId, $this->perPage);

        $counts = $this->getUserDataCounts();
        $is_hardware = true; // sim_configs is a hardware page
        $is_software = false;

        $nav_html = '<div class="d-flex justify-content-end flex-wrap mb-3" style="gap: 8px;">';
        $nav_html .= sprintf(
            '<a class="btn btn-sm %s" href="%s"><i class="fas fa-microchip mr-1"></i> Hardware</a>',
            $is_hardware ? 'btn-primary' : 'btn-outline-secondary',
            base_url('advanced/hardware')
        );
        $nav_html .= sprintf(
            '<a class="btn btn-sm %s" href="%s"><i class="fas fa-laptop-code mr-1"></i> Software</a>',
            $is_software ? 'btn-primary' : 'btn-outline-secondary',
            base_url('advanced/software')
        );
        $nav_html .= '</div>';

        $data = [
            'data'           => $result['rows'],
            'total'          => $result['total'],
            'pager'          => $result['pager'],
            'devices'        => $model->getDistinctDevices($this->userId),
            'selectedDevice' => $deviceId,
            'pag'            => 'sim_configs',
            'sub_pag'        => 'sim_configs',
            'active_tab'     => 'sim_configs',
            'title'          => 'SIM Configs',
            'nav_urls'       => $nav_html,
        ];

        return $this->renderAppView('users/advanced/sim_configs', $data);
    }

    public function delete(int $id)
    {
        if (!$this->request->isAJAX() && !$this->request->getMethod() === 'post') {
            return $this->response->setStatusCode(405)->setJSON(['success' => false, 'error' => 'Method not allowed']);
        }

        $model = new Mod_SimConfig();
        $deleted = $model->deleteSimConfig($id, $this->userId);

        if ($deleted) {
            $logModel = new Mod_Log_User_Action();
            $logModel->logAction([
                'user_id' => $this->userId,
                'action_category' => 'system',
                'action_type' => 'sim_config_delete',
                'action_severity' => 'low',
                'success' => 1,
                'resource_id' => (string) $id,
            ]);
            return $this->response->setJSON(['success' => true, 'message' => 'SIM config record deleted']);
        }

        return $this->response->setStatusCode(404)->setJSON(['success' => false, 'error' => 'Record not found or already deleted']);
    }
}
