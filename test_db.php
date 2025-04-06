<?php
header("Content-Type: application/json");

// 1. Verify PHP is running
file_put_contents('php://stderr', "=== TEST_DB.PH P STARTED ===\n");

// 2. Log all environment variables
file_put_contents('php://stderr', print_r($_ENV, true));

// 3. Test database connection
try {
    $dsn = "pgsql:host=" . getenv('DB_HOST') . ";port=" . getenv('DB_PORT') . ";dbname=" . getenv('DB_NAME') . ";sslmode=require";
    file_put_contents('php://stderr', "DSN: $dsn\n");
    
    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3
    ]);
    
    echo json_encode(["success" => true]);
} catch (PDOException $e) {
    file_put_contents('php://stderr', "ERROR: " . $e->getMessage() . "\n");
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>