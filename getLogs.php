<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");
header("Content-Type: application/json");

function respond($status, $data) {
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

$ch = curl_init();
$apiKey = getenv("SUPABASE_API_KEY");

$userId = $_GET['user_id'] ?? null;
$url = "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/detection_logs";
if ($userId) {
    $url .= "?user_id=eq.$userId";
}

curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey"
]);

$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

respond($httpCode, json_decode($result, true));
