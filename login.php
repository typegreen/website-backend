<?php
header("Access-Control-Allow-Origin: https://website-application-ndfutubgk-pauls-projects-7496f616.vercel.app/"); // Update this!
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/db.php';
$conn = require_once __DIR__ . '/db.php';

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

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
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(["success" => false, "message" => "Invalid credentials"]);
}
?>