<?php namespace App\Controllers;

class BackfillCreatedAt extends BaseController {
    public function index() {
        $db = \Config\Database::connect();
        $result = $db->query("UPDATE tbl_device_profile SET created_at = extraction_timestamp WHERE created_at IS NULL;");
        return "Updated " . $db->affectedRows() . " rows";
    }
}