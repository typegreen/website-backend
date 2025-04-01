<?php
require_once 'authUtils.php';

$serverName = "MSI";
$connectionOptions = [
    "Database" => "Thesis",
    "Uid" => "", // Your username
    "PWD" => "", // Your password
    "CharacterSet" => "UTF-8"
];

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
$stmt = sqlsrv_query($conn, $sql, [$input['accessLevel'], $input['id']]);

if ($stmt) {
    echo json_encode(["success" => true]);
} else {
    http_response_code(500);
    echo json_encode(["error" => "Failed to update user"]);
}
?>