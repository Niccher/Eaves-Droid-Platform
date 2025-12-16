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
        return $this->view('all');
    }

    /**
     * Display only received SMS messages
     * Route: /sms/inbox
     */
    public function inbox()
    {
        return $this->view('inbox');
    }

    /**
     * Display only sent SMS messages
     * Route: /sms/sent
     */
    public function sent()
    {
        return $this->view('sent');
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
}