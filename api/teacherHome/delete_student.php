<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$classID = intval($_POST['classID']);
$studentID = intval($_POST['studentID']);

$stmt = $mysqli->prepare("DELETE FROM class_member WHERE ClassID = ? AND MemberID = ?");
$stmt->bind_param("ii", $classID, $studentID);
$stmt->execute();

echo "success";
