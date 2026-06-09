<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../php/db_connect.php';

// 取得 4 位隨機老師
$sql = "SELECT UserID, Name, userIMG
        FROM `user`
        WHERE Role = 'teacher'
        ORDER BY RAND()
        LIMIT 4";
$res = $mysqli->query($sql);

if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->error]);
    exit;
}

$teachers = [];

while ($row = $res->fetch_assoc()) {
    $teacher = [
        'UserID' => (int)$row['UserID'],
        'Name' => $row['Name'],
        'userIMG' => $row['userIMG'],
        'classes' => []
    ];

    // 查詢該老師最多 3 門課
    $stmt = $mysqli->prepare("SELECT ClassID, ClassName FROM `class` WHERE TeacherID = ? ORDER BY RAND() LIMIT 3");
    $stmt->bind_param('i', $teacher['UserID']);
    $stmt->execute();
    $classRes = $stmt->get_result();
    $teacher['classes'] = $classRes->fetch_all(MYSQLI_ASSOC);
    $stmt->close();

    $teachers[] = $teacher;
}

echo json_encode(['teachers' => $teachers], JSON_UNESCAPED_UNICODE);
$mysqli->close();
