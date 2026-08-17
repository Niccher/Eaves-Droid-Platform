<?php

namespace App\Controllers\clients;

use App\Models\Mod_Blocklist;
use App\Models\Mod_Log_User_Action;

class Blocklist extends BaseClientController
{
    public function index()
    {
        // Ensure table exists (graceful self-migration)
        $this->ensureTable();

        $modBlocklist = new Mod_Blocklist();
        $userId = $this->userId; // Set by BaseClientController via auth()->user()->id

        $data = array_merge($this->getUserDataCounts(), [
            'pag'     => 'settings',
            'sub_pag' => 'blocklist',
            'title'   => 'Manage Blocklist',
            'blocks'  => [
                'sms'          => $modBlocklist->getUserBlocks($userId, 'sms'),
                'call'         => $modBlocklist->getUserBlocks($userId, 'call'),
                'notification' => $modBlocklist->getUserBlocks($userId, 'notification'),
                'app_usage'    => $modBlocklist->getUserBlocks($userId, 'app_usage'),
            ]
        ]);

        return $this->renderUserView('users/settings/blocklist', $data);
    }

    public function add()
    {
        $this->ensureTable();

        $userId     = $this->userId;
        $category   = $this->request->getPost('category');
        $identifier = trim($this->request->getPost('identifier') ?? '');
        $description = trim($this->request->getPost('description') ?? '');

        if (empty($category) || empty($identifier)) {
            session()->setFlashdata('error', 'Category and Identifier are required.');
            return redirect()->to('/analysis/blocklist');
        }

        $modBlocklist = new Mod_Blocklist();
        if ($modBlocklist->addBlock($userId, $category, $identifier, $description)) {
            session()->setFlashdata('success', '"' . $identifier . '" has been blocked.');
            $logModel = new Mod_Log_User_Action();
            $logModel->logAction([
                'user_id' => $this->userId,
                'action_category' => 'system',
                'action_type' => 'blocklist_add',
                'action_severity' => 'low',
                'success' => 1,
                'new_values' => json_encode(['category' => $category, 'identifier' => $identifier]),
            ]);
        } else {
            session()->setFlashdata('error', 'Failed to add block rule. It may already exist.');
        }

        return redirect()->to('/analysis/blocklist');
    }

    public function delete($id)
    {
        $this->ensureTable();
        $modBlocklist = new Mod_Blocklist();

        if ($modBlocklist->removeBlock((int) $id, $this->userId)) {
            session()->setFlashdata('success', 'Block rule removed successfully.');
            $logModel = new Mod_Log_User_Action();
            $logModel->logAction([
                'user_id' => $this->userId,
                'action_category' => 'system',
                'action_type' => 'blocklist_remove',
                'action_severity' => 'low',
                'success' => 1,
                'resource_id' => (string) $id,
            ]);
        } else {
            session()->setFlashdata('error', 'Failed to remove block rule.');
        }

        return redirect()->to('/analysis/blocklist');
    }

    /**
     * Ensure tbl_user_blocklists exists; create it on-the-fly if missing.
     */
    private function ensureTable(): void
    {
        try {
            $db = \Config\Database::connect();
            if (!$db->tableExists('tbl_user_blocklists')) {
                $db->query("
                    CREATE TABLE IF NOT EXISTS `tbl_user_blocklists` (
                        `id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
                        `owner_id` INT(11) UNSIGNED NOT NULL,
                        `category` ENUM('sms','call','notification','app_usage','location') NOT NULL,
                        `identifier` VARCHAR(255) NOT NULL,
                        `description` VARCHAR(255) DEFAULT NULL,
                        `created_at` DATETIME DEFAULT NULL,
                        `updated_at` DATETIME DEFAULT NULL,
                        PRIMARY KEY (`id`),
                        KEY `owner_id` (`owner_id`),
                        KEY `category` (`category`),
                        UNIQUE KEY `uq_block` (`owner_id`, `category`, `identifier`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
                ");
            }
        } catch (\Exception $e) {
            log_message('error', 'Blocklist ensureTable error: ' . $e->getMessage());
        }
    }
}
