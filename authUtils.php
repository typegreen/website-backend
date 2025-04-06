<?php
header("Access-Control-Allow-Origin: https://website-application-ndfutubgk-pauls-projects-7496f616.vercel.app/");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Access-Control-Max-Age: 3600");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $headers = trim($requestHeaders['Authorization'] ?? '');
    }
    return $headers;
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
    $stmt = $conn->prepare($sql);
    $stmt->execute([$userId]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$row) {
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