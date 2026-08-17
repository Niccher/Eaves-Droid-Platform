<?php

namespace App\Filters;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use App\Models\Mod_Android;

class ApiAuthFilter implements FilterInterface
{
    public function before(RequestInterface $request, $params = null)
    {
        $path = $request->getUri()->getPath();

        // Public endpoints — no auth required
        if (str_contains($path, 'api/v1/health') ||
            str_contains($path, 'api/v1/tokens/verify') ||
            str_contains($path, 'api/v1/devices/fingerprints')) {
            return;
        }

        // ─────────────────────────────────────────────────────────────────────
        // STEP 1: Resolve Bearer token
        // ─────────────────────────────────────────────────────────────────────
        $token      = null;
        $authHeader = $request->getHeaderLine('Authorization');

        if (!empty($authHeader) && preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            $token = trim($matches[1]);
        }

        // Fallback: JSON or POST body (legacy support during migration)
        if (!$token) {
            $json  = $request->getJSON(true) ?? [];
            $token = $json['token'] ?? $request->getPost('token');
        }

        if (empty($token)) {
            return response()
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 401,
                    'error'   => 'Unauthorized: Missing API authentication token',
                    'success' => false,
                ]);
        }

        // ─────────────────────────────────────────────────────────────────────
        // STEP 2: Validate token against DB
        // ─────────────────────────────────────────────────────────────────────
        $androidModel = new Mod_Android();
        $tokenData    = $androidModel->token_test($token);

        if (!$tokenData) {
            return response()
                ->setStatusCode(401)
                ->setJSON([
                    'status'  => 401,
                    'error'   => 'Unauthorized: Invalid or expired API authentication token',
                    'success' => false,
                ]);
        }

        $ownerId = (int) $tokenData['owner_id'];

        // ─────────────────────────────────────────────────────────────────────
        // STEP 3: Cross-check X-Device-UUID + X-Device-Checksum
        //
        // Only rejects if:
        //   - BOTH headers are present AND non-empty
        //   - The device IS registered in tbl_device_profiles (uuid found)
        //   - The checksum does NOT match the stored device_id column
        //
        // Passes through silently when:
        //   - Headers are missing (old client, pre-migration)
        //   - Device is not yet registered (fingerprint hasn't been submitted)
        // ─────────────────────────────────────────────────────────────────────
        $deviceUuid     = $request->getHeaderLine('X-Device-UUID');
        $deviceChecksum = $request->getHeaderLine('X-Device-Checksum');

        if (!empty($deviceUuid) && !empty($deviceChecksum)) {
            $verified = $androidModel->verify_device_checksum($ownerId, $deviceUuid, $deviceChecksum);

            if ($verified === false) {
                // Device is registered but checksum is wrong — spoofed UUID or cloned token
                return response()
                    ->setStatusCode(403)
                    ->setJSON([
                        'status'  => 403,
                        'error'   => 'Forbidden: Device identity mismatch',
                        'success' => false,
                    ]);
            }
            // $verified === null → device not yet registered → allow through
            // $verified === true → device verified → allow through
        }

        // ─────────────────────────────────────────────────────────────────────
        // STEP 4: Attach resolved identity to request for use in controllers
        // ─────────────────────────────────────────────────────────────────────
        $request->token_owner_id  = $ownerId;
        $request->device_uuid     = $deviceUuid;
        $request->device_checksum = $deviceChecksum;
    }

    public function after(RequestInterface $request, ResponseInterface $response, $params = null)
    {
    }
}
