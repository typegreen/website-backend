<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");

// Helper function for responses
function respond($status, $data) {
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

// Get environment variable
$apiKey = getenv("SUPABASE_API_KEY");
$baseUrl = getenv("SUPABASE_BASE_URL");

if (!$apiKey || !$baseUrl) {
    respond(500, "❌ Missing Supabase credentials in environment.");
    exit;
}

// Read incoming JSON data
$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ["location", "date", "time", "image_code", "rice_crop_image", "classification", "user_id"];
foreach ($required as $key) {
    if (!isset($data[$key]) || empty($data[$key])) {
        respond(400, "❌ Missing or empty field: $key");
        exit;
    }
}

// Construct data payload for Supabase
$payload = [
    "location" => $data["location"],
    "date_of_detection" => $data["date"],
    "time_of_detection" => $data["time"],
    "image_code" => $data["image_code"],
    "rice_crop_image" => $data["rice_crop_image"],
    "classification" => $data["classification"],
    "user_id" => intval($data["user_id"])
];

// Send request to Supabase
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, "$baseUrl/rest/v1/detection_logs");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
    "Prefer: return=representation"
]);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
$result = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

respond($httpCode, json_decode($result, true));
?>
