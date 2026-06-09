<?php
session_start();
header('Content-Type: application/json; charset=utf-8');

// 顯示錯誤方便除錯（上線後請移除）
ini_set('display_errors', 1);
error_reporting(E_ALL);

// 未登入或非學生
if (!isset($_SESSION['UserID']) || $_SESSION['Role'] !== 'student') {
    http_response_code(401);
    echo json_encode(['error' => '未登入或非學生']);
    exit();
}

require_once 'db_connect.php';

$studentID = $_SESSION['UserID'];

// 1. 查學生參加的其中一門課（從 class_member）
$stmt = $mysqli->prepare("
    SELECT cm.ClassID
FROM class_member cm
JOIN material m ON cm.ClassID = m.classID
WHERE cm.MemberID = ?
GROUP BY cm.ClassID
ORDER BY RAND()
LIMIT 1
");
if (!$stmt) {
    echo json_encode(['error' => 'Prepare failed: ' . $mysqli->error]);
    exit();
}
$stmt->bind_param("i", $studentID);
$stmt->execute();
$result = $stmt->get_result();
$classRow = $result->fetch_assoc();
$stmt->close();

if (!$classRow) {
    echo json_encode(['classID' => null, 'materials' => []]);
    exit();
}

$classID = $classRow['ClassID'];

// 2. 查該課程對應的教材資料（最多 10 筆）
$stmt2 = $mysqli->prepare("
    SELECT materialID, name
    FROM material
    WHERE classID = ?
    ORDER BY materialID DESC
    LIMIT 10
");
$stmt2->bind_param("i", $classID);
$stmt2->execute();
$result2 = $stmt2->get_result();
$materials = $result2->fetch_all(MYSQLI_ASSOC);
$stmt2->close();

echo json_encode([
    'classID' => $classID,
    'materials' => $materials
], JSON_UNESCAPED_UNICODE);
