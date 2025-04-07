<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST, GET, OPTIONS, PUT, DELETE");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

function respond($status, $data) {
    header("Content-Type: application/json");
    echo json_encode(["status" => $status, "response" => $data]);
}

$data = json_decode(file_get_contents("php://input"), true);
$user_name = $data['user_name'];
$password = $data['password'];
$apiKey = getenv("SUPABASE_API_KEY");

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
if (count($users) === 1 && $users[0]['password'] === $password) {
    respond(200, array("login" => "success", "user" => $users[0]));
} else {
    respond(401, array("login" => "failed"));
}
?>