<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$host = 'aws-0-us-east-1.pooler.supabase.com';
$port = '5432';
$db   = 'postgres';
$user = 'postgres.oyicdamiuhqlwqckxjpe';
$pass = 'your_actual_supabase_password';
$dsn  = "pgsql:host=$host;port=$port;dbname=$db;";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

// Handle OPTIONS request first
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Origin: http://localhost:3000");
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Allow-Credentials: true");
    header("Content-Type: application/json");
    header("Access-Control-Max-Age: 3600");
    http_response_code(200);
    exit();
}

// Regular headers for other requests
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

function getAuthorizationHeader() {
    $headers = null;
    if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $headers = $requestHeaders['Authorization'] ?? $requestHeaders['authorization'] ?? null;
    } else {
        $headers = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['Authorization'] ?? null;
    }
    return $headers ? trim($headers) : null;
}

function verifyAdminAccess($conn) {
    $authHeader = getAuthorizationHeader();
    if (!$authHeader) {
        http_response_code(401);
        die(json_encode(["error" => "Authorization header missing"]));
    }

    if (!preg_match('/Bearer\s(\d+)/i', $authHeader, $matches)) {
        http_response_code(401);
        die(json_encode(["error" => "Invalid token format"]));
    }

    $userId = $matches[1];
    $sql = "SELECT ACCESS_LEVEL FROM ACCOUNTS WHERE USER_ID = ?";
$stmt = $pdo->prepare($sql, [$userId]);
$stmt->execute();
    
$row = $stmt->fetch(PDO::FETCH_ASSOC);
        http_response_code(403);
        die(json_encode(["error" => "User not found"]));
    }

    if (strtoupper($row['ACCESS_LEVEL']) !== 'ADMIN') {
        http_response_code(403);
        die(json_encode(["error" => "Admin access required"]));
    }

    return $userId;
}
?>