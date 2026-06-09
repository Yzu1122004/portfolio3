<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$classID = $_GET['classID'] ?? null;
if (!$classID) {
    http_response_code(400);
    exit("缺少班級 ID");
}

// 取得該班級所有教材的 name 與 type（不重複）
$stmt = $mysqli->prepare("SELECT DISTINCT name, type FROM material WHERE classID = ?");
$stmt->bind_param("i", $classID);
$stmt->execute();
$res = $stmt->get_result();

$materials = [];

while ($row = $res->fetch_assoc()) {
    $name = $row['name'];
    $type = $row['type'];
    $total = 0;

    // 只統計 "題目" 類型下的填空、單選、多選題目數
    if ($type === "題目") {
        $stmtMID = $mysqli->prepare("SELECT MaterialID FROM material WHERE name = ? AND type = '題目' AND classID = ?");
        $stmtMID->bind_param("si", $name, $classID);
        $stmtMID->execute();
        $resMID = $stmtMID->get_result();

        $materialIDs = [];
        while ($midRow = $resMID->fetch_assoc()) {
            $materialIDs[] = "'" . $mysqli->real_escape_string($midRow['MaterialID']) . "'";
        }

        if (count($materialIDs) > 0) {
            $idStr = implode(",", $materialIDs);
            $q = "
                SELECT COUNT(*) AS total FROM question
                WHERE MaterialID IN ($idStr)
                AND type IN ('單選', '多選', '填空')
            ";
            $qRes = $mysqli->query($q);
            if ($qRes) {
                $total = intval($qRes->fetch_assoc()['total']);
            }
        }
    }

    $materials[] = [
        "name" => $name,
        "type" => $type,
        "total" => $total
    ];
}

echo json_encode($materials);
?>
