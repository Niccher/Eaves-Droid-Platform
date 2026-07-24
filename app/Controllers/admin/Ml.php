<?php

namespace App\Controllers\admin;

class Ml extends BaseAdminController
{
    public function index()
    {
        $db = $this->getDb();

        $saved = [];
        $rows = $db->table('settings')->where('class', 'ml')->get()->getResultArray();
        foreach ($rows as $r) {
            $saved[$r['key']] = $r['value'];
        }

        return $this->renderView('admin/ml', [
            'pag' => 'admin-ml',
            'settings' => $saved,
        ]);
    }
}
