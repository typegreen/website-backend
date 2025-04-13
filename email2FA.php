<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization, apikey");

function respond($status, $data) {
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

$apiKey = getenv("SUPABASE_API_KEY");
$brevoApiKey = getenv("BREVO_API_KEY"); // Set this in Railway or your .env

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data["email"]) || !isset($data["user_id"])) {
    respond(400, "Missing email or user_id.");
    exit;
}

$email = $data["email"];
$user_id = $data["user_id"];
$code = rand(100000, 999999);

// Step 1: Store the code in Supabase
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

// Step 2: Send email using Brevo SMTP API
$payload = [
    "sender" => [
        "name" => "AniMonitor",
        "email" => "nlplalvarez@gmail.com" // Brevo sender (must be verified)
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

/*
$response = curl_exec($send);
$httpCode = curl_getinfo($send, CURLINFO_HTTP_CODE);
curl_close($send);

// Step 3: Respond based on email delivery status
if ($httpCode === 201) {
    respond(200, "2FA code sent to $email");
} else {
    respond(500, [
        "error" => "Failed to send 2FA code.",
        "brevo_response" => $response,
        "http_code" => $httpCode
    ]);
}
    */

    $response = curl_exec($send);
    $httpCode = curl_getinfo($send, CURLINFO_HTTP_CODE);
    curl_close($send);
    
    // TEMP: Always return Brevo debug response
    respond($httpCode, [
        "raw_response" => $response,
        "http_code" => $httpCode,
        "email_sent_to" => $email
    ]);
    
