<?php

namespace App\Controllers\superadmin;

use App\Controllers\BaseController;
use CodeIgniter\HTTP\ResponseInterface;

class LLMController extends BaseController
{
    public function dashboard()
    {
        $data['title'] = 'LLM Management Dashboard';
        $data['user_data'] = session()->get('user_data');
        
        // Fetch models from ML Backend
        $models = [];
        try {
            $client = \Config\Services::curlrequest();
            $mlUrl = env('ML_API_URL', 'http://ml-backend:8000') . '/api/v1/models/llm';
            $internalToken = env('ML_INTERNAL_TOKEN', 'internal-secret-token');
            
            $response = $client->get($mlUrl, [
                'headers' => [
                    'Authorization' => 'Bearer ' . $internalToken,
                    'Accept'        => 'application/json',
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                $models = json_decode($response->getBody(), true);
            }
        } catch (\Exception $e) {
            $data['ml_error'] = "Could not connect to ML Backend: " . $e->getMessage();
        }
        
        $data['models'] = $models;
        
        return view('superadmin/llm_dashboard', $data);
    }
    
    public function setActive()
    {
        $filename = $this->request->getPost('filename');
        if (!$filename) {
            return redirect()->back()->with('error', 'Filename is required.');
        }
        
        try {
            $client = \Config\Services::curlrequest();
            $mlUrl = env('ML_API_URL', 'http://ml-backend:8000') . '/api/v1/models/llm/active';
            $internalToken = env('ML_INTERNAL_TOKEN', 'internal-secret-token');
            
            $response = $client->post($mlUrl, [
                'json' => ['filename' => $filename],
                'headers' => [
                    'Authorization' => 'Bearer ' . $internalToken,
                    'Accept'        => 'application/json',
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                return redirect()->back()->with('success', 'Active model updated successfully.');
            } else {
                return redirect()->back()->with('error', 'Failed to update active model.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error connecting to ML backend: ' . $e->getMessage());
        }
    }
    
    public function download()
    {
        $url = $this->request->getPost('url');
        $targetFilename = $this->request->getPost('target_filename');
        
        if (!$url || !$targetFilename) {
            return redirect()->back()->with('error', 'URL and target filename are required.');
        }
        
        try {
            $client = \Config\Services::curlrequest();
            $mlUrl = env('ML_API_URL', 'http://ml-backend:8000') . '/api/v1/models/llm/download';
            $internalToken = env('ML_INTERNAL_TOKEN', 'internal-secret-token');
            
            $response = $client->post($mlUrl, [
                'json' => [
                    'url' => $url,
                    'target_filename' => $targetFilename
                ],
                'headers' => [
                    'Authorization' => 'Bearer ' . $internalToken,
                    'Accept'        => 'application/json',
                ]
            ]);
            
            if ($response->getStatusCode() === 200) {
                return redirect()->back()->with('success', 'Download task started in the background. It may take several minutes to complete.');
            } else {
                return redirect()->back()->with('error', 'Failed to start download task.');
            }
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Error connecting to ML backend: ' . $e->getMessage());
        }
    }
}
