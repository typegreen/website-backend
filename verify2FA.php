<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

function respond($status, $data) {
    echo json_encode(["status" => $status, "response" => $data]);
    exit;
}

$apiKey = getenv("SUPABASE_API_KEY");
$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"] ?? null;
$input_code = $data["code"] ?? null;

if (!$user_id || !$input_code) {
    respond(400, "Missing user_id or code.");
}

// STEP 1: Get latest (most recent) code for user
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/two_fa_authcode?user_id=eq.$user_id&order=expiry.desc&limit=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey"
]);

$result = curl_exec($ch);
curl_close($ch);
$records = json_decode($result, true);

if (!is_array($records) || count($records) === 0) {
    respond(401, "No 2FA code found.");
}

$record = $records[0];
$stored_code = (string)$record["code"];
$expiry_time = strtotime($record["expiry"]);
$current_time = time();

if ((string)$input_code !== $stored_code) {
    respond(403, "Incorrect code.");
}

if ($current_time > $expiry_time) {
    respond(403, "Code expired.");
}

// STEP 2: Delete used 2FA code
$delete = curl_init();
curl_setopt($delete, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/two_fa_authcode?user_id=eq.$user_id");
curl_setopt($delete, CURLOPT_RETURNTRANSFER, true);
curl_setopt($delete, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($delete, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Prefer: return=representation"
]);
curl_exec($delete);
curl_close($delete);

// STEP 3: Get full user data
$userFetch = curl_init();
curl_setopt($userFetch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/accounts?user_id=eq.$user_id");
curl_setopt($userFetch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($userFetch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey"
]);

$userResult = curl_exec($userFetch);
curl_close($userFetch);
$userData = json_decode($userResult, true);

if (is_array($userData) && count($userData) > 0) {
    $user = $userData[0];
    respond(200, [
        "login" => "success",
        "user_id" => $user["user_id"],
        "username" => $user["user_name"],
        "access_level" => $user["access_level"],
        "email" => $user["email"]
    ]);
} else {
    respond(404, "User not found.");
}
