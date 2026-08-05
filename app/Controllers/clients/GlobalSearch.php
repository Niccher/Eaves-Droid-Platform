<?php

namespace App\Controllers\clients;

class GlobalSearch extends BaseClientController
{
    private const PER_PAGE = 25;

    private const TABS = [
        'sms'      => ['label' => 'SMS',        'icon' => 'fas fa-sms',           'badge' => 'primary',  'color' => 'primary'],
        'calls'    => ['label' => 'Calls',       'icon' => 'fas fa-phone-alt',    'badge' => 'info',     'color' => 'info'],
        'contacts' => ['label' => 'Contacts',    'icon' => 'fas fa-id-card',      'badge' => 'secondary','color' => 'secondary'],
        'files'    => ['label' => 'Files',       'icon' => 'fas fa-file',         'badge' => 'dark',     'color' => 'dark'],
        'apps'     => ['label' => 'Apps',        'icon' => 'fab fa-android',      'badge' => 'secondary','color' => 'secondary'],
    ];

    /**
     * Overview page — stat cards + tab pills.
     *
     * @return string
     */
    public function index(): string
    {
        $query = trim((string) $this->request->getGet('q'));

        if (empty($query)) {
            return redirect()->to('home');
        }

        // Log user global search
        $logModel = new \App\Models\Mod_Log_User_Action();
        $logModel->logAction([
            'user_id'         => $this->userId,
            'action_category' => 'search',
            'action_type'     => 'global_search',
            'action_severity' => 'low',
            'success'         => 1,
            'new_values'      => json_encode(['keyword' => $query]),
            'request_url'     => current_url(),
        ]);

        $counts = $this->getCounts($query);
        $totalCount = array_sum($counts);

        $data = [
            'pag'        => 'globalsearch',
            'query'      => $query,
            'counts'     => $counts,
            'totalCount' => $totalCount,
            'activeTab'  => null,
            'tabs'       => self::TABS,
        ];

        return $this->renderUserView('users/globalsearch', $data);
    }

    /**
     * Per-tab paginated results page.
     *
     * @return string
     */
    public function search(string $tab = 'sms'): string
    {
        $query = trim((string) $this->request->getGet('q'));

        if (empty($query)) {
            return redirect()->to('global-search');
        }

        if (!array_key_exists($tab, self::TABS)) {
            return redirect()->to('global-search?q=' . urlencode($query));
        }

        // Log user global search tab view
        $logModel = new \App\Models\Mod_Log_User_Action();
        $logModel->logAction([
            'user_id'         => $this->userId,
            'action_category' => 'search',
            'action_type'     => 'global_search_tab',
            'action_severity' => 'low',
            'success'         => 1,
            'new_values'      => json_encode(['keyword' => $query, 'tab' => $tab]),
            'request_url'     => current_url(),
        ]);

        $counts = $this->getCounts($query);
        $totalCount = array_sum($counts);

        $perPage  = self::PER_PAGE;
        $page     = (int) ($this->request->getGet('page') ?? 1);
        $page     = max(1, $page);
        $offset   = ($page - 1) * $perPage;
        $total    = (int) ($counts[$tab] ?? 0);

        $method = 'search_' . $tab;
        $rows   = $this->finderModel->$method($this->userId, $query, $perPage, $offset);

        $pager = \Config\Services::pager();
        $pager->makeLinks($page, $perPage, $total, 'bootstrap5_full');

        $data = [
            'pag'        => 'globalsearch',
            'query'      => $query,
            'counts'     => $counts,
            'totalCount' => $totalCount,
            'activeTab'  => $tab,
            'tabLabel'   => self::TABS[$tab]['label'],
            'tabIcon'    => self::TABS[$tab]['icon'],
            'tabBadge'   => self::TABS[$tab]['badge'],
            'tabs'       => self::TABS,
            'rows'       => $rows,
            'pager'      => $pager,
            'currentPage'=> $page,
            'totalRows'  => $total,
            'perPage'    => $perPage,
        ];

        return $this->renderUserView('users/globalsearch_tab', $data);
    }

    /**
     * Fetch count for every search table.
     *
     * @return array<string, int>
     */
    private function getCounts(string $query): array
    {
        return [
            'sms'      => $this->finderModel->search_sms_count($this->userId, $query),
            'calls'    => $this->finderModel->search_calls_count($this->userId, $query),
            'contacts' => $this->finderModel->search_contacts_count($this->userId, $query),
            'files'    => $this->finderModel->search_files_count($this->userId, $query),
            'apps'     => $this->finderModel->search_apps_count($this->userId, $query),
        ];
    }
}