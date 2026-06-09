<?php
header('Content-Type: application/json; charset=utf-8');
require_once '../php/db_connect.php';

$checks = [
    'connected' => true,
    'database' => $DB_NAME,
    'tables' => []
];

foreach (['user', 'class'] as $tableName) {
    $result = $mysqli->query("SHOW TABLES LIKE '" . $mysqli->real_escape_string($tableName) . "'");
    $exists = $result && $result->num_rows > 0;

    $checks['tables'][$tableName] = [
        'exists' => $exists,
        'row_count' => null,
        'error' => null
    ];

    if ($exists) {
        $countResult = $mysqli->query("SELECT COUNT(*) AS total FROM `$tableName`");
        if ($countResult) {
            $checks['tables'][$tableName]['row_count'] = (int)$countResult->fetch_assoc()['total'];
        } else {
            $checks['tables'][$tableName]['error'] = $mysqli->error;
        }
    }
}

echo json_encode($checks, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
?>
