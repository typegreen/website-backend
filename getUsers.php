<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");

function respond($status, $data) {
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

// 🔍 DEBUG: Uncomment this to check if your API key is loaded
// var_dump(getenv("SUPABASE_API_KEY"));

$apiKey = getenv("SUPABASE_API_KEY");

if (!$apiKey) {
    respond(500, "❌ Environment variable SUPABASE_API_KEY is not found.");
    exit;
}

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/accounts");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey"
]);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

respond($httpCode, json_decode($result, true));
