<?php

namespace App\Controllers\clients;

use App\Controllers\BaseController;
use App\Models\Mod_Finder;
use Config\Services;

class BaseClientController extends BaseController
{
    protected $finderModel;
    protected $userData;
    protected $userId;
    protected $perPage = 50;
    protected $session;
    protected $activeDeviceId = null;
    protected $userDevices = [];

    /**
     * Initialize controller.
     *
     * @param \CodeIgniter\HTTP\RequestInterface $request
     * @param \CodeIgniter\HTTP\ResponseInterface $response
     * @param \Psr\Log\LoggerInterface $logger
     * @return void
     */
    public function initController(
        \CodeIgniter\HTTP\RequestInterface $request,
        \CodeIgniter\HTTP\ResponseInterface $response,
        \Psr\Log\LoggerInterface $logger
    ): void
    {
        parent::initController($request, $response, $logger);

        // Check authentication
        if (!auth()->loggedIn()) {
            session()->setFlashdata('error', 'Please login to continue');
            throw new \RuntimeException('Authentication required');
        }

        // Initialize session service
        $this->session = Services::session();

        // Initialize models
        $this->finderModel = new Mod_Finder();

        // Get authenticated user data
        $this->userData = $this->finderModel->basic_user();
        if (!$this->userData || !isset($this->userData['id'])) {
            session()->setFlashdata('error', 'User session invalid');
            throw new \RuntimeException('Invalid user session');
        }

        $this->userId = (int) $this->userData['id'];

        // Initialize active device filter
        $this->initActiveDevice();
    }

    /**
     * Initialize or validate the active device filter from session.
     */
    protected function initActiveDevice(): void
    {
        $modUser = new \App\Models\Mod_User();
        $this->userDevices = $modUser->get_user_devices_from_profile($this->userId);

        $tokenData = $modUser->get_token($this->userId);
        $this->userToken = is_array($tokenData) ? $tokenData : [];

        $sessionDeviceId = $this->session->get('active_device_id');
        if (!empty($sessionDeviceId) && $sessionDeviceId !== 'all') {
            // Validate device belongs to this user
            $valid = false;
            foreach ($this->userDevices as $d) {
                if (($d['device_id'] ?? '') === $sessionDeviceId) {
                    $valid = true;
                    break;
                }
            }
            if ($valid) {
                $this->activeDeviceId = $sessionDeviceId;
            }
        }

        // Apply device filter to finder model
        $this->finderModel->setDeviceId($this->activeDeviceId);
    }

    /**
     * GET /account/switch-device/{deviceId}
     * Switches the active device filter.
     */
    public function switchDevice(string $deviceId = 'all'): \CodeIgniter\HTTP\ResponseInterface
    {
        if ($deviceId === 'all') {
            $this->session->remove('active_device_id');
        } else {
            $this->session->set('active_device_id', $deviceId);
        }
        return $this->response->redirect(previous_url() ?: base_url('home'));
    }

    /**
     * Get user data counts.
     *
     * @return array
     */
    protected function getUserDataCounts(): array
    {
        return [
            'total_apps'     => $this->finderModel->get_count_Apps($this->userId),
            'total_contacts' => $this->finderModel->get_count_Contacts($this->userId),
            'total_sms'      => $this->finderModel->get_count_Sms($this->userId),
            'total_files'    => $this->finderModel->get_count_Files($this->userId),
            'total_calls'      => $this->finderModel->get_count_Calls($this->userId),
            'total_locations'  => $this->finderModel->get_count_Location($this->userId),
            'total_activities' => $this->finderModel->get_count_Activity($this->userId),
            'total_device'     => $this->finderModel->get_count_DeviceContext($this->userId),
            'total_network'    => $this->finderModel->get_count_NetworkInfo($this->userId),
            'total_accounts'   => $this->finderModel->get_count_Accounts($this->userId),
            'total_calendar'   => $this->finderModel->get_count_Calendar($this->userId),
            'total_app_usage'  => $this->finderModel->get_count_AppUsage($this->userId),
            'total_notifications' => $this->finderModel->get_count_Notifications($this->userId),
            'total_bluetooth'  => $this->finderModel->get_count_Bluetooth($this->userId),
            'total_sensors'    => $this->finderModel->get_count_Sensors($this->userId),
            'total_media'           => $this->finderModel->get_count_CapturedMedia($this->userId),
            'total_security_audit'  => $this->finderModel->get_count_SecurityAudit($this->userId),
            'total_sim_configs'     => $this->finderModel->get_count_SimConfig($this->userId),
            'active_sms'       => $this->finderModel->get_sms_active($this->userId),
            'active_calls'     => $this->finderModel->get_calls_active($this->userId),
        ];

    }

    /**
     * Get navigation URLs for SMS views.
     *
     * @param string $activeView
     * @return string
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
     * Get navigation URLs for Call views.
     *
     * @param string $activeView
     * @return string
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
     * Get navigation URLs for App views.
     *
     * @param string $activeView
     * @return string
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
        &nbsp;&nbsp;';
    }

    /**
     * Get navigation URLs for Location/Activity views.
     *
     * @param string $activeView
     * @return string
     */
    protected function getLocationNavigationUrls(string $activeView = 'location'): string
    {
        $tabs = [
            'location' => ['url' => 'location', 'label' => 'Locations', 'icon' => 'fas fa-map-marker-alt'],
            'activity' => ['url' => 'activities', 'label' => 'Activities', 'icon' => 'fas fa-walking'],
            'sms'      => ['url' => 'sms', 'label' => 'Messages', 'icon' => 'fas fa-sms'],
            'advanced' => ['url' => 'advanced/device', 'label' => 'Advanced Data', 'icon' => 'fas fa-microchip'],
        ];

        $html = '<div class="d-flex justify-content-end flex-wrap" style="gap: 5px;">';
        foreach ($tabs as $key => $tab) {
            $btnClass = ($key === $activeView) ? 'btn-primary' : 'btn-outline-primary';
            $html .= sprintf(
                '<a class="btn btn-sm %s" href="%s"><i class="%s mr-1"></i> %s</a>',
                $btnClass,
                base_url($tab['url']),
                $tab['icon'],
                $tab['label']
            );
        }
        $html .= '</div>';
        return $html;
    }


    /**
     * Get page titles for different SMS views.
     *
     * @param string $viewType
     * @return string
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
     * Get page titles for different Call views.
     *
     * @param string $viewType
     * @return string
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
     * Get page titles for different App views.
     *
     * @param string $viewType
     * @return string
     */
    protected function getAppPageTitle(string $viewType): string
    {
        $titles = [
            'all'      => 'All Apps',
            'system'   => 'System Apps',
            'user'     => 'User Apps'
        ];

        return $titles[$viewType] ?? 'Apps';
    }

    /**
     * Get pagination data.
     *
     * @return array
     */
    protected function getPaginationData(): array
    {
        // Get current page from query string
        $currentPage = $this->request->getGet('page') ?? 1;

        return [
            'currentPage' => $currentPage,
            'perPage' => $this->perPage,
        ];
    }

    /**
     * Get common data for SMS views.
     *
     * @param string $viewType
     * @return array
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
     * Get common data for Call views.
     *
     * @param string $viewType
     * @return array
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
     * Get common data for App views.
     *
     * @param string $viewType
     * @return array
     */
    protected function getAppCommonData(string $viewType = 'all'): array
    {
        $paginationData = $this->getPaginationData();

        // Get counts for each category
        $totalApps = $this->finderModel->get_count_Apps($this->userId);

        return array_merge([
            'pag' => 'apps',
            'apps_head' => $this->getAppPageTitle($viewType),
            'apps_urls' => $this->getAppNavigationUrls($viewType),
            'totalApps' => $totalApps,
        ], $paginationData);
    }

