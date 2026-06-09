<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$account = $_SESSION['Account'] ;

$stmt = $mysqli->prepare("SELECT UserID, Name FROM user WHERE Account = ?");
$stmt->bind_param("s", $account);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$teacherID = $user['UserID'];

$classes = [];
$res = $mysqli->query("SELECT ClassID, ClassName FROM class WHERE TeacherID = $teacherID");
while ($row = $res->fetch_assoc()) {
    $classes[] = $row;
}

echo json_encode([
    "teacherName" => $user['Name'],
    "classes" => $classes
]);
