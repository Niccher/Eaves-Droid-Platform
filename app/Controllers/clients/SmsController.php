<?php

namespace App\Controllers\clients;

use App\Models\FinderModel;
use CodeIgniter\API\ResponseTrait;

class SmsController extends BaseClientController
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
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderSmsView('users/sms/sms', $data);
    }

    /**
     * Display only received SMS messages
     * Route: /sms/inbox
     */
    public function inbox()
    {
        $totalSMS = $this->finderModel->get_count_Sms_category($this->userId, 'inbox');
        $data = $this->getSmsCommonData('inbox');
        $data = array_merge($data, [
            'sms_dump' => $this->finderModel->get_sms_type($this->userId, 'inbox', $this->perPage),
            'pager' => $this->finderModel->getPager(),
        ]);
        return $this->renderSmsView('users/sms/inbox', $data);
    }

    /**
     * Display only sent SMS messages
     * Route: /sms/sent
     */
    public function sent()
    {
        $totalSMS = $this->finderModel->get_count_Sms_category($this->userId, 'sent');
        $data = $this->getSmsCommonData('sent');
        $data = array_merge($data, [
            'sms_dump' => $this->finderModel->get_sms_type($this->userId, 'sent', $this->perPage),
            'pager' => $this->finderModel->getPager(),
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
            'pager' => $this->finderModel->getPager(),
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
        // Always compute all counters so every page can show Total / Inbox / Sent
        $totalAllSMS = $this->finderModel->get_count_Sms($this->userId);
        $totalSmsInbox = $this->finderModel->get_count_Sms_category($this->userId, 'inbox');
        $totalSmsSent = $this->finderModel->get_count_Sms_category($this->userId, 'sent');

        // totalSMS stays page-specific for the "Showing X of Y" pagination info
        switch ($type) {
            case 'inbox':
                $totalSMS = $totalSmsInbox;
                break;
            case 'sent':
                $totalSMS = $totalSmsSent;
                break;
            default:
                $totalSMS = $totalAllSMS;
                break;
        }

        $paginationData = $this->getPaginationData();

        return array_merge([
            'pag' => 'sms',
            'sms_head' => ucfirst($type) . ' SMS Messages',
            'sms_urls' => $this->getSmsNavigationUrls($type),
            'totalSMS' => $totalSMS,
            'totalAllSMS' => $totalAllSMS,
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
        
        $extraData['contactMap'] = $this->getContactLookupMap();
        $extraData['total_sms'] = $extraData['totalSMS'];
        if (!isset($extraData['pag'])) {
            $extraData['pag'] = 'sms';
        }

        return $this->renderUserView($mainView, $extraData);
    }

    /**
     * Build lookup map for contacts numbers to encrypted IDs
     */
    protected function getContactLookupMap(): array
    {
        $db = \Config\Database::connect();
        $contacts = $db->table('tbl_extracted_contacts')
            ->select('counter, display_name, phone_numbers')
            ->where('owner_id', $this->userId)
            ->get()
            ->getResultArray();

        $map = [];
        $encrypter = model('CryptModel');

        foreach ($contacts as $c) {
            $enc_id = $encrypter->encrypt_id($c['counter']);

            $names = [
                'id' => $c['counter'],
                'enc_id' => $enc_id,
                'name' => $c['display_name']
            ];

            // Decode phone numbers
            $phoneNumbers = json_decode($c['phone_numbers'], true);
            if (!empty($phoneNumbers) && is_array($phoneNumbers)) {
                foreach ($phoneNumbers as $phone) {
                    $num = '';
                    if (is_array($phone) && isset($phone['number'])) {
                        $num = $phone['number'];
                    } elseif (is_string($phone)) {
                        $num = $phone;
                    }
                    if (!empty($num)) {
                        // Normalize number for lookup
                        $clean = preg_replace('/[^0-9+]/', '', $num);
                        $map[$clean] = $names;
                        // Also store the raw value
                        $map[$num] = $names;
                    }
                }
            }
        }
        return $map;
    }

    public function delete($id)
    {
        if (!$this->request->isAJAX() || $this->request->getMethod() !== 'post') {
            return $this->response->setJSON(['success' => false, 'message' => 'Invalid request method.']);
        }
        if ($this->finderModel->delete_sms((int) $id, $this->userId)) {
            return $this->response->setJSON(['success' => true, 'message' => 'SMS deleted successfully.']);
        }
        return $this->response->setJSON(['success' => false, 'message' => 'Failed to delete SMS.']);
    }
}