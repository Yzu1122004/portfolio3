<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");
if (!isset($_SESSION['Account'])) {
    http_response_code(403);
    exit("請先登入");
}
$account = $_SESSION['Account'];


$newName = $_POST['name'];

$stmt = $mysqli->prepare("UPDATE user SET Name = ? WHERE Account = ?");
$stmt->bind_param("ss", $newName, $account);
$stmt->execute();

echo "success";
?>
