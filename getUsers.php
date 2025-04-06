<?php
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();

try {
    $adminId = verifyAdminAccess($conn); // Will exit if not admin

    $sql = "SELECT USER_ID as id, USER_NAME as username, ACCESS_LEVEL as accessLevel FROM ACCOUNTS";
    $stmt = $conn->prepare($sql);
    $stmt->execute();

    if ($stmt === false) {
        throw new Exception("Failed to fetch users");
    }

    $users = [];
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        $users[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($users);

} catch (Exception $e) {
    header('Content-Type: application/json');
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>