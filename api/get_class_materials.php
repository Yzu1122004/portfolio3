<?php
session_start();
error_reporting(E_ALL);
ini_set("display_errors", 1);
$account = $_SESSION['Account'];
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$classID = intval($_GET['classID'] ?? 0);

// 取得班級名稱
$classRes = $mysqli->query("SELECT ClassName FROM class WHERE ClassID = $classID");
if (!$classRes || $classRes->num_rows === 0) {
    http_response_code(400);
    echo json_encode(["error" => "無此班級"]);
    exit;
}
$className = $classRes->fetch_assoc()['ClassName'];

// 取得班級總人數
$memberRes = $mysqli->query("SELECT COUNT(*) AS total FROM class_member WHERE ClassID = $classID");
$studentTotal = $memberRes ? intval($memberRes->fetch_assoc()['total']) : 0;

// 取得不重複教材名稱
$materialRes = $mysqli->query("SELECT DISTINCT Name FROM material WHERE ClassID = $classID");



$materials = [];

while ($row = $materialRes->fetch_assoc()) {
    $name = $row['Name'];

    // 查完成人數（根據教材名稱）
    $stmt = $mysqli->prepare("SELECT complete_number FROM student_completed WHERE material_name = ?");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $completed = $result ? intval($result['complete_number']) : 0;

    $materials[] = [
        "name" => $name,
        "completed" => $completed,
        "total" => $studentTotal
    ];
}

echo json_encode([
    "className" => $className,
    "classID" => $classID,
    "materials" => $materials
]);
