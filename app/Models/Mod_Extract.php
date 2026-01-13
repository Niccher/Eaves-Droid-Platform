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
            return $this->db->table('tbl_Sms')
                ->where('owner_id', $user_id)
                ->where('sms_number', $contactNumber1)
                ->orWhere('sms_number', $contactNumber2)
                ->orderBy('sms_time', 'DESC')
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
            return $this->db->table('tbl_Logs')
                ->where('owner_id', $user_id)
                ->where('Caller', $contactNumber1)
                ->orWhere('Caller', $contactNumber2)
                ->orderBy('Timestamp', 'DESC')
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
            $result = $this->db->table('tbl_Contacts')
                ->where('ID', $contact_id)
                ->get()
                ->getRowArray();
            if ($result) {
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
            return $this->db->table('tbl_Sms')
                ->where('owner_id', $user_id)
                ->where('sms_number', $sender)
                ->get()
                ->getResultArray();
        } catch (\Exception $e) {
            log_message('error', 'get_sms_from error: ' . $e->getMessage());
            return [];
        }
    }
}