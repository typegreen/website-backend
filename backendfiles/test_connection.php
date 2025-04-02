<?php
header('Content-Type: text/plain');
try {
    require __DIR__ . '/authUtils.php';
    $conn->query("SELECT 1");
    echo "✓ Successfully connected to Supabase with SSL";
} catch (PDOException $e) {
    echo "✗ Connection failed: " . $e->getMessage();
}