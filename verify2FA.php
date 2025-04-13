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

if (!isset($data['user_id']) || !isset($data['code'])) {
  respond(400, ["error" => "Missing user_id or code."]);
  exit;
}

$user_id = intval($data['user_id']);
$code = intval($data['code']);

// 1. Fetch code from Supabase
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

if (count($records) === 0) {
  respond(401, ["verified" => false, "error" => "Code not found."]);
  exit;
}

$record = $records[0];
if ($record['code'] == $code && strtotime($record['expiry']) > time()) {
  respond(200, ["verified" => true]);
} else {
  respond(401, ["verified" => false, "error" => "Invalid or expired code."]);
}
