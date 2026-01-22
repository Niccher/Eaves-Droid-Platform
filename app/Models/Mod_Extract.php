<?php

namespace App\Models;

use CodeIgniter\Model;

class Mod_Extract extends Model
{
    /**
     * Gets SMS between contacts.
     *
     * @param int $user_id
     * @param string $contactNumber1
     * @param string $contactNumber2
     * @return array
     */
    public function get_sms_between_contacts(int $user_id, string $contactNumber1, string $contactNumber2): array
    {
        try {
            return $this->db->table('tbl_sms')
                ->select('*, address as sms_number, body as sms_body, sms_date as sms_time')
                ->where('owner_id', $user_id)
                ->groupStart()
                    ->where('address', $contactNumber1)
                    ->orWhere('address', $contactNumber2)
                ->groupEnd()
                ->orderBy('sms_date', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_sms_between_contacts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets logs between contacts.
     *
     * @param int $user_id
     * @param string $contactNumber1
     * @param string $contactNumber2
     * @return array
     */
    public function get_logs_between_contacts(int $user_id, string $contactNumber1, string $contactNumber2): array
    {
        try {
            return $this->db->table('tbl_logs')
                ->select('*, call_type as Type, phone_number as Caller, call_date as Timestamp, duration_seconds as Durations')
                ->where('owner_id', $user_id)
                ->groupStart()
                    ->where('phone_number', $contactNumber1)
                    ->orWhere('phone_number', $contactNumber2)
                ->groupEnd()
                ->orderBy('call_date', 'DESC')
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_logs_between_contacts error: ' . $e->getMessage());
            return [];
        }
    }

    /**
     * Gets contact by ID.
     *
     * @param int $contact_id
     * @return array|false
     */
    public function get_contact_at(int $contact_id)
    {
        try {
            $result = $this->db->table('tbl_contacts')
                ->select('*, display_name as Name')
                ->where('counter', $contact_id)
                ->get()
                ->getRowArray();
                
            if ($result) {
                // Handle new schema phone numbers (stored as JSON)
                if (isset($result['phone_numbers'])) {
                    $phoneNumbers = json_decode($result['phone_numbers'], true);
                    $result['Number'] = !empty($phoneNumbers) && is_array($phoneNumbers) ? $phoneNumbers[0] : '';
                } else {
                    $result['Number'] = '';
                }
                return $result;
            }
            log_message('error', 'No contact found for ID ' . $contact_id);
            return false;
        } catch (\Exception $e) {
            log_message('error', 'get_contact_at error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Gets SMS from a sender.
     *
     * @param int $user_id
     * @param string $sender
     * @return array
     */
    public function get_sms_from(int $user_id, string $sender): array
    {
        try {
            return $this->db->table('tbl_sms')
                ->select('*, address as sms_number, body as sms_body, sms_date as sms_time')
                ->where('owner_id', $user_id)
                ->where('address', $sender)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_sms_from error: ' . $e->getMessage());
            return [];
        }
    }
}