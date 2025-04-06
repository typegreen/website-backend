<?php
header("Content-Type: application/json");
require_once __DIR__ . '/db.php';

try {
    $conn = getDBConnection();
    $stmt = $conn->query("SELECT 1 AS test"); // Simple PostgreSQL query
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result['test'] == 1) {
        echo json_encode(["success" => true, "message" => "Database connection successful!"]);
    } else {
        throw new Exception("Unexpected query result");
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => "Database connection failed", "details" => $e->getMessage()]);
}
?>