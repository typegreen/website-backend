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

try {
    $userId = verifyAdminAccess($conn); // Will exit if not admin

    $sql = "SELECT USER_ID as id, USER_NAME as username, ACCESS_LEVEL as accessLevel FROM ACCOUNTS";
$stmt = $pdo->prepare($sql);
$stmt->execute();

    if ($stmt === false) {
        throw new Exception("Failed to fetch users");
    }

    $users = [];
$row = $stmt->fetch(PDO::FETCH_ASSOC);
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