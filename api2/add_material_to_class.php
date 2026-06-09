<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");
$account = $_SESSION['Account'];
$classID = $_POST['classID'];
$name = $_POST['name'];
$type = $_POST['type'];
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
if ($type === "題目") {
    $res = $mysqli->prepare("SELECT MaterialID FROM materials_quations WHERE name = ?");
} else {
    $res = $mysqli->prepare("SELECT MaterialID FROM video WHERE name = ?");
}

$res->bind_param("s", $name);
$res->execute();
$result = $res->get_result();

while ($row = $result->fetch_assoc()) {
    $stmt = $mysqli->prepare("INSERT INTO material (classID, name, type, materialID) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("isss", $classID, $name, $type, $row['MaterialID']);
    $stmt->execute();
}

echo json_encode(["success" => true]);
?>
