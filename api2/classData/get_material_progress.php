<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$account = $_SESSION['Account'] ?? '123';
$classID = intval($_GET['classID'] ?? 0);
$name = $_GET['name'] ?? '';

$totalQuestions = 0;

// 查找所有該教材名稱的 MaterialID（限 type='題目'）
$stmt = $mysqli->prepare("SELECT MaterialID FROM material WHERE Name = ? AND Type = '題目'");
$stmt->bind_param("s", $name);
$stmt->execute();
$resIDs = $stmt->get_result();

$materialIDs = [];
while ($row = $resIDs->fetch_assoc()) {
    $materialIDs[] = "'" . $mysqli->real_escape_string($row['MaterialID']) . "'";
}

if (count($materialIDs) > 0) {
    $idList = implode(",", $materialIDs);
    $sql = "
        SELECT COUNT(*) AS total FROM question
        WHERE MaterialID IN ($idList)
        AND type IN ('填空', '單選', '多選')
    ";
    $resCount = $mysqli->query($sql);
    $totalQuestions = $resCount ? intval($resCount->fetch_assoc()['total']) : 0;
}

// 取得該班級的所有學生
$students = [];
$stmtStu = $mysqli->prepare("
    SELECT u.UserID, u.Name
    FROM class_member cm
    JOIN user u ON cm.MemberID = u.UserID
    WHERE cm.ClassID = ?
");
$stmtStu->bind_param("i", $classID);
$stmtStu->execute();
$resStu = $stmtStu->get_result();

while ($row = $resStu->fetch_assoc()) {
    $students[$row['UserID']] = [
        'studentName' => $row['Name'],
        'completed' => 0,
        'total' => $totalQuestions,
        'attemptCount' => 0
    ];
}

// 將該班級、教材的學生資料補上
$stmtPro = $mysqli->prepare("
    SELECT studentID, complet_number, attemptCount
    FROM student_complet_quetion
    WHERE classID = ? AND name = ?
");
$stmtPro->bind_param("is", $classID, $name);
$stmtPro->execute();
$resPro = $stmtPro->get_result();

$completedCount = 0;
while ($row = $resPro->fetch_assoc()) {
    $sid = $row['studentID'];
    if (!isset($students[$sid])) continue;

    $students[$sid]['completed'] = $row['complet_number'];
    $students[$sid]['attemptCount'] = $row['attemptCount'];

    if ($totalQuestions > 0 && $row['complet_number'] >= $totalQuestions) {
        $completedCount++;
    }
}

$progressList = array_values($students);

echo json_encode([
    "studentTotal" => count($students),
    "completedTotal" => $completedCount,
    "progress" => $progressList
]);
?>
