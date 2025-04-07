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

if (!$conn) die(json_encode(["error" => "Connection failed"]));

$input = json_decode(file_get_contents('php://input'), true);
$username = $input['username'] ?? '';
$password = $input['password'] ?? '';

// Query ACCOUNTS table
$sql = "SELECT USER_ID, USER_NAME, ACCESS_LEVEL FROM ACCOUNTS WHERE USER_NAME = ? AND PASSWORD = ?";
$params = [$username, $password];
$stmt = $pdo->prepare($sql, $params);
$stmt->execute();

$row = $stmt->fetch(PDO::FETCH_ASSOC);
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