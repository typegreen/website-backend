<?php
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
$serverName = "MSI";
$connectionOptions = [
    "Database" => "Thesis",
    "Uid" => "", // Your username
    "PWD" => "", // Your password
    "CharacterSet" => "UTF-8"
];

// Connect to database
$conn = sqlsrv_connect($serverName, $connectionOptions);
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
    $checkStmt = sqlsrv_query($conn, $checkSql, $checkParams);
    
    if ($checkStmt === false) {
        throw new Exception("Check username query failed");
    }
    
    if (sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
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
    
    $insertStmt = sqlsrv_query($conn, $insertSql, $insertParams);
    
    if ($insertStmt === false) {
        throw new Exception("Insert user query failed");
    }

    // Get the new user ID
    $newId = null;
    $getIdSql = "SELECT SCOPE_IDENTITY() AS new_id";
    $getIdStmt = sqlsrv_query($conn, $getIdSql);
    if ($getIdStmt && sqlsrv_fetch_array($getIdStmt, SQLSRV_FETCH_ASSOC)) {
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