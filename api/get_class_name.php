<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");
$account = $_SESSION['Account'];
$classID = $_GET['classID'] ?? null;
if (!$classID) {
    http_response_code(400);
    exit(json_encode(["error" => "缺少 classID"]));
}

$stmt = $mysqli->prepare("SELECT ClassName FROM class WHERE ClassID = ?");
$stmt->bind_param("i", $classID);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode(["className" => $row['ClassName']]);
} else {
    echo json_encode(["className" => null]);
}
?>
