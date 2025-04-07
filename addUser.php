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

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Database configuration
    "CharacterSet" => "UTF-8"
];

// Connect to database
if ($conn === false) {
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed", "details" => sqlsrv_errors()]));
}

// Get and validate input
$input = json_decode(file_get_contents('php://input'), true);
if (json_last_error() !== JSON_ERROR_NONE) {
    http_response_code(400);
    die(json_encode(["error" => "Invalid JSON input"]));
}

if (empty($input['username']) || empty($input['password']) || empty($input['accessLevel'])) {
    http_response_code(400);
    die(json_encode(["error" => "All fields are required"]));
}

try {
    // Check if username exists
    $checkSql = "SELECT USER_ID FROM ACCOUNTS WHERE USER_NAME = ?";
    $checkParams = [$input['username']];
$stmt = $pdo->prepare($checkSql, $checkParams);
$stmt->execute();
    
    if ($checkStmt === false) {
        throw new Exception("Check username query failed");
    }
    
        http_response_code(409);
        die(json_encode(["error" => "Username already exists"]));
    }

    // Insert new user
    $insertSql = "INSERT INTO ACCOUNTS (USER_NAME, PASSWORD, ACCESS_LEVEL) VALUES (?, ?, ?)";
    $insertParams = [
        $input['username'],
        $input['password'], // Note: Should be hashed in production!
        strtoupper($input['accessLevel'])
    ];
    
$stmt = $pdo->prepare($insertSql, $insertParams);
$stmt->execute();
    
    if ($insertStmt === false) {
        throw new Exception("Insert user query failed");
    }

    // Get the new user ID
    $newId = null;
    $getIdSql = "SELECT SCOPE_IDENTITY() AS new_id";
$stmt = $pdo->prepare($getIdSql);
$stmt->execute();
        $newId = sqlsrv_get_field($getIdStmt, 0);
    }

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $newId,
            "username" => $input['username'],
            "accessLevel" => $input['accessLevel']
        ]
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to add user",
        "details" => $e->getMessage(),
        "sql_errors" => sqlsrv_errors()
    ]);
} finally {
    if (isset($checkStmt)) sqlsrv_free_stmt($checkStmt);
    if (isset($insertStmt)) sqlsrv_free_stmt($insertStmt);
    if (isset($conn)) sqlsrv_close($conn);
}
?>