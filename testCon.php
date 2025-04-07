<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$host = 'aws-0-us-east-1.pooler.supabase.com';
$port = '5432';
$db   = 'postgres';
$user = 'postgres.oyicdamiuhqlwqckxjpe';
$pass = 'SimpleNewTest'; // ✅ this is the correct password
$dsn  = "pgsql:host=$host;port=$port;dbname=$db;";

echo "User: $user<br>";
echo "Password: $pass<br>";
echo "DSN: $dsn<br><br>";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Connected to Supabase successfully!";
} catch (PDOException $e) {
    die("❌ Connection failed: " . $e->getMessage());
}
?>
