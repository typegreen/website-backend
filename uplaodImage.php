<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$bucket = "rice-crop-images";
$file = $_FILES['file'];

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "File upload failed"]);
    exit;
}

$tempPath = $file["tmp_name"];
$fileName = basename($file["name"]);
$uniqueFileName = uniqid() . "_" . $fileName;

// ✅ Get Supabase values from environment
$apiKey = getenv("SUPABASE_PUBLIC_API_KEY");
$baseUrl = getenv("SUPABASE_BASE_URL");

if (!$apiKey || !$baseUrl) {
    echo json_encode(["error" => "Missing Supabase environment variables"]);
    exit;
}

$uploadUrl = "$baseUrl/storage/v1/object/$bucket/$uniqueFileName";
$data = file_get_contents($tempPath);

$headers = [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Content-Type: application/octet-stream"
];

$ch = curl_init($uploadUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

// ✅ Check result of cURL
if (!$response || curl_errno($ch)) {
    echo json_encode([
        "error" => "Supabase upload failed",
        "details" => curl_error($ch),
        "response" => $response
    ]);
    curl_close($ch);
    exit;
}

curl_close($ch);

// ✅ Return full public URL
$publicUrl = "$baseUrl/storage/v1/object/public/$bucket/$uniqueFileName";
echo json_encode(["url" => $publicUrl]);
?>
