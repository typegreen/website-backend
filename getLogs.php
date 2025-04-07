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

// Enhanced CORS and security headers
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");
header("Cache-Control: no-cache, no-store, must-revalidate");
header("Pragma: no-cache");
header("Expires: 0");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
    "CharacterSet" => "UTF-8"
];

// Connect to SQL Server with error handling
if ($conn === false) {
    error_log("Database connection failed: " . print_r(sqlsrv_errors(), true));
    http_response_code(500);
    die(json_encode([
        "error" => "Database connection failed",
        "details" => (ENVIRONMENT === 'development') ? sqlsrv_errors() : null
    ]));
}

// Get authorization token from headers
function getAuthorizationHeader() {
    $headers = null;
    if (isset($_SERVER['Authorization'])) {
        $headers = trim($_SERVER['Authorization']);
    } elseif (isset($_SERVER['HTTP_AUTHORIZATION'])) {
        $headers = trim($_SERVER['HTTP_AUTHORIZATION']);
    } elseif (function_exists('apache_request_headers')) {
        $requestHeaders = apache_request_headers();
        $requestHeaders = array_combine(
            array_map('ucwords', array_keys($requestHeaders)),
            array_values($requestHeaders)
        );
        if (isset($requestHeaders['Authorization'])) {
            $headers = trim($requestHeaders['Authorization']);
        }
    }
    return $headers;
}

$authHeader = getAuthorizationHeader();
$userId = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $userId = $matches[1];
    } else {
        $userId = $authHeader;
    }
}

// Validate user ID
if (!$userId || !is_numeric($userId)) {
    http_response_code(401);
    die(json_encode([
        "error" => "Unauthorized",
        "message" => "Valid authorization token required"
    ]));
}

// Secure database query with parameterization
$sql = "SELECT 
          IMAGE_CODE as imageCode,
          CONVERT(varchar, DATE_OF_DETECTION, 23) as date,
          CASE
            WHEN TIME_OF_DETECTION IS NULL THEN '00:00:00'
            ELSE CONVERT(varchar, TIME_OF_DETECTION, 108)
          END as rawTime,
          LOCATION as location,
          CLASSIFICATION as classification,
          RICE_CROP_IMAGE as img
        FROM DETECTION_LOGS
        WHERE USER_ID = ?
        ORDER BY DATE_OF_DETECTION DESC";

$params = array($userId);
$stmt = $pdo->prepare($sql, $params);
$stmt->execute();

if ($stmt === false) {
    error_log("Query failed: " . print_r(sqlsrv_errors(), true));
    http_response_code(500);
    die(json_encode([
        "error" => "Database query failed",
        "details" => (ENVIRONMENT === 'development') ? sqlsrv_errors() : null
    ]));
}

// Process and sanitize results
$results = [];
$row = $stmt->fetch(PDO::FETCH_ASSOC);
    $timeDisplay = 'N/A';
    if (!empty($row['rawTime'])) {
        $timeParts = explode(':', $row['rawTime']);
        if (count($timeParts) >= 2) {
            $hours = (int)$timeParts[0];
            $minutes = $timeParts[1];
            $period = $hours >= 12 ? 'PM' : 'AM';
            $displayHours = $hours % 12;
            $displayHours = $displayHours ? $displayHours : 12;
            $timeDisplay = sprintf("%02d:%02d %s", $displayHours, $minutes, $period);
        }
    }

    $results[] = [
        'imageCode' => htmlspecialchars($row['imageCode']),
        'date' => htmlspecialchars($row['date']),
        'time' => htmlspecialchars($timeDisplay),
        'location' => htmlspecialchars($row['location']),
        'classification' => htmlspecialchars($row['classification']),
                htmlspecialchars(basename($row['img']))
    ];
}

// Return JSON response
http_response_code(200);
echo json_encode([
    "success" => true,
    "data" => $results,
    "count" => count($results)
], JSON_UNESCAPED_SLASHES);

// Close connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
?>