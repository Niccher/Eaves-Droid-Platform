<?php

namespace App\Controllers\clients;

use App\Models\Mod_Finder;
use CodeIgniter\API\ResponseTrait;

class Sms extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display all SMS messages with pagination
     * Route: /sms
     */
    public function index()
    {
        $totalSMS = $this->finderModel->get_count_Sms($this->userId);
        $data = $this->getSmsCommonData('all');
        $data = array_merge($data, [
            'sms_dump' => $this->finderModel->get_sms($this->userId, $this->perPage),
            'pager' => $this->finderModel->pager,
        ]);
        return $this->renderSmsView('users/sms/sms', $data);
    }

    /**
     * Display only received SMS messages
     * Route: /sms/inbox
     */
    public function inbox()
    {
        $totalSMS = $this->finderModel->get_count_SmsInbox($this->userId);
        $data = $this->getSmsCommonData('inbox');
        $data = array_merge($data, [
            'sms_dump' => $this->finderModel->get_sms_type($this->userId, 'inbox', $this->perPage),
            'pager' => $this->finderModel->pager,
        ]);
        return $this->renderSmsView('users/sms/inbox', $data);
    }

    /**
     * Display only sent SMS messages
     * Route: /sms/sent
     */
    public function sent()
    {
        $totalSMS = $this->finderModel->get_count_SmsSent($this->userId);
        $data = $this->getSmsCommonData('sent');
        $data = array_merge($data, [
            'sms_dump' => $this->finderModel->get_sms_type($this->userId, 'sent', $this->perPage),
            'pager' => $this->finderModel->pager,
        ]);
        return $this->renderSmsView('users/sms/sent', $data);
    }

    /**
     * Alternative: Single method with parameter
     * Route: /sms/(all|inbox|sent)
     */
    public function view($type = 'all')
    {
        // Validate type parameter
        $validTypes = ['all', 'inbox', 'sent'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('sms');
        }

        // Get SMS data based on type
        switch ($type) {
            case 'inbox':
                $smsData = $this->finderModel->get_sms_type($this->userId, 'inbox');
                $viewFile = 'users/sms/inbox';
                break;
            case 'sent':
                $smsData = $this->finderModel->get_sms_type($this->userId, 'sent');
                $viewFile = 'users/sms/sent';
                break;
            default: // 'all'
                $smsData = $this->finderModel->get_sms($this->userId, $this->perPage);
                $viewFile = 'users/sms/sms';
                break;
        }

        // Get common data for SMS views
        $commonData = $this->getSmsCommonData($type);

        // Prepare data for the view
        $data = array_merge($commonData, [
            'sms_dump' => $smsData,
            'pager' => $this->finderModel->pager,
        ]);

        return $this->renderSmsView($viewFile, $data);
    }

    /**
     * Alternative method for backward compatibility
     * Route: /sms/sms (maps to index)
     */
    public function sms()
    {
        return $this->index();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /sms/sms_inbox (maps to inbox)
     */
    public function sms_inbox()
    {
        return $this->inbox();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /sms/sms_sent (maps to sent)
     */
    public function sms_sent()
    {
        return $this->sent();
    }

    /**
     * Get common data for SMS views
     */
    protected function getSmsCommonData(string $type = 'all'): array
    {
        // Get total counts based on type
        $totalSMS = 0;
        $totalSmsInbox = 0;
        $totalSmsSent = 0;

        switch ($type) {
            case 'inbox':
                $totalSMS = $this->finderModel->get_count_Sms_category($this->userId, 'inbox');
                $totalSmsInbox = $totalSMS;
                break;
            case 'sent':
                $totalSMS = $this->finderModel->get_count_Sms_category($this->userId, 'sent');
                $totalSmsSent = $totalSMS;
                break;
            default:
                $totalSMS = $this->finderModel->get_count_Sms($this->userId);
                $totalSmsInbox = $this->finderModel->get_count_Sms_category($this->userId, 'inbox');
                $totalSmsSent = $this->finderModel->get_count_Sms_category($this->userId, 'sent');
                break;
        }

        $paginationData = $this->getPaginationData();

        return array_merge([
            'pag' => 'sms',
            'sms_head' => ucfirst($type) . ' SMS Messages',
            'sms_urls' => $this->getSmsNavigationUrls($type),
            'totalSMS' => $totalSMS,
            'totalSmsInbox' => $totalSmsInbox,
            'totalSmsSent' => $totalSmsSent,
        ], $this->getDeviceViewData(), $paginationData);
    }

    /**
     * Get navigation URLs for SMS views
     */
    protected function getSmsNavigationUrls(string $activeView = 'all'): string
    {
        $buttons = [
            'all'    => ($activeView === 'all') ? 'btn-primary' : 'btn-outline-primary',
            'inbox'  => ($activeView === 'inbox') ? 'btn-primary' : 'btn-outline-primary',
            'sent'   => ($activeView === 'sent') ? 'btn-primary' : 'btn-outline-primary'
        ];

        return '
            <a class="btn ' . $buttons['all'] . '" href="' . base_url("sms") . '">All SMS</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['inbox'] . '" href="' . base_url("sms/inbox") . '">Inbox</a>
            &nbsp;&nbsp;
            <a class="btn ' . $buttons['sent'] . '" href="' . base_url("sms/sent") . '">Sent</a>';
    }

    /**
     * Render SMS-specific view
     */
    protected function renderSmsView(string $mainView, array $extraData = []): string
    {
        // Ensure totalSMS is passed to the view (used by sidebar)
        if (!isset($extraData['totalSMS'])) {
            $extraData['totalSMS'] = $this->finderModel->get_count_Sms($this->userId);
        }
        
        $data = array_merge([
            'user_info' => $this->userData,
            'total_sms' => $extraData['totalSMS'],
        ], $this->getUserDataCounts(), $extraData);

        return view('headers_footers/head_users', $data)
            . view('headers_footers/sidebar_users', $data)
            . view($mainView, $data)
            . view('headers_footers/footer_data_datatables', $data);
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX() && $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_sms((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'SMS deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete SMS.']);
    }
}