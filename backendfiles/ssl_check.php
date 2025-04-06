<?php
header('Content-Type: text/plain');
error_reporting(E_ALL);

// 1. Check certificate exists
echo "1. Certificate Check:\n";
$certPath = '/etc/ssl/certs/supabase.crt';
echo file_exists($certPath) 
    ? "✓ Found at: $certPath\n" 
    : "✗ Missing at: $certPath\n";

// 2. Check PHP extensions
echo "\n2. PHP Extensions:\n";
echo in_array('pdo_pgsql', get_loaded_extensions()) 
    ? "✓ pdo_pgsql enabled\n" 
    : "✗ pdo_pgsql missing\n";

// 3. Test SSL connection
echo "\n3. SSL Connection Test:\n";
try {
    $dsn = "pgsql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT');
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_SSL_MODE => PDO::SSL_MODE_VERIFY_FULL,
        PDO::ATTR_SSL_CERT => $certPath
    ];
    
    new PDO($dsn, getenv('DB_USER'), getenv('DB_PASSWORD'), $options);
    echo "✓ SSL Connection Successful\n";
} catch (PDOException $e) {
    echo "✗ SSL Failed: " . $e->getMessage() . "\n";
}