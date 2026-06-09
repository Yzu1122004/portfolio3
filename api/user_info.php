<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

// 假設帳號存在 session 裡
$account = $_SESSION['Account'];

$stmt = $mysqli->prepare("SELECT UserID, userIMG FROM user WHERE Account = ?");
$stmt->bind_param("s", $account);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

$teacherID = $row['UserID'] ?? null;
$userIMG = $row['userIMG'] ?? null;

echo json_encode([
    "success" => (bool)$teacherID,
    "teacherID" => $teacherID,
    "userIMG" => $userIMG
]);
?>
