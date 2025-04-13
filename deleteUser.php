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

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    respond(400, array("error" => "Invalid or missing user_id"));
    exit;
}

$user_id = intval($_GET['id']);

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/accounts?user_id=eq." . $user_id);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Prefer: return=representation"
]);
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);
respond($httpCode, json_decode($result, true));
