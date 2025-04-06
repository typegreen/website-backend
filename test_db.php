<?php
header("Content-Type: application/json");
error_reporting(E_ALL);

// Debug environment variables
$env = [
    'DB_HOST' => getenv('DB_HOST'),
    'DB_PORT' => getenv('DB_PORT'),
    'DB_NAME' => getenv('DB_NAME'),
    'PHP_VERSION' => phpversion()
];

try {
    $dsn = "pgsql:host={$env['DB_HOST']};port={$env['DB_PORT']};dbname={$env['DB_NAME']};sslmode=require";
    $conn = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_TIMEOUT => 3
    ]);
    
    echo json_encode([
        "success" => true,
        "env" => $env,
        "version" => $conn->query("SELECT version()")->fetchColumn()
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Connection failed",
        "env" => $env,
        "details" => $e->getMessage()
    ]);
}
?>