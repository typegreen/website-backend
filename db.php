<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'];
    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 5  // 5-second timeout
    ]);
    
    echo json_encode([
        "success" => true,
        "message" => "Connected to: " . $_ENV['DB_HOST'],
        "db_version" => $conn->query("SELECT version()")->fetchColumn()
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Connection failed",
        "details" => $e->getMessage(),
        "dsn" => $dsn  // This will show your connection string
    ]);
}