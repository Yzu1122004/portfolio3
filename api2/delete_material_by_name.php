<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");
$account = $_SESSION['Account'];
$name = $_POST['name'] ?? null;
if (!$name) {
    http_response_code(400);
    exit("缺少教材名稱");
}

$stmt = $mysqli->prepare("DELETE FROM material WHERE name = ?");
$stmt->bind_param("s", $name);
$stmt->execute();

echo json_encode(["success" => true]);
