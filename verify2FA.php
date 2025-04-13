<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");

function respond($status, $data) {
  header("Content-Type: application/json");
  echo json_encode(["status" => $status, "response" => $data]);
}

$apiKey = getenv("SUPABASE_API_KEY");
$data = json_decode(file_get_contents("php://input"), true);

$user_id = $data["user_id"];
$inputCode = $data["code"];

// Fetch the latest 2FA code for the user
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/two_fa_authcode?user_id=eq.$user_id&order=expiry.desc&limit=1");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "apikey: $apiKey",
  "Authorization: Bearer $apiKey"
]);
$result = curl_exec($ch);
curl_close($ch);

$codes = json_decode($result, true);

if (count($codes) === 0) {
  respond("error", "Code not found. Please try again.");
  exit;
}

$storedCode = $codes[0]["code"];
$expiry = strtotime($codes[0]["expiry"]);
$now = time();

if ($inputCode != $storedCode) {
  respond("error", "Incorrect code.");
  exit;
}

if ($now > $expiry) {
  respond("error", "Code expired.");
  exit;
}

// Fetch user info again for login
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/accounts?user_id=eq." . $user_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
  "apikey: $apiKey",
  "Authorization: Bearer $apiKey"
]);
$userResult = curl_exec($ch);
curl_close($ch);

$userInfo = json_decode($userResult, true);

if (count($userInfo) === 1) {
  $user = $userInfo[0];
  respond("success", [
    "user_id" => $user["user_id"],
    "username" => $user["user_name"],
    "access_level" => $user["access_level"]
  ]);
} else {
  respond("error", "User not found.");
}
