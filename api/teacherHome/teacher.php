<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$account = $_SESSION['Account'] ;

// 取得教師資料
$stmt = $mysqli->prepare("SELECT UserID, Name, userIMG FROM user WHERE Account = ? AND Role = 'teacher'");

$stmt->bind_param("s", $account);
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();

$teacherID = $result['UserID'];
$name = $result['Name'];

// 查詢班級
$classes = [];
$res = $mysqli->query("SELECT ClassID, ClassName FROM class WHERE TeacherID = $teacherID");
while ($row = $res->fetch_assoc()) {
    $classes[] = [
        'classID' => $row['ClassID'],
        'className' => $row['ClassName']
    ];
}
$userIMG = $result['userIMG'] ?? null;

echo json_encode([
    'name' => $name,
    'userIMG' => $userIMG,
    'classes' => $classes
]);

