<?php
session_start();
header('Content-Type: application/json');
require_once 'db_connect.php';
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//------------ 讀取並解析 JSON ---------------
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

//------------ 取得必要參數 --------------------
$user_id = isset($_SESSION['UserID']) ? intval($_SESSION['UserID']) : 0;
$materialName = isset($data['materialName']) ? $mysqli->real_escape_string($data['materialName']) : '';
$classid = isset($data['classid']) ? intval($data['classid']) : 0;
$correctCount = isset($data['correctCount']) ? intval($data['correctCount']) : 0;
$wrongCount = isset($data['wrongCount']) ? intval($data['wrongCount']) : 0;
$totalQuestions = isset($data['totalQuestions']) ? intval($data['totalQuestions']) : 0;
if (!isset($_SESSION['UserID'])) {
    error_log("⚠️ SESSION['UserID'] 尚未設置！");
} else {
    error_log("✅ SESSION['UserID'] 為：{$_SESSION['UserID']}");
}
//------------ 基本檢查 ------------------------
if ($user_id === 0 || empty($materialName) || $classid === 0) {
    echo json_encode(['error' => 'Invalid data']);
    exit;
}

//------------ student_complet_quetion ---------- 
error_log("即將寫入：user_id=$user_id, materialName=$materialName, classid=$classid, correctCount=$correctCount");

$stmt = $mysqli->prepare(
    "SELECT complet_number FROM student_complet_quetion
     WHERE studentID = ? AND name = ? AND classID = ?"
);
$stmt->bind_param("isi", $user_id, $materialName, $classid);

$stmt->execute();
$res = $stmt->get_result();

$isAllCorrect = ($correctCount === $totalQuestions && $totalQuestions > 0);

if ($res->num_rows === 0) {
    // ➜ 第一次寫入
    $attempt = $isAllCorrect ? 0 : 1;          // 全對則 0，否則 1
    $ins = $mysqli->prepare(
        "INSERT INTO student_complet_quetion
         (studentID, name, classID, complet_number, attemptCount)
         VALUES (?, ?, ?, ?, ?)"
    );
    $ins->bind_param("isiii", $user_id, $materialName, $classid, $correctCount, $attempt);
    $ins->execute();
    $ins->close();
} else {
    // ➜ 已有紀錄，覆寫 complet_number；若沒全對則 attemptCount+1
    if ($isAllCorrect) {
        $upd = $mysqli->prepare(
            "UPDATE student_complet_quetion
         SET complet_number = ?
         WHERE studentID = ? AND name = ? AND classID = ?"
        );
        $upd->bind_param("iisi", $correctCount, $user_id, $materialName, $classid);
    } else {
        $upd = $mysqli->prepare(
            "UPDATE student_complet_quetion
         SET complet_number = ?, 
             attemptCount  = attemptCount + 1
         WHERE studentID = ? AND name = ? AND classID = ?"
        );
        $upd->bind_param("iisi", $correctCount, $user_id, $materialName, $classid);
    }
    $upd->execute();
    $upd->close();

}
$stmt->close();

//------------ student_completed (全對才動) -----
if ($correctCount === $totalQuestions && $totalQuestions > 0) {
    $stmt = $mysqli->prepare(
        "SELECT complete_number FROM student_completed
         WHERE material_name = ? AND classID = ?"
    );
    $stmt->bind_param("si", $materialName, $classid);
    $stmt->execute();
    $res = $stmt->get_result();

    if ($res->num_rows === 0) {
        $ins = $mysqli->prepare(
            "INSERT INTO student_completed
             (material_name, classID, complete_number)
             VALUES (?, ?, 1)"
        );
        $ins->bind_param("si", $materialName, $classid);
        $ins->execute();
        $ins->close();
    } else {
        $upd = $mysqli->prepare(
            "UPDATE student_completed
             SET complete_number = complete_number + 1
             WHERE material_name = ? AND classID = ?"
        );
        $upd->bind_param("si", $materialName, $classid);
        $upd->execute();
        $upd->close();
    }
    $stmt->close();
}

echo json_encode(['success' => true]);
