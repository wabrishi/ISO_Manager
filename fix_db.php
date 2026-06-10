<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$db->exec("ALTER TABLE certificates ADD COLUMN `main_type` ENUM('IAF', 'Non-IAF') DEFAULT 'Non-IAF' AFTER `iso_standard`");
echo "DB updated.\n";
