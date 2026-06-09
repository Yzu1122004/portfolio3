<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

// 驗證使用者
$account = $_SESSION['Account'] ?? '';
if (!$account) {
    http_response_code(403);
    exit("請先登入");
}

// 找出教師ID
$stmt = $mysqli->prepare("SELECT UserID FROM user WHERE Account = ?");
$stmt->bind_param("s", $account);
$stmt->execute();
$res = $stmt->get_result();
$row = $res->fetch_assoc();
$teacherID = $row['UserID'] ?? null;

if (!$teacherID) {
    http_response_code(403);
    exit("找不到使用者");
}

// 驗證檔案與名稱
if (!isset($_FILES['videoFile']) || !isset($_POST['name'])) {
    http_response_code(400);
    exit("缺少檔案或名稱");
}

$name = $_POST['name'];
$video = $_FILES['videoFile'];
$materialID = uniqid("v_");

// 指定儲存目錄
$targetDir = "./videos/";
if (!is_dir($targetDir)) {
    mkdir($targetDir, 0777, true);
}

$ext = pathinfo($video['name'], PATHINFO_EXTENSION);
$targetPath = $targetDir . $materialID . "." . $ext;

// 搬移影片檔案
if (!move_uploaded_file($video['tmp_name'], $targetPath)) {
    http_response_code(500);
    exit("無法儲存影片檔案");
}

// 儲存路徑到資料庫（只存相對路徑）
$relativePath = "../網頁資料庫(整合)/api2/makeQ/videos/" . $materialID . "." . $ext;
$stmt = $mysqli->prepare("INSERT INTO video (MaterialID, name, content, teacherID) VALUES (?, ?, ?, ?)");
$stmt->bind_param("sssi", $materialID, $name, $relativePath, $teacherID);
if ($stmt->execute()) {
    echo json_encode(["success" => true, "content" => $relativePath]);
} else {
    http_response_code(500);
    echo "資料庫寫入失敗：" . $stmt->error;
}
?>
