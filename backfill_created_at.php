<?php

define('FCPATH', __DIR__ . DIRECTORY_SEPARATOR);
require __DIR__ . '/vendor/autoload.php';

$paths = new Config\Paths();
$app = new CodeIgniter\Boot($paths);
$app->initialize();

$db = \Config\Database::connect();
$result = $db->query("UPDATE tbl_device_profile SET created_at = extraction_timestamp WHERE created_at IS NULL;");
echo "Updated " . $db->affectedRows() . " rows\n";