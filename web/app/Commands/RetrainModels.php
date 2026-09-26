<?php

namespace App\Commands;

use CodeIgniter\CLI\BaseCommand;
use CodeIgniter\CLI\CLI;

class RetrainModels extends BaseCommand
{
    /**
     * The Command's Group
     *
     * @var string
     */
    protected $group = 'ML';

    /**
     * The Command's Name
     *
     * @var string
     */
    protected $name = 'ml:retrain';

    /**
     * The Command's Description
     *
     * @var string
     */
    protected $description = 'Triggers a nightly retraining job on the ML backend for Fusion and Markov models.';

    /**
     * The Command's Usage
     *
     * @var string
     */
    protected $usage = 'ml:retrain';

    /**
     * Actually execute a command.
     *
     * @param array $params
     */
    public function run(array $params)
    {
        CLI::write('Triggering ML model retraining...', 'green');
        
        try {
            $client = \Config\Services::curlrequest();
            // Assuming ML API is at env variable or hardcoded internal address
            $mlUrl = env('ML_API_URL', 'http://ml-backend:8000') . '/api/v1/ml/retrain';
            $internalToken = env('ML_INTERNAL_TOKEN', 'internal-secret-token');
            
            $response = $client->post($mlUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $internalToken,
                    'Accept'        => 'application/json',
                ],
                'timeout' => 120, // Retraining might take a while
            ]);
            
            if ($response->getStatusCode() === 200 || $response->getStatusCode() === 202) {
                CLI::write('Retraining job triggered successfully: ' . $response->getBody(), 'green');
            } else {
                CLI::write('Failed to trigger retraining: HTTP ' . $response->getStatusCode(), 'red');
                log_message('error', 'ML Retrain failed: HTTP ' . $response->getStatusCode() . ' - ' . $response->getBody());
            }
        } catch (\Exception $e) {
            CLI::write('Error connecting to ML backend: ' . $e->getMessage(), 'red');
            log_message('error', 'ML Retrain exception: ' . $e->getMessage());
        }
    }
}
