<?php

namespace App\Controllers\admin;

use CodeIgniter\API\ResponseTrait;

class SecuritySettingsController extends BaseAdminController
{
    use ResponseTrait;

    private function requirePermission(string $permission)
    {
        $user = auth()->user();
        if ($user && ($user->inGroup('superadmin') || $user->can('admin.settings') || $user->can($permission))) {
            return null;
        }

        $this->logAdminAction('permission_denied', 'medium', false, [
            'new_values' => json_encode(['uri' => current_url(), 'required_permission' => $permission]),
        ]);
        return redirect()->to('admin/dashboard')->with('error', 'You do not have permission to access this page.');
    }


    public function api_settings()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'api',
        ]);

        $saved = [];
        $rows = $db->table('settings')->where('class', 'api')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/api', [
            'pag' => 'admin-settings-api',
            'settings' => $saved,
        ]);
    }

    public function security_settings()
    {
        $db = $this->getDb();

        $this->logAdminAction('settings_view', 'low', true, [
            'section' => 'security',
        ]);

        $saved = [];
        $rows = $db->table('settings')->where('class', 'security')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/settings/security', [
            'pag' => 'admin-settings-security',
            'settings' => $saved,
        ]);
    }
}
