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

require_once 'authUtils.php';

// Database connection
$serverName = "MSI";
$connectionOptions = [
    "Database" => "Thesis",
    "Uid" => "", // Your username
    "PWD" => "", // Your password
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

try {
    $userId = verifyAdminAccess($conn); // Will exit if not admin

    $sql = "SELECT USER_ID as id, USER_NAME as username, ACCESS_LEVEL as accessLevel FROM ACCOUNTS";
    $stmt = sqlsrv_query($conn, $sql);

    if ($stmt === false) {
        throw new Exception("Failed to fetch users");
    }

    $users = [];
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
        $users[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($users);
    
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
} finally {
    if (isset($stmt)) {
        sqlsrv_free_stmt($stmt);
    }
    if (isset($conn)) {
        sqlsrv_close($conn);
    }
}
?>