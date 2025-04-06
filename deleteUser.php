<?php
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();

try {
    $adminId = verifyAdminAccess($conn);
    $userIdToDelete = $_GET['id'] ?? null;

    if (!$userIdToDelete || !is_numeric($userIdToDelete)) {
        http_response_code(400);
        die(json_encode(["error" => "Valid user ID is required"]));
    }

    if ($userIdToDelete == $adminId) {
        http_response_code(403);
        die(json_encode(["error" => "Cannot delete your own account"]));
    }

    // Verify user exists
    $checkSql = "SELECT USER_ID FROM ACCOUNTS WHERE USER_ID = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$userIdToDelete]);

    if (!$checkStmt->fetch()) {
        http_response_code(404);
        die(json_encode(["error" => "User not found"]));
    }

    // Delete user
    $deleteSql = "DELETE FROM ACCOUNTS WHERE USER_ID = ?";
    $deleteStmt = $conn->prepare($deleteSql);
    $deleteStmt->execute([$userIdToDelete]);

    if ($deleteStmt->rowCount() === 0) {
        http_response_code(404);
        die(json_encode(["error" => "No user was deleted"]));
    }

    echo json_encode([
        "success" => true,
        "deletedId" => $userIdToDelete
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to delete user",
        "details" => $e->getMessage()
    ]);
}
?>