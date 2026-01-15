<?php

namespace App\Controllers\clients;

use CodeIgniter\API\ResponseTrait;

class Calls extends BaseClientController
{
    use ResponseTrait;

    /**
     * Display all call logs with pagination
     * Route: /call_logs
     */
    public function index()
    {
        return $this->view('all');
    }

    /**
     * Display only incoming calls
     * Route: /call_logs/incoming
     */
    public function incoming()
    {
        return $this->view('incoming');
    }

    /**
     * Display only outgoing calls
     * Route: /call_logs/outgoing
     */
    public function outgoing()
    {
        return $this->view('outgoing');
    }

    /**
     * Display only rejected/missed calls
     * Route: /call_logs/rejected
     */
    public function rejected()
    {
        return $this->view('rejected');
    }

    /**
     * Display only blocked calls
     * Route: /call_logs/blocked
     */
    public function blocked()
    {
        return $this->view('blocked');
    }

    /**
     * Alternative: Single method with parameter
     * Route: /call_logs/(all|incoming|outgoing|rejected|blocked)
     */
    public function view($type = 'all')
    {
        // Validate type parameter
        $validTypes = ['all', 'incoming', 'outgoing', 'rejected', 'blocked'];
        if (!in_array($type, $validTypes)) {
            return redirect()->to('call_logs');
        }

        // Get call data based on type
        switch ($type) {
            case 'incoming':
                $callData = $this->finderModel->get_calls_limited($this->userId, 'Incoming', $this->perPage);
                $viewFile = 'users/call_logs/logs_with_type';
                break;
            case 'outgoing':
                $callData = $this->finderModel->get_calls_limited($this->userId, 'Outgoing', $this->perPage);
                $viewFile = 'users/call_logs/logs_with_type';
                break;
            case 'rejected':
                $callData = $this->finderModel->get_calls_limited($this->userId, 'Rejected', $this->perPage);
                $viewFile = 'users/call_logs/logs_with_type';
                break;
            case 'blocked':
                $callData = $this->finderModel->get_calls_limited($this->userId, 'Blocked', $this->perPage);
                $viewFile = 'users/call_logs/logs_with_type';
                break;
            default: // 'all'
                $callData = $this->finderModel->get_call_logs($this->userId, $this->perPage);
                $viewFile = 'users/call_logs/logs_all';
                break;
        }

        // Get common data for Call views
        $commonData = $this->getCallCommonData($type);

        // Prepare data for the view
        $data = array_merge($commonData, [
            'call_logs_dump' => $callData,
            'pager' => $this->finderModel->pager,
        ]);

        return $this->renderCallView($viewFile, $data);
    }

    /**
     * Alternative method for backward compatibility
     * Route: /call_logs (maps to index)
     */
    public function call_logs()
    {
        return $this->index();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /call_logs/incoming (maps to incoming)
     */
    public function call_incoming()
    {
        return $this->incoming();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /call_logs/outgoing (maps to outgoing)
     */
    public function call_outgoing()
    {
        return $this->outgoing();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /call_logs/rejected (maps to rejected)
     */
    public function call_rejected()
    {
        return $this->rejected();
    }

    /**
     * Alternative method for backward compatibility
     * Route: /call_logs/blocked (maps to blocked)
     */
    public function call_blocked()
    {
        return $this->blocked();
    }
}