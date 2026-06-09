<?php
// logout.php
session_start();
$_SESSION = [];         // 清空所有 Session 變數
session_destroy();      // 釋放 Session
header("Location: ../index.html");
exit();
?>