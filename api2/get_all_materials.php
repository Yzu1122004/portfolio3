<?php
session_start();
$mysqli = new mysqli("localhost", "root", "", "test");
$mysqli->set_charset("utf8");

$classID = $_GET['classID'] ?? null;
if (!$classID) {
    http_response_code(400);
    exit("缺少 classID");
}

// 查出這個班級已加入的教材名稱
$exists = [];
$res0 = $mysqli->prepare("SELECT name FROM material WHERE classID = ?");
$res0->bind_param("i", $classID);
$res0->execute();
$result0 = $res0->get_result();
while ($row = $result0->fetch_assoc()) {
    $exists[$row['name']] = true;
}

$materials = [];
$seen = [];

// 題目型教材：從 materials_quations 中取得不重複教材名稱與 teacherID
$res1 = $mysqli->query("SELECT name, teacherid FROM materials_quations");
while ($row = $res1->fetch_assoc()) {
    $name = $row['name'];

    if (!isset($seen[$name]) && !isset($exists[$name])) {
        // 找出該 name 對應的所有 MaterialID
        $stmtMID = $mysqli->prepare("SELECT MaterialID FROM materials_quations WHERE name = ?");
        $stmtMID->bind_param("s", $name);
        $stmtMID->execute();
        $resMID = $stmtMID->get_result();

        $idList = [];
        while ($midRow = $resMID->fetch_assoc()) {
            $idList[] = "'" . $mysqli->real_escape_string($midRow['MaterialID']) . "'";
        }

        $total = 0;
        if (count($idList) > 0) {
            $idStr = implode(",", $idList);
            $qRes = $mysqli->query("
                SELECT COUNT(*) AS total 
                FROM question 
                WHERE MaterialID IN ($idStr) 
                AND type IN ('單選', '多選', '填空')
            ");
            if ($qRes) {
                $total = intval($qRes->fetch_assoc()['total']);
            }
        }

        $materials[] = [
            "name" => $name,
            "type" => "題目",
            "count" => $total,
            "teacherID" => $row['teacherid']
        ];
        $seen[$name] = true;
    }
}

// 影片型教材：從 video 表取得未加入的影片
$res2 = $mysqli->query("SELECT name, teacherID FROM video");
while ($row = $res2->fetch_assoc()) {
    $name = $row['name'];

    if (!isset($seen[$name]) && !isset($exists[$name])) {
        $materials[] = [
            "name" => $name,
            "type" => "影片",
            "count" => 1,
            "teacherID" => $row['teacherID']
        ];
        $seen[$name] = true;
    }
}

echo json_encode($materials);
