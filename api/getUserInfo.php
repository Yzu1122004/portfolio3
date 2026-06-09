<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['UserID'])) {
    header("Location: ../index.html");
    exit();
}

// 回傳使用者名稱
echo json_encode([
    'Name'   => $_SESSION['Name']
]);
?>