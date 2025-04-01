<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "=== Environment Test ===\n";
echo "PHP Version: " . phpversion() . "\n";
echo "PORT: " . (getenv('PORT') ?: 'NOT SET') . "\n";

// Test database connection
try {
    $dsn = "pgsql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT').";dbname=".getenv('DB_NAME');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_SSL_MODE => PDO::SSL_MODE_VERIFY_FULL
    ];
    
    echo "\nConnecting to: " . getenv('DB_HOST') . "\n";
    
    $db = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $options);
    echo "✓ SSL Connection Successful\n";
    
    // Verify tables exist
    $tables = $db->query("SELECT table_name FROM information_schema.tables WHERE table_schema='public'")->fetchAll();
    echo "Tables: " . implode(', ', array_column($tables, 'table_name')) . "\n";
    
} catch (PDOException $e) {
    echo "\n✗ Database Error: " . $e->getMessage() . "\n";
    
    // Test without SSL (diagnostic only)
    try {
        $options[PDO::ATTR_SSL_MODE] = PDO::SSL_MODE_ALLOW;
        $db = new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $options);
        echo "⚠ Connected WITHOUT SSL (fix your SSL config!)\n";
    } catch (PDOException $e2) {
        echo "✗ Non-SSL Connection ALSO failed: " . $e2->getMessage() . "\n";
    }
}