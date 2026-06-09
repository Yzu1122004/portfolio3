<?php
// db_connect.php
$DB_HOST = 'sql209.infinityfree.com';
$DB_USER = 'if0_42139419';
$DB_PASS = 'OWOOHO111222333';
$DB_NAME = 'if0_42139419_XXX';
$DB_PORT = 3306;

$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME, $DB_PORT);
if ($mysqli->connect_errno) {
    echo "MySQL connection failed: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}

$mysqli->set_charset("utf8mb4");
?>
