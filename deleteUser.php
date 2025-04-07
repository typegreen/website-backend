<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
$host = 'aws-0-us-east-1.pooler.supabase.com';
$port = '5432';
$db   = 'postgres';
$user = 'postgres.oyicdamiuhqlwqckxjpe';
$pass = 'YOUR_SUPABASE_PASSWORD'; // Replace with env variable or actual password
$dsn = "pgsql:host=$host;port=$port;dbname=$db;";
try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight request
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once 'authUtils.php';

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
    die(json_encode([
        "error" => "Database connection failed",
        "details" => sqlsrv_errors()
    ]));
}

try {
    $adminId = verifyAdminAccess($conn);
    $userIdToDelete = $_GET['id'] ?? null;

    if (!$userIdToDelete || !is_numeric($userIdToDelete)) {
        http_response_code(400);
        die(json_encode(["error" => "Valid user ID is required"]));
    }

    if ($userIdToDelete == $adminId) {
        http_response_code(403);
        die(json_encode(["error" => "Cannot delete your own account"]));
    }

    // First verify user exists
    $checkSql = "SELECT USER_ID FROM ACCOUNTS WHERE USER_ID = ?";
    $checkStmt = sqlsrv_query($conn, $checkSql, [$userIdToDelete]);
    
    if (!$checkStmt || !sqlsrv_fetch_array($checkStmt, SQLSRV_FETCH_ASSOC)) {
        http_response_code(404);
        die(json_encode(["error" => "User not found"]));
    }

    // Delete user
    $deleteSql = "DELETE FROM ACCOUNTS WHERE USER_ID = ?";
    $deleteStmt = sqlsrv_query($conn, $deleteSql, [$userIdToDelete]);
    
    if ($deleteStmt === false) {
        throw new Exception("Delete operation failed");
    }

    // Check if any rows were actually deleted
    if (sqlsrv_rows_affected($deleteStmt) === 0) {
        http_response_code(404);
        die(json_encode(["error" => "No user was deleted"]));
    }

    echo json_encode(["success" => true, "deletedId" => $userIdToDelete]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to delete user",
        "details" => $e->getMessage(),
        "sql_errors" => sqlsrv_errors()
    ]);
} finally {
    if (isset($checkStmt)) sqlsrv_free_stmt($checkStmt);
    if (isset($deleteStmt)) sqlsrv_free_stmt($deleteStmt);
    if (isset($conn)) sqlsrv_close($conn);
}
?>