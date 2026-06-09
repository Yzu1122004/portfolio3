<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$classID = intval($_GET['classID'] ?? 0);

// 查學生
$stmt = $mysqli->prepare("
    SELECT u.UserID, u.Account, u.Name
    FROM class_member cm
    JOIN user u ON cm.MemberID = u.UserID
    WHERE cm.ClassID = ?
");
$stmt->bind_param("i", $classID);
$stmt->execute();
$res = $stmt->get_result();

$students = [];
while ($row = $res->fetch_assoc()) {
    $students[] = $row;
}

echo json_encode([
    'classID' => $classID,
    'studentCount' => count($students),
    'students' => $students
]);
