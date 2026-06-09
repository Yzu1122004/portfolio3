<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");
$account = $_SESSION['Account'];
$classID = intval($_GET['classID'] ?? 0);
$materials = [];

$res = $mysqli->query("SELECT DISTINCT Name FROM material WHERE ClassID = $classID");
while ($row = $res->fetch_assoc()) {
    $materials[] = $row['Name'];
}

echo json_encode($materials);
