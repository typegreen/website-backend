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

require_once 'authUtils.php';

    "CharacterSet" => "UTF-8"
];

$adminId = verifyAdminAccess($conn);
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['id']) || empty($input['accessLevel'])) {
    http_response_code(400);
    die(json_encode(["error" => "Missing required fields"]));
}

// Prevent self-demotion
if ($input['id'] == $adminId && $input['accessLevel'] !== 'ADMIN') {
    http_response_code(403);
    die(json_encode(["error" => "Cannot remove your own admin status"]));
}

$sql = "UPDATE ACCOUNTS SET ACCESS_LEVEL = ? WHERE USER_ID = ?";
$stmt = $pdo->prepare($sql, [$input['accessLevel'], $input['id']]);
$stmt->execute();

if ($stmt) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update user"]);
}
?>