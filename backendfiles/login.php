<?php
header("Access-Control-Allow-Origin: http://localhost:3000");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");
header("Access-Control-Allow-Credentials: true");
header("Content-Type: application/json");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

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

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

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
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode([
        "success" => false,
        "message" => "Invalid credentials"
    ]);
}
?>