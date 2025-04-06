<?php
header("Content-Type: application/json");
error_reporting(E_ALL);
ini_set('display_errors', '1');

// Debug env vars
error_log("=== ENV VARS ===");
error_log("DB_HOST: " . ($_ENV['DB_HOST'] ?? 'NOT SET'));
error_log("DB_PORT: " . ($_ENV['DB_PORT'] ?? 'NOT SET'));

try {
    $dsn = "pgsql:host=" . $_ENV['DB_HOST'] . ";port=" . $_ENV['DB_PORT'] . ";dbname=" . $_ENV['DB_NAME'];
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_SSL_MODE => PDO::SSL_MODE_PREFER
    ];
    
    error_log("Connecting to: " . $dsn);
    $conn = new PDO($dsn, $_ENV['DB_USER'], $_ENV['DB_PASSWORD'], $options);
    
    echo json_encode([
        "success" => true,
        "message" => "Connected to PostgreSQL",
        "version" => $conn->query("SELECT version()")->fetchColumn()
    ]);
    
} catch (PDOException $e) {
    error_log("Connection failed: " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "error" => "Database connection failed",
        "details" => $e->getMessage(),
        "dsn" => $dsn ?? 'Not generated'
    ]);
}
?>