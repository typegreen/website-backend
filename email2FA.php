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
    header("Access-Control-Allow-Origin: *"); // Ensure always present
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

$apiKey = getenv("SUPABASE_API_KEY");
$brevoApiKey = getenv("BREVO_API_KEY");

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["email"]) || !isset($data["user_id"])) {
    respond(400, "Missing email or user_id.");
    exit;
}

$email = $data["email"];
$user_id = $data["user_id"];
$code = rand(100000, 999999);

// Store code in Supabase
$store = curl_init();
curl_setopt($store, CURLOPT_URL, "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/two_fa_authcode");
curl_setopt($store, CURLOPT_RETURNTRANSFER, true);
curl_setopt($store, CURLOPT_POST, true);
curl_setopt($store, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Content-Type: application/json",
    "Prefer: return=representation"
]);
curl_setopt($store, CURLOPT_POSTFIELDS, json_encode([
    "user_id" => $user_id,
    "code" => $code,
    "expiry" => date("c", strtotime("+10 minutes"))
]));
$storeResult = curl_exec($store);
curl_close($store);

// Send email with Brevo API
$payload = [
    "sender" => [
        "name" => "AniMonitor",
        "email" => "nlplalvarez@gmail.com"
    ],
    "to" => [[ "email" => $email ]],
    "subject" => "Your AniMonitor 2FA Code",
    "htmlContent" => "
        <html>
            <body>
                <p>Hi!</p>
                <p>Your AniMonitor verification code is:</p>
                <h2>$code</h2>
                <p>This code will expire in 10 minutes.</p>
            </body>
        </html>"
];

$send = curl_init();
curl_setopt($send, CURLOPT_URL, "https://api.brevo.com/v3/smtp/email");
curl_setopt($send, CURLOPT_RETURNTRANSFER, true);
curl_setopt($send, CURLOPT_POST, true);
curl_setopt($send, CURLOPT_HTTPHEADER, [
    "Content-Type: application/json",
    "api-key: $brevoApiKey"
]);
curl_setopt($send, CURLOPT_POSTFIELDS, json_encode($payload));

$response = curl_exec($send);
$httpCode = curl_getinfo($send, CURLINFO_HTTP_CODE);
curl_close($send);

if ($httpCode === 201) {
    respond(200, "2FA code sent to $email");
} else {
    respond(500, [
        "error" => "Failed to send 2FA code.",
        "brevo_response" => $response,
        "http_code" => $httpCode
    ]);
}
