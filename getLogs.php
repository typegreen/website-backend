<?php
<<<<<<< HEAD
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();
=======
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
$serverName = "MSI";
$connectionOptions = [
    "Database" => "Thesis",
    "Uid" => "", // Add your username
    "PWD" => "", // Add your password
    "CharacterSet" => "UTF-8"
];

// Connect to SQL Server with error handling
$conn = sqlsrv_connect($serverName, $connectionOptions);
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
>>>>>>> 2564443 (Testing)

$authHeader = getAuthorizationHeader();
$userId = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $userId = $matches[1];
    } else {
        $userId = $authHeader;
    }
}

<<<<<<< HEAD
=======
// Validate user ID
>>>>>>> 2564443 (Testing)
if (!$userId || !is_numeric($userId)) {
    http_response_code(401);
    die(json_encode([
        "error" => "Unauthorized",
        "message" => "Valid authorization token required"
    ]));
}

<<<<<<< HEAD
$sql = "SELECT 
          IMAGE_CODE as imageCode,
          TO_CHAR(DATE_OF_DETECTION, 'YYYY-MM-DD') as date,
          CASE
            WHEN TIME_OF_DETECTION IS NULL THEN '00:00:00'
            ELSE TO_CHAR(TIME_OF_DETECTION, 'HH24:MI:SS')
=======
// Secure database query with parameterization
$sql = "SELECT 
          IMAGE_CODE as imageCode,
          CONVERT(varchar, DATE_OF_DETECTION, 23) as date,
          CASE
            WHEN TIME_OF_DETECTION IS NULL THEN '00:00:00'
            ELSE CONVERT(varchar, TIME_OF_DETECTION, 108)
>>>>>>> 2564443 (Testing)
          END as rawTime,
          LOCATION as location,
          CLASSIFICATION as classification,
          RICE_CROP_IMAGE as img
        FROM DETECTION_LOGS
        WHERE USER_ID = ?
<<<<<<< HEAD
        ORDER BY LOG_ID ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$results = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
=======
        ORDER BY DATE_OF_DETECTION DESC";

$params = array($userId);
$stmt = sqlsrv_query($conn, $sql, $params);

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
while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
>>>>>>> 2564443 (Testing)
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
        'img' => 'webapp/Thesis/website-backend/assets/' . 
                htmlspecialchars(basename($row['img']))
    ];
}

<<<<<<< HEAD
=======
// Return JSON response
>>>>>>> 2564443 (Testing)
http_response_code(200);
echo json_encode([
    "success" => true,
    "data" => $results,
    "count" => count($results)
], JSON_UNESCAPED_SLASHES);
<<<<<<< HEAD
=======

// Close connection
sqlsrv_free_stmt($stmt);
sqlsrv_close($conn);
>>>>>>> 2564443 (Testing)
?>