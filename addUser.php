<?php
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();

$adminId = verifyAdminAccess($conn);
$input = json_decode(file_get_contents('php://input'), true);

if (empty($input['username']) || empty($input['password']) || empty($input['accessLevel'])) {
    http_response_code(400);
    die(json_encode(["error" => "All fields are required"]));
}

try {
    $checkSql = "SELECT USER_ID FROM ACCOUNTS WHERE USER_NAME = ?";
    $checkStmt = $conn->prepare($checkSql);
    $checkStmt->execute([$input['username']]);

    if ($checkStmt->fetch()) {
        http_response_code(409);
        die(json_encode(["error" => "Username already exists"]));
    }

    $insertSql = "INSERT INTO ACCOUNTS (USER_NAME, PASSWORD, ACCESS_LEVEL) VALUES (?, ?, ?) RETURNING USER_ID";
    $insertStmt = $conn->prepare($insertSql);
    $insertStmt->execute([
        $input['username'],
        $input['password'],
        strtoupper($input['accessLevel'])
    ]);

    $newId = $insertStmt->fetchColumn();

    http_response_code(201);
    echo json_encode([
        "success" => true,
        "user" => [
            "id" => $newId,
            "username" => $input['username'],
            "accessLevel" => $input['accessLevel']
        ]
    ]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode([
        "error" => "Failed to add user",
        "details" => $e->getMessage()
    ]);
}
?>