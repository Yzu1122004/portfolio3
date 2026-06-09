<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

// 驗證登入
$account = $_SESSION['Account'];
$teacherRes = $mysqli->prepare("SELECT UserID FROM user WHERE Account = ?");
$teacherRes->bind_param("s", $account);
$teacherRes->execute();
$result = $teacherRes->get_result();
$teacherRow = $result->fetch_assoc();
$teacherID = $teacherRow['UserID'] ?? null;

if (!$teacherID) {
    http_response_code(403);
    exit("請先登入");
}


// 解析接收到的 JSON 資料
$data = json_decode(file_get_contents("php://input"), true);
if (!$data) {
    file_put_contents("debug_log.txt", "無法解析 JSON 原始內容：" . file_get_contents("php://input"));
    http_response_code(400);
    exit("無效的 JSON 資料");
}

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
$materialName = $data['materialName'];
$questions = $data['questions'];


foreach ($questions as $index => $q) {
    $materialID = $q['materialID'] ?? uniqid("m_" . time() . "_");

    $type = $q['type'];
    $answer = $q['answer'] ?? null;
    $a = $q['a'] ?? null;
    $b = $q['b'] ?? null;
    $c = $q['c'] ?? null;
    $d = $q['d'] ?? null;
    $text = $q['text'] ?? null;
    $order = $index + 1;

    // 插入 question
    $stmt = $mysqli->prepare("INSERT INTO question (MaterialID, type, answer, a_options, b_options, c_options, d_options, text_or_answer, question_order)
                              VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssssssssi", $materialID, $type, $answer, $a, $b, $c, $d, $text, $order);
    $stmt->execute();

    // 插入 materials_quations
    $stmt2 = $mysqli->prepare("INSERT INTO materials_quations (MaterialID, name, teacherid)
                               VALUES (?, ?, ?)");
    $stmt2->bind_param("ssi", $materialID, $materialName, $teacherID);
    $stmt2->execute();
}


echo json_encode(["success" => true]);
?>
