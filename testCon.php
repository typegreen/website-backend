<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$host = 'db.oyicdamiuhqlwqckxjpe.supabase.co'; // ✅ Direct DB host
$port = '5432';
$db   = 'postgres';
$user = 'postgres'; // ✅ Direct DB user
$pass = 'SimpleNewTest'; // ✅ Password you reset
$dsn  = "pgsql:host=$host;port=$port;dbname=$db;";

echo "Trying to connect with the following config:<br>";
echo "Host: $host<br>";
echo "Port: $port<br>";
echo "DB: $db<br>";
echo "User: $user<br>";
echo "Password: $pass<br>";
echo "DSN: $dsn<br><br>";

try {
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    echo "✅ Successfully connected to Supabase using direct DB connection.";
} catch (PDOException $e) {
    echo "❌ Connection failed: " . $e->getMessage();
}
?>
