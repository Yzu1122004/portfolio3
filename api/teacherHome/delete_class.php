<?php
session_start();
$account = $_SESSION['Account'];
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$classID = intval($_POST['classID'] ?? 0);
if ($classID === 0) {
    http_response_code(400);
    echo "未提供 classID";
    exit;
}

// 刪除 class 表資料
$stmt = $mysqli->prepare("DELETE FROM class WHERE ClassID = ?");
$stmt->bind_param("i", $classID);
$stmt->execute();

// 也建議同步刪除 class_member 中該班級的學生關聯（避免殘留）
$mysqli->query("DELETE FROM class_member WHERE ClassID = $classID");

echo "success";
