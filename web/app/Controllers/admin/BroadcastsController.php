<?php

namespace App\Controllers\admin;

class BroadcastsController extends BaseAdminController
{
    private function getBroadcasts(): array
    {
        $db = $this->getDb();
        $row = $db->table('settings')
            ->where('class', 'system')
            ->where('key', 'system_broadcasts')
            ->get()
            ->getRowArray();

        if ($row && !empty($row['value'])) {
            $data = json_decode($row['value'], true);
            return is_array($data) ? $data : [];
        }

        return [];
    }

    private function saveBroadcasts(array $broadcasts): void
    {
        $db = $this->getDb();
        $existing = $db->table('settings')
            ->where('class', 'system')
            ->where('key', 'system_broadcasts')
            ->get()
            ->getRowArray();

        $payload = json_encode(array_values($broadcasts), JSON_PRETTY_PRINT);

        if ($existing) {
            $db->table('settings')
                ->where('class', 'system')
                ->where('key', 'system_broadcasts')
                ->update(['value' => $payload, 'updated_at' => date('Y-m-d H:i:s')]);
        } else {
            $db->table('settings')->insert([
                'class'      => 'system',
                'key'        => 'system_broadcasts',
                'value'      => $payload,
                'type'       => 'json',
                'context'    => 'admin',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s'),
            ]);
        }
    }

    public function index(): string
    {
        $broadcasts = $this->getBroadcasts();

        return $this->renderView('admin/broadcasts', [
            'pag'        => 'admin-broadcasts',
            'broadcasts' => $broadcasts,
        ]);
    }

    public function send()
    {
        $title    = trim($this->request->getPost('title') ?? '');
        $message  = trim($this->request->getPost('message') ?? '');
        $type     = trim($this->request->getPost('type') ?? 'info');
        $target   = trim($this->request->getPost('target') ?? 'all');
        $expires  = trim($this->request->getPost('expires_at') ?? '');

        if (empty($title) || empty($message)) {
            return redirect()->back()->with('error', 'Broadcast title and message are required.');
        }

        $validTypes = ['info', 'warning', 'danger', 'success'];
        if (!in_array($type, $validTypes)) {
            $type = 'info';
        }

        $broadcasts = $this->getBroadcasts();

        $newBroadcast = [
            'id'         => time() . '_' . substr(md5(uniqid()), 0, 6),
            'title'      => $title,
            'message'    => $message,
            'type'       => $type,
            'target'     => $target,
            'active'     => true,
            'created_at' => date('Y-m-d H:i:s'),
            'created_by' => $this->userData['username'] ?? 'Admin',
            'expires_at' => !empty($expires) ? $expires : null,
        ];

        array_unshift($broadcasts, $newBroadcast);
        $this->saveBroadcasts($broadcasts);

        $this->logAdminAction('broadcast_created', 'medium', true, [
            'title'  => $title,
            'type'   => $type,
            'target' => $target,
        ]);

        return redirect()->to(base_url('admin/broadcasts'))->with('message', 'Broadcast message published successfully.');
    }

    public function toggle(string $id)
    {
        $broadcasts = $this->getBroadcasts();
        foreach ($broadcasts as &$b) {
            if ($b['id'] === $id) {
                $b['active'] = !($b['active'] ?? true);
                break;
            }
        }
        $this->saveBroadcasts($broadcasts);

        return redirect()->to(base_url('admin/broadcasts'))->with('message', 'Broadcast status updated.');
    }

    public function delete(string $id)
    {
        $broadcasts = $this->getBroadcasts();
        $broadcasts = array_filter($broadcasts, fn($b) => $b['id'] !== $id);
        $this->saveBroadcasts($broadcasts);

        return redirect()->to(base_url('admin/broadcasts'))->with('message', 'Broadcast announcement deleted.');
    }
}
