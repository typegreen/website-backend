<?php
// Include your existing authUtils (which now has Supabase connection)
require __DIR__ . '/authUtils.php';

header('Content-Type: application/json');

try {
    // Test 1: Verify database connection
    $stmt = $conn->query("SELECT 1 as connection_test");
    $result = $stmt->fetch();
    
    // Test 2: Verify accounts table exists (Supabase compatibility)
    $tableCheck = $conn->query("SELECT EXISTS (SELECT FROM information_schema.tables WHERE table_name = 'accounts')");
    $tableExists = $tableCheck->fetchColumn();
    
    echo json_encode([
        'status' => 'success',
        'database' => [
            'connection' => $result['connection_test'] === 1,
            'accounts_table_exists' => (bool)$tableExists
        ],
        'environment' => [
            'db_host' => getenv('DB_HOST') ? '***' : 'missing',
            'php_version' => phpversion()
        ]
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'error' => $e->getMessage(),
        'solution' => 'Check Railway variables match Supabase credentials'
    ]);
}