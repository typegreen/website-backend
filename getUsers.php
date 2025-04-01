<?php
<<<<<<< HEAD
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();

try {
    $adminId = verifyAdminAccess($conn); // Will exit if not admin

    $sql = "SELECT USER_ID as id, USER_NAME as username, ACCESS_LEVEL as accessLevel FROM ACCOUNTS";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
=======
require_once 'authUtils.php';

// Database connection
$serverName = "MSI";
$connectionOptions = [
    "Database" => "Thesis",
    "Uid" => "", // Your username
    "PWD" => "", // Your password
    "CharacterSet" => "UTF-8"
];

$conn = sqlsrv_connect($serverName, $connectionOptions);
if ($conn === false) {
    header('Content-Type: application/json');
    http_response_code(500);
    die(json_encode(["error" => "Database connection failed"]));
}

try {
    $userId = verifyAdminAccess($conn); // Will exit if not admin

    $sql = "SELECT USER_ID as id, USER_NAME as username, ACCESS_LEVEL as accessLevel FROM ACCOUNTS";
    $stmt = sqlsrv_query($conn, $sql);
>>>>>>> 2564443 (Testing)

    if ($stmt === false) {
        throw new Exception("Failed to fetch users");
    }

    $users = [];
<<<<<<< HEAD
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
=======
    while ($row = sqlsrv_fetch_array($stmt, SQLSRV_FETCH_ASSOC)) {
>>>>>>> 2564443 (Testing)
        $users[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($users);
<<<<<<< HEAD

=======
    
>>>>>>> 2564443 (Testing)
} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
<<<<<<< HEAD
=======
} finally {
    if (isset($stmt)) {
        sqlsrv_free_stmt($stmt);
    }
    if (isset($conn)) {
        sqlsrv_close($conn);
    }
>>>>>>> 2564443 (Testing)
}
?>