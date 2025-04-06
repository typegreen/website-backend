<?php
header("Content-Type: application/json");

function getDBConnection() {
    try {
        $conn = new PDO(
            "pgsql:host=" . $_ENV['DB_HOST'] . ";dbname=" . $_ENV['DB_NAME'],
            $_ENV['DB_USER'],
            $_ENV['DB_PASSWORD']
        );
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        http_response_code(500);
        die(json_encode([
            "error" => "Database connection failed",
            "details" => $e->getMessage()
        ]));
    }
}
?>