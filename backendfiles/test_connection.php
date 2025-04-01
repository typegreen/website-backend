<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);

// 1. Test PHP environment
echo "PHP Version: ".phpversion()."\n";
echo "PORT: ".getenv('PORT')."\n";

// 2. Test DB connection
try {
    $db = new PDO(
        "pgsql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT'),
        getenv('DB_USER'),
        getenv('DB_PASSWORD'),
        [PDO::ATTR_SSL_MODE => PDO::SSL_MODE_ALLOW] // Temporary
    );
    echo "✓ Connected to Supabase\n";
} catch (PDOException $e) {
    echo "✗ Connection failed: ".$e->getMessage()."\n";
}