<?php
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();

try {
    $adminId = verifyAdminAccess($conn);
    $input = json_decode(file_get_contents('php://input'), true);

    if (empty($input['id']) || empty($input['accessLevel'])) {
        http_response_code(400);
        die(json_encode(["error" => "Missing required fields"]));
    }

    // Prevent self-demotion
    if ($input['id'] == $adminId && $input['accessLevel'] !== 'ADMIN') {
        http_response_code(403);
        die(json_encode(["error" => "Cannot remove your own admin status"]));
    }

    $sql = "UPDATE ACCOUNTS SET ACCESS_LEVEL = ? WHERE USER_ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->execute([$input['accessLevel'], $input['id']]);

    if ($stmt->rowCount() === 0) {
        http_response_code(404);
        die(json_encode(["error" => "User not found or no changes made"]));
    }

    echo json_encode(["success" => true]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to update user",
        "details" => $e->getMessage()
    ]);
}
?>