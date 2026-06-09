<?php
// db_connect.php
// ---------------------------
// 修改下面 4 個參數為你自己的設定
$DB_HOST = '127.0.0.1';      // 資料庫主機 (如果跟網頁同一台常用 localhost 或 127.0.0.1)
$DB_USER = 'root';   // 資料庫使用者名稱
$DB_PASS = '';     // 資料庫密碼
$DB_NAME = 'test';           // 資料庫名稱

// 建立 MySQLi 連線
$mysqli = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);
if ($mysqli->connect_errno) {
    // 無法連線時顯示錯誤並停掉
    echo "無法連線到 MySQL: (" . $mysqli->connect_errno . ") " . $mysqli->connect_error;
    exit();
}
// else{
//     echo "成功";
// }
?>