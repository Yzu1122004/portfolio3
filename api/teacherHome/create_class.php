<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$account = $_SESSION['Account'];
$className = $_POST['classname'] ?? '';

if (empty($className)) {
    http_response_code(400);
    echo "班級名稱為空";
    exit;
}

// 取得教師 ID
$res = $mysqli->prepare("SELECT UserID FROM user WHERE Account = ?");
$res->bind_param("s", $account);
$res->execute();
$teacherID = $res->get_result()->fetch_assoc()['UserID'] ?? null;

if (!$teacherID) {
    http_response_code(400);
    echo "找不到教師帳號";
    exit;
}

// 插入班級資料，ClassID 由資料庫自動產生
$stmt = $mysqli->prepare("INSERT INTO class (ClassName, TeacherID) VALUES (?, ?)");
$stmt->bind_param("si", $className, $teacherID);
$stmt->execute();

echo json_encode(["success" => true]);
