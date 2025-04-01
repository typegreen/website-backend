<?php
require_once 'authUtils.php'; // Or your connection file

header('Content-Type: application/json');

try {
    $stmt = $pdo->query("SELECT 1 as connection_test");
    $result = $stmt->fetch();
    echo json_encode([
        'status' => 'success',
        'message' => 'Database connection successful',
        'data' => $result
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Database connection failed',
        'error' => $e->getMessage()
    ]);
}