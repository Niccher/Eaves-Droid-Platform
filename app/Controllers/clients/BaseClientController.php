<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Models\Mod_Finder;
use CodeIgniter\HTTP\RedirectResponse;

class BaseClientController extends BaseController
{
    protected $finderModel;
    protected $userData;
    protected $userId;
    protected $perPage = 50; // Default items per page

    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ) {
        parent::initController($request, $response, $logger);

        // Force login
        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            return redirect()->to('login')->send();
        }

        $this->finderModel = new Mod_Finder();

        $this->userData = $this->finderModel->basic_user();
        if (!$this->userData || !isset($this->userData['id'])) {
            session()->setFlashdata('error', 'User session invalid');
            return redirect()->to('login')->send();
        }

        $this->userId = (int) $this->userData['id'];
    }

    protected function getUserDataCounts(): array
    {
        return [
            'total_apps'     => $this->finderModel->get_count_Apps($this->userId),
            'total_contacts' => $this->finderModel->get_count_Contacts($this->userId),
            'total_sms'      => $this->finderModel->get_count_Sms($this->userId),
            'total_calls'    => $this->finderModel->get_count_Calls($this->userId),
            'active_sms'     => $this->finderModel->get_sms_active($this->userId),
            'active_calls'   => $this->finderModel->get_calls_active($this->userId),
        ];
    }

    /**
     * Get navigation URLs for SMS views
     */
    protected function getSmsNavigationUrls(string $activeView = 'all'): string
    {
        $buttons = [
            'all' => ($activeView === 'all') ? 'btn-primary' : 'btn-outline-primary',
            'inbox' => ($activeView === 'inbox') ? 'btn-primary' : 'btn-outline-primary',
            'sent' => ($activeView === 'sent') ? 'btn-primary' : 'btn-outline-primary'
        ];

        return '
            <a class="btn ' . $buttons['all'] . '" href="' . base_url("sms") . '">All</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['inbox'] . '" href="' . base_url("sms/inbox") . '">Inbox</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['sent'] . '" href="' . base_url("sms/sent") . '">Sent</a>
            &nbsp;&nbsp;';
    }

    /**
     * Get navigation URLs for Call views
     */
    protected function getCallNavigationUrls(string $activeView = 'all'): string
    {
        $buttons = [
            'all'      => ($activeView === 'all') ? 'btn-primary' : 'btn-outline-primary',
            'incoming' => ($activeView === 'incoming') ? 'btn-primary' : 'btn-outline-primary',
            'outgoing' => ($activeView === 'outgoing') ? 'btn-primary' : 'btn-outline-primary',
            'rejected' => ($activeView === 'rejected') ? 'btn-primary' : 'btn-outline-primary',
            'blocked'  => ($activeView === 'blocked') ? 'btn-primary' : 'btn-outline-primary'
        ];

        return '
            <a class="btn ' . $buttons['all'] . '" href="' . base_url("call_logs") . '">All</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['incoming'] . '" href="' . base_url("call_logs/incoming") . '">Incoming</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['outgoing'] . '" href="' . base_url("call_logs/outgoing") . '">Outgoing</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['rejected'] . '" href="' . base_url("call_logs/rejected") . '">Rejected</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['blocked'] . '" href="' . base_url("call_logs/blocked") . '">Blocked</a>
            &nbsp;&nbsp;';
    }

    /**
     * Get navigation URLs for App views
     */
    protected function getAppNavigationUrls(string $activeView = 'all'): string
    {
        $buttons = [
            'all'      => ($activeView === 'all') ? 'btn-primary' : 'btn-outline-primary',
            'system'   => ($activeView === 'system') ? 'btn-primary' : 'btn-outline-primary',
            'user'     => ($activeView === 'user') ? 'btn-primary' : 'btn-outline-primary',
            'recent'   => ($activeView === 'recent') ? 'btn-primary' : 'btn-outline-primary',
            'disabled' => ($activeView === 'disabled') ? 'btn-primary' : 'btn-outline-primary'
        ];

        return '
        <a class="btn ' . $buttons['all'] . '" href="' . base_url("apps") . '">All</a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['system'] . '" href="' . base_url("apps/system") . '">System</a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['user'] . '" href="' . base_url("apps/user") . '">User</a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['recent'] . '" href="' . base_url("apps/recent") . '">Recent</a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['disabled'] . '" href="' . base_url("apps/disabled") . '">Disabled</a>
        &nbsp;&nbsp;';
    }

    /**
     * Get page titles for different SMS views
     */
    protected function getSmsPageTitle(string $viewType): string
    {
        $titles = [
            'all' => 'All SMS',
            'inbox' => 'Received SMS',
            'sent' => 'Sent SMS'
        ];

        return $titles[$viewType] ?? 'SMS';
    }

    /**
     * Get page titles for different Call views
     */
    protected function getCallPageTitle(string $viewType): string
    {
        $titles = [
            'all' => 'All Call Logs',
            'incoming' => 'Incoming Calls',
            'outgoing' => 'Outgoing Calls',
            'rejected' => 'Missed and Rejected Calls',
            'blocked' => 'Blocked Calls'
        ];

        return $titles[$viewType] ?? 'Call Logs';
    }

    /**
     * Get page titles for different App views
     */
    protected function getAppPageTitle(string $viewType): string
    {
        $titles = [
            'all'      => 'All Apps',
            'system'   => 'System Apps',
            'user'     => 'User Apps',
            'recent'   => 'Recently Installed',
            'disabled' => 'Disabled Apps'
        ];

        return $titles[$viewType] ?? 'Apps';
    }

    /**
     * Get pagination data
     */
    protected function getPaginationData(): array
    {
        // Get current page from query string
        $currentPage = $this->request->getGet('page') ?? 1;

        // Make sure pager is initialized
        $pager = $this->finderModel->pager;
        if (!$pager) {
            $pager = $this->finderModel->getPager();
        }

        return [
            'pager' => $pager,
            'currentPage' => $currentPage,
            'perPage' => $this->perPage,
        ];
    }

    /**
     * Get common data for SMS views
     */
    protected function getSmsCommonData(string $viewType = 'all'): array
    {
        $paginationData = $this->getPaginationData();

        return array_merge([
            'pag' => 'sms',
            'sms_head' => $this->getSmsPageTitle($viewType),
            'sms_urls' => $this->getSmsNavigationUrls($viewType),
            'totalSMS' => $this->finderModel->get_count_Sms($this->userId),
            'totalSmsInbox' => $this->finderModel->get_count_Sms_category($this->userId, 'inbox'),
            'totalSmsSent' => $this->finderModel->get_count_Sms_category($this->userId, 'sent'),
        ], $paginationData);
    }

    /**
     * Get common data for Call views
     */
    protected function getCallCommonData(string $viewType = 'all'): array
    {
        $paginationData = $this->getPaginationData();

        return array_merge([
            'pag' => 'call_logs',
            'call_head' => $this->getCallPageTitle($viewType),
            'call_urls' => $this->getCallNavigationUrls($viewType),
            'totalCalls' => $this->finderModel->get_count_Calls($this->userId),
        ], $paginationData);
    }

    /**
     * Get common data for App views
     */
    protected function getAppCommonData(string $viewType = 'all'): array
    {
        $paginationData = $this->getPaginationData();

        return array_merge([
            'pag' => 'apps',
            'apps_head' => $this->getAppPageTitle($viewType),
            'apps_urls' => $this->getAppNavigationUrls($viewType),
            'totalApps' => $this->finderModel->get_count_Apps($this->userId),
        ], $paginationData);
    }

    /**
     * Render user view with common data
     */
    protected function renderUserView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Render SMS-specific view
     */
    protected function renderSmsView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Render Call-specific view
     */
    protected function renderCallView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Render App-specific view
     */
    protected function renderAppView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Set items per page for pagination
     */
    protected function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }
}