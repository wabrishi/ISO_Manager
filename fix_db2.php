<?php
require_once 'config/database.php';
$database = new Database();
$db = $database->getConnection();
$db->exec("ALTER TABLE certificates MODIFY COLUMN iso_standard VARCHAR(255) NOT NULL");
echo "DB updated.\n";
