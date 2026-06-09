<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");
$account = $_SESSION['Account'];
$name = $_POST['name'] ?? '';
if (!$name) {
    http_response_code(400);
    echo "教材名稱為空";
    exit;
}

$stmt = $mysqli->prepare("DELETE FROM material WHERE Name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();

echo "success";
