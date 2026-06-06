<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Firebase extends BaseConfig
{
    /**
     * Firebase Service Account Key (JSON file path)
     * If using the new HTTP v1 API (Recommended)
     */
    public string $serviceAccountPath = FCPATH . 'firebase_service_account.json';

    /**
     * Firebase Server Key
     * If using the Legacy HTTP API
     */
    public string $serverKey = 'YOUR_SERVER_KEY_HERE';

    /**
     * Firebase Project ID
     */
    public string $projectId = 'ona-sasa';
}
