<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../php/db_connect.php';

$sql = "SELECT UserID, Name, userIMG
        FROM `user`
        WHERE Role = 'teacher'
        ORDER BY RAND()
        LIMIT 4";
$res = $mysqli->query($sql);

if (!$res) {
    http_response_code(500);
    echo json_encode(['error' => $mysqli->error], JSON_UNESCAPED_UNICODE);
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

    $stmt = $mysqli->prepare("SELECT ClassID, ClassName FROM `class` WHERE TeacherID = ? ORDER BY RAND() LIMIT 3");
    if (!$stmt) {
        http_response_code(500);
        echo json_encode(['error' => $mysqli->error], JSON_UNESCAPED_UNICODE);
        exit;
    }

    $stmt->bind_param('i', $teacher['UserID']);
    $stmt->execute();
    $stmt->bind_result($classID, $className);

    while ($stmt->fetch()) {
        $teacher['classes'][] = [
            'ClassID' => $classID,
            'ClassName' => $className
        ];
    }

    $stmt->close();
    $teachers[] = $teacher;
}

echo json_encode(['teachers' => $teachers], JSON_UNESCAPED_UNICODE);
$mysqli->close();
?>
