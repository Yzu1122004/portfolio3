<?php
session_start();
$mysqli = new mysqli("localhost","root","","test");
$mysqli->set_charset("utf8");

//──── 1. 取出教師帳號 ───────────────────────────
$account = $_SESSION['Account'] ?? null;
if (!$account) {
    http_response_code(403);
    exit("未登入");
}

$stmt = $mysqli->prepare("SELECT UserID FROM user WHERE Account = ?");
$stmt->bind_param("s",$account);
$stmt->execute();
$teacherID = $stmt->get_result()->fetch_assoc()['UserID'] ?? null;
if (!$teacherID){
    http_response_code(403);
    exit("帳號無效");
}

//──── 2. 更新名字 (可選) ─────────────────────────
if (!empty($_POST['name'])){
    $newName = trim($_POST['name']);
    $stmt = $mysqli->prepare("UPDATE user SET Name = ? WHERE UserID = ?");
    $stmt->bind_param("si",$newName,$teacherID);
    $stmt->execute();
}

//──── 3. 更新頭像 (可選) ─────────────────────────
if (isset($_FILES['img']) && $_FILES['img']['error'] === 0){

    /* (1) 允許副檔名 */
    $ext = strtolower(pathinfo($_FILES['img']['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg','jpeg','png','gif','webp'];
    if (!in_array($ext,$allowed)){
        http_response_code(400);
        exit("僅允許上傳: ".implode(", ",$allowed));
    }

    /* (2) 確保目錄存在 —— 直接用實體路徑 */
    $dir = __DIR__ . "/../../api/userImg";
    if (!is_dir($dir))  mkdir($dir,0777,true);

    /* (3) 搬檔案 */
    $saveName   = "teacher_{$teacherID}.".$ext;
    $savePath   = $dir . "/" . $saveName;           // 實體路徑
    if (!move_uploaded_file($_FILES['img']['tmp_name'],$savePath)){
        http_response_code(500);
        exit("無法儲存檔案");
    }

    /* (4) 寫入相對路徑；讓前端可直接放到 <img> 或 background */
    $dbPath = "../網頁資料庫(整合)/api/userImg/".$saveName;
    $stmt = $mysqli->prepare("UPDATE user SET userIMG = ? WHERE UserID = ?");
    $stmt->bind_param("si",$dbPath,$teacherID);
    $stmt->execute();
}

echo json_encode(["success"=>true]);