    /**
     * Render user view with common data.
     *
     * @param string $mainView
     * @param array $extraData
     * @return string
     */
    protected function getDeviceViewData(): array
    {
        return [
            'active_device_id' => $this->activeDeviceId,
            'sidebar_user_devices' => $this->userDevices,
            'user_token' => $this->userToken ?? [],
        ];
    }

    protected function renderUserView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getDeviceViewData(), $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Render SMS-specific view.
     *
     * @param string $mainView
     * @param array $extraData
     * @return string
     */
    protected function renderSmsView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getDeviceViewData(), $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Render Call-specific view.
     *
     * @param string $mainView
     * @param array $extraData
     * @return string
     */
    protected function renderCallView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getDeviceViewData(), $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Render App-specific view.
     *
     * @param string $mainView
     * @param array $extraData
     * @return string
     */
    protected function renderAppView(string $mainView, array $extraData = []): string
    {
        $data = array_merge([
            'user_info' => $this->userData,
        ], $this->getDeviceViewData(), $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    /**
     * Get navigation URLs for File views.
     *
     * @param string $activeView
     * @return string
     */
    protected function getFileNavigationUrls(string $activeView = 'all'): string
    {
        $buttons = [
            'all'       => ($activeView === 'all') ? 'btn-primary' : 'btn-outline-primary',
            'media'     => ($activeView === 'media') ? 'btn-primary' : 'btn-outline-primary',
            'documents' => ($activeView === 'documents') ? 'btn-primary' : 'btn-outline-primary',
            'audio'     => ($activeView === 'audio') ? 'btn-primary' : 'btn-outline-primary',
            'archives'  => ($activeView === 'archives') ? 'btn-primary' : 'btn-outline-primary',
            'others'    => ($activeView === 'others') ? 'btn-primary' : 'btn-outline-primary',
        ];

        return '
        <a class="btn ' . $buttons['all'] . '" href="' . base_url("files") . '">
            <i class="fas fa-folder"></i> All
        </a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['media'] . '" href="' . base_url("files/media") . '">
            <i class="fas fa-photo-video"></i> Media
        </a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['documents'] . '" href="' . base_url("files/documents") . '">
            <i class="fas fa-file-alt"></i> Documents
        </a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['audio'] . '" href="' . base_url("files/audio") . '">
            <i class="fas fa-music"></i> Audio
        </a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['archives'] . '" href="' . base_url("files/archives") . '">
            <i class="fas fa-file-archive"></i> Archives
        </a>
        &nbsp;&nbsp;
        <a class="btn ' . $buttons['others'] . '" href="' . base_url("files/others") . '">
            <i class="fas fa-ellipsis-h"></i> Others
        </a>
        &nbsp;&nbsp;';
    }

    /**
     * Get page titles for different File views.
     *
     * @param string $viewType
     * @return string
     */
    protected function getFilePageTitle(string $viewType): string
    {
        $titles = [
            'all'       => 'All Files',
            'images'    => 'Images',
            'videos'    => 'Videos',
            'media'     => 'Media Files',
            'documents' => 'Documents',
            'audio'     => 'Audio Files',
            'archives'  => 'Archives',
            'others'    => 'Other Files'
        ];

        return $titles[$viewType] ?? 'Files';
    }

    /**
     * Get common data for File views.
     *
     * @param string $viewType
     * @return array
     */
    protected function getFileCommonData(string $viewType = 'all'): array
    {
        $paginationData = $this->getPaginationData();

        return array_merge([
            'pag' => 'files',
            'files_head' => $this->getFilePageTitle($viewType),
            'files_urls' => $this->getFileNavigationUrls($viewType),
            // We can add counts here later if needed
        ], $paginationData);
    }

    /**
     * Set items per page for pagination.
     *
     * @param int $perPage
     * @return void
     */
    protected function setPerPage(int $perPage): void
    {
        $this->perPage = $perPage;
    }
}