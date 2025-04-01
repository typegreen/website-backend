<?php
// ======================
// Database Configuration
// ======================
$dbHost = getenv('DB_HOST');      // From Railway variables
$dbName = getenv('DB_NAME');      // Typically 'postgres'
$dbUser = getenv('DB_USER');      // Typically 'postgres'
$dbPass = getenv('DB_PASSWORD');  // Your Supabase password
$dbPort = getenv('DB_PORT') ?: '5432';

// Secure PostgreSQL connection with SSL enforcement
try {
    $conn = new PDO(
        "pgsql:host=$dbHost;port=$dbPort;dbname=$dbName",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_SSL_MODE => PDO::SSL_MODE_VERIFY_FULL, // Strict SSL validation
            PDO::ATTR_PERSISTENT => false,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]
    );
} catch (PDOException $e) {
    error_log("Database connection failed: " . $e->getMessage());
    http_response_code(503);
    die(json_encode(["error" => "Service unavailable"]));
}

// ======================
// CORS Configuration
// ======================
$allowedOrigins = [
    'https://animonitor.vercel.app',  // Production frontend
    'http://localhost:3000'           // For local development
];

$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if (in_array($origin, $allowedOrigins)) {
    header("Access-Control-Allow-Origin: $origin");
    header("Access-Control-Allow-Credentials: true");
}

// Preflight OPTIONS handling
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
    header("Access-Control-Allow-Headers: Content-Type, Authorization");
    header("Access-Control-Max-Age: 3600");
    header("Content-Type: application/json");
    http_response_code(204);
    exit();
}

header("Content-Type: application/json");

// ======================
// Authentication Utilities
// ======================
function getAuthorizationHeader() {
    $headers = null;
    
    // Try Apache headers first
    if (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $headers = $requestHeaders['Authorization'] ?? $requestHeaders['authorization'] ?? null;
    }
    
    // Fallback to standard server headers
    if (!$headers) {
        $headers = $_SERVER['HTTP_AUTHORIZATION'] ?? $_SERVER['REDIRECT_HTTP_AUTHORIZATION'] ?? null;
    }
    
    return $headers ? trim(str_replace('Bearer ', '', $headers)) : null;
}

function verifyAdminAccess($conn) {
    $token = getAuthorizationHeader();
    
    if (!$token) {
        http_response_code(401);
        die(json_encode(["error" => "Authorization token required"]));
    }

    try {
        // Verify token format (example: UUID or JWT)
        if (!preg_match('/^[a-f0-9]{8}-([a-f0-9]{4}-){3}[a-f0-9]{12}$/i', $token)) {
            throw new Exception("Invalid token format");
        }

        $stmt = $conn->prepare("
            SELECT access_level 
            FROM accounts 
            WHERE auth_token = :token AND token_expires_at > NOW()
        ");
        $stmt->execute([':token' => $token]);
        $user = $stmt->fetch();

        if (!$user) {
            http_response_code(403);
            die(json_encode(["error" => "Invalid or expired token"]));
        }

        if (strtoupper($user['access_level']) !== 'ADMIN') {
            http_response_code(403);
            die(json_encode(["error" => "Insufficient privileges"]));
        }

        return $user;
    } catch (Exception $e) {
        error_log("Auth error: " . $e->getMessage());
        http_response_code(500);
        die(json_encode(["error" => "Authentication failed"]));
    }
}

// ======================
// Security Headers
// ======================
header("X-Content-Type-Options: nosniff");
header("X-Frame-Options: DENY");
header("X-XSS-Protection: 1; mode=block");