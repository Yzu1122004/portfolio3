<?php
require_once 'db_connect.php';

// 取得 id 與 type
$id   = isset($_GET['id'])   ? $mysqli->real_escape_string($_GET['id']) : '';
$type = isset($_GET['type']) ? $_GET['type'] : '';

// 只在 type=video 且 id 不為空時才執行
if ($type === 'video' && $id !== '') {

    // 只選取 content，因為沒有 mime_type
    $sql = "
        SELECT `content`
        FROM `video`
        WHERE `MaterialID` = ?
        LIMIT 1
    ";
    $stmt = $mysqli->prepare($sql);
    if (!$stmt) {
        http_response_code(500);
        echo "Database prepare error: " . $mysqli->error;
        exit;
    }

    $stmt->bind_param("s", $id);
    $stmt->execute();
    $stmt->store_result();
    $stmt->bind_result($videoData);

    if ($stmt->fetch()) {
        // 直接以 video/mp4 回傳
        header("Content-Type: video/mp4");
        header("Content-Length: " . strlen($videoData));
        echo $videoData;
    } else {
        http_response_code(404);
        echo "Video not found.";
    }

    $stmt->close();
    exit;
}

// 非法請求
http_response_code(400);
echo "Invalid request.";
