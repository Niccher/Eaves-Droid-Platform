<?php

namespace App\Controllers\api\v1;

use App\Controllers\BaseController;
use CodeIgniter\API\ResponseTrait;

/**
 * FCMStatusController
 *
 * Lightweight polling endpoint for checking the status of a dispatched
 * FCM command. Used by the "File Details" modal live-status panel.
 *
 * GET /api/v1/fcm-status/{logId}
 *
 * Returns JSON:
 * {
 *   "status":   "pending"|"ack_success"|"ack_failed"|"dispatched"|"timeout",
 *   "message":  "Human-readable status",
 *   "acked_at": "2026-08-17 18:00:00" | null,
 *   "elapsed":  42            // seconds since dispatch
 * }
 */
class FCMStatusController extends BaseController
{
    use ResponseTrait;

    /** Max seconds before we declare a command timed-out (no device ACK) */
    private const TIMEOUT_SECONDS = 120;

    public function status(int $logId = 0)
    {
        if (!$logId) {
            return $this->fail('Log ID required.', 400);
        }

        $db  = \Config\Database::connect();
        $row = $db->table('tbl_user_actions')
            ->select('id, action_type, success, new_values, error_message, created_at')
            ->where('id', $logId)
            ->get()
            ->getRowArray();

        if (!$row) {
            return $this->fail('Action log not found.', 404);
        }

        $nv      = !empty($row['new_values']) ? json_decode($row['new_values'], true) : [];
        $hasAck  = !empty($nv['device_ack']);
        $ackData = $nv['device_ack'] ?? null;

        $createdAt = strtotime($row['created_at']);
        $elapsed   = time() - $createdAt;

        // --- Determine status ---
        if ($hasAck) {
            $ackStatus = $ackData['status'] ?? 'unknown';
            $status    = ($ackStatus === 'success') ? 'ack_success' : 'ack_failed';
            
            if ($ackStatus === 'success') {
                $rawMsg = $ackData['message'] ?? 'Command completed.';
                if (stripos($rawMsg, 'File fetch started:') !== false) {
                    $message = 'Device Confirmed: File download started.';
                } else {
                    $message = 'Device confirmed: ' . $rawMsg;
                }
            } else {
                $message = 'Device reported failure: ' . ($ackData['message'] ?? 'Unknown error');
            }
            $ackedAt   = $ackData['acknowledged_at'] ?? null;
        } elseif ($elapsed > self::TIMEOUT_SECONDS) {
            $status  = 'timeout';
            $message = 'No response from device after ' . self::TIMEOUT_SECONDS . ' seconds. The device may be offline.';
            $ackedAt = null;
        } else {
            $status  = 'pending';
            $message = 'Waiting for device to acknowledge… (' . $elapsed . 's elapsed)';
            $ackedAt = null;
        }

        return $this->respond([
            'status'    => $status,
            'message'   => $message,
            'acked_at'  => $ackedAt,
            'elapsed'   => $elapsed,
            'log_id'    => $logId,
            'action'    => $row['action_type'],
        ]);
    }
}
