<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");

function respond($status, $data) {
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

$apiKey = getenv("SUPABASE_API_KEY");
if (!$apiKey) {
    respond(500, "❌ SUPABASE_API_KEY is missing.");
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
if (!isset($data['user_name']) || !isset($data['password'])) {
    respond(400, array("error" => "Missing credentials"));
    exit;
}
$user_name = $data['user_name'];
$password = $data['password'];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/accounts?user_name=eq." . urlencode($user_name));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey"
]);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$users = json_decode($result, true);
if (is_array($users) && count($users) === 1 && $users[0]['password'] === $password) {
    // Send only the necessary fields
    $user = $users[0];
    respond(200, array(
        "login" => "success",
        "user_id" => $user['user_id'],
        "username" => $user['user_name'],
        "access_level" => $user['access_level']
    ));
} else {
    respond(401, array("login" => "failed"));
}
