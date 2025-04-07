<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

$apiUrl = "https://oyicdamiuhqlwqckxjpe.supabase.co/rest/v1/ACCOUNTS"; // Change if table name differs
$apiKey = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6Im95aWNkYW1pdWhxbHdxY2t4anBlIiwicm9sZSI6ImFub24iLCJpYXQiOjE3NDM0NDAzMzEsImV4cCI6MjA1OTAxNjMzMX0.tR2W8pggj_yx53UxgHk828u33CqhmzRRzA2xvaRV9g8"; // Replace this with your anon/public API key from Supabase dashboard

$ch = curl_init();

curl_setopt($ch, CURLOPT_URL, $apiUrl);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey"
]);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

// Output result
header("Content-Type: application/json");
echo json_encode([
    "status" => $httpCode,
    "response" => json_decode($response, true)
]);
?>
