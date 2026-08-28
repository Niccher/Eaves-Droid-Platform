<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;

class UserActivityFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        if (function_exists('auth') && auth()->loggedIn()) {
            $now = time();
            $lastUpdate = session()->get('last_activity_update');

            // Throttle database updates to once every 60 seconds
            if (!$lastUpdate || ($now - $lastUpdate) > 60) {
                $db = \Config\Database::connect();
                $db->table('user_profiles')
                    ->where('user_id', auth()->id())
                    ->update([
                        'last_seen_at' => date('Y-m-d H:i:s')
                    ]);
                session()->set('last_activity_update', $now);
            }
        }

        return null;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
    }
}
