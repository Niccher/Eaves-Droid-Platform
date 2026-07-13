<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Log_User_Action extends Model
{
    protected $table = 'tbl_user_actions';
    protected $primaryKey = 'id';

    protected $useAutoIncrement = true;

    protected $returnType = 'array';
    protected $useSoftDeletes = false;

    protected $allowedFields = [
        'user_id',
        'session_id',
        'action_category',
        'action_type',
        'action_severity',
        'ip_address',
        'user_agent',
        'device_type',
        'device_name',
        'operating_system',
        'browser',
        'country_code',
        'city',
        'request_url',
        'request_method',
        'response_code',
        'execution_time_ms',
        'resource_id',
        'old_values',
        'new_values',
        'success',
        'error_code',
        'error_message'
    ];

    protected $useTimestamps = true;
    protected $createdField = 'created_at';
    protected $updatedField = ''; // No updated field for audit logs

    protected $validationRules = [
        'action_category' => 'required|in_list[authentication,file,profile,admin,system,security]',
        'action_type' => 'required|max_length[100]',
        'action_severity' => 'required|in_list[low,medium,high,critical]',
        'ip_address' => 'required|max_length[45]',
        'success' => 'required|in_list[0,1]',
    ];

    protected $validationMessages = [];
    protected $skipValidation = false;

    // Helper method to log actions
    public function logAction($data)
    {
        $request = service('request');
        $agent = $request->getUserAgent();

        // Add IP address if not provided
        if (!isset($data['ip_address'])) {
            $data['ip_address'] = $request->getIPAddress();
        }

        // Add user agent if not provided
        if (!isset($data['user_agent'])) {
            $data['user_agent'] = $agent->getAgentString();
        }

        // Parse user agent to populate device fields if not explicitly provided
        $uaString = $data['user_agent'];
        
        if (!isset($data['device_type'])) {
            if ($agent->isMobile()) {
                $data['device_type'] = 'mobile';
            } elseif ($agent->isRobot()) {
                $data['device_type'] = 'bot';
            } else {
                if (stripos($uaString, 'okhttp') !== false || stripos($uaString, 'android') !== false) {
                    $data['device_type'] = 'mobile';
                } else {
                    $data['device_type'] = 'desktop';
                }
            }
        }

        if (!isset($data['operating_system'])) {
            $data['operating_system'] = $agent->getPlatform();
            if (empty($data['operating_system']) || $data['operating_system'] === 'Unknown Platform') {
                if (stripos($uaString, 'android') !== false) {
                    $data['operating_system'] = 'Android';
                } elseif (stripos($uaString, 'windows') !== false) {
                    $data['operating_system'] = 'Windows';
                } elseif (stripos($uaString, 'macintosh') !== false || stripos($uaString, 'mac os') !== false) {
                    $data['operating_system'] = 'macOS';
                } elseif (stripos($uaString, 'linux') !== false) {
                    $data['operating_system'] = 'Linux';
                } else {
                    $data['operating_system'] = 'Unknown OS';
                }
            }
        }

        if (!isset($data['browser'])) {
            if ($agent->isBrowser()) {
                $data['browser'] = $agent->getBrowser() . ' ' . $agent->getVersion();
            } else {
                if (stripos($uaString, 'okhttp') !== false) {
                    $data['browser'] = 'OkHttp Client';
                } elseif (stripos($uaString, 'postman') !== false) {
                    $data['browser'] = 'Postman';
                } else {
                    $data['browser'] = 'API Client';
                }
            }
        }

        if (!isset($data['device_name'])) {
            if ($data['device_type'] === 'mobile') {
                $matches = [];
                if (preg_match('/\b(android\s+\d+;\s+)?([^;\/]+)\s+build\b/i', $uaString, $matches)) {
                    $data['device_name'] = trim($matches[2]);
                } elseif (preg_match('/\(([^;]+);\s+[^;]+;\s+Android\s+[^;]+;\s+([^)]+)\)/i', $uaString, $matches)) {
                    $data['device_name'] = trim($matches[2]);
                } else {
                    $data['device_name'] = $agent->isMobile() ? ($agent->getMobile() ?: 'Android Mobile') : 'Desktop PC';
                }
            } else {
                $data['device_name'] = 'Desktop PC';
            }
        }

        // Add user ID from session if not provided
        if (!isset($data['user_id']) && session()->has('user_id')) {
            $data['user_id'] = session()->get('user_id');
        }

        // Add session ID
        if (!isset($data['session_id'])) {
            $data['session_id'] = session_id();
        }

        return $this->insert($data);
    }

    // Method to get user actions
    public function getUserActions($userId, $limit = 50)
    {
        return $this->where('user_id', $userId)
            ->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->findAll();
    }

    // Method to search actions
    public function searchActions($filters = [], $limit = 100)
    {
        $builder = $this->builder();

        // Apply filters
        foreach ($filters as $field => $value) {
            if (!empty($value)) {
                $builder->where($field, $value);
            }
        }

        return $builder->orderBy('created_at', 'DESC')
            ->limit($limit)
            ->get()
            ->getResultArray();
    }
}