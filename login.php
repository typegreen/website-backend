<?php
<<<<<<< HEAD
header("Access-Control-Allow-Origin: https://website-application-ndfutubgk-pauls-projects-7496f616.vercel.app/"); // Update this!
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
=======
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
>>>>>>> 2564443 (Testing)
    http_response_code(200);
    exit();
}

<<<<<<< HEAD
require_once __DIR__ . '/db.php';
$conn = require_once __DIR__ . '/db.php';
=======
// Database configuration
$serverName = "MSI";
$connectionOptions = [
    "Database" => "Thesis",
    "Uid" => "",
    "PWD" => "",
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if (!$conn) die(json_encode(["error" => "Connection failed"]));
>>>>>>> 2564443 (Testing)

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

<<<<<<< HEAD
// PostgreSQL query (changed from SQL Server)
$sql = "SELECT user_id, user_name, access_level FROM accounts WHERE user_name = ? AND password = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$username, $password]);

if ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $row['user_id'],
            "username" => $row['user_name'],
            "accessLevel" => $row['access_level']
=======
// Query ACCOUNTS table
$sql = "SELECT USER_ID, USER_NAME, ACCESS_LEVEL FROM ACCOUNTS WHERE USER_NAME = ? AND PASSWORD = ?";
$params = [$username, $password];
$stmt = sqlsrv_query($conn, $sql, $params);

if ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $row['USER_ID'],
            "username" => $row['USER_NAME'],
            "accessLevel" => $row['ACCESS_LEVEL']
>>>>>>> 2564443 (Testing)
        ]
    ]);
} else {
    http_response_code(401);
<<<<<<< HEAD
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
=======
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
>>>>>>> 2564443 (Testing)
}
?>