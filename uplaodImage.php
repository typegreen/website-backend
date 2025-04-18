<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");
header("Content-Type: application/json");

$bucket = "rice-crop-images";
$file = $_FILES['file'] ?? null;

if (!$file || $file['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "File upload failed"]);
    exit;
}

$tempPath = $file["tmp_name"];
$fileName = basename($file["name"]);
$uniqueFileName = uniqid() . "_" . $fileName;

// ✅ Get Supabase credentials from environment variables
$apiKey = getenv("SUPABASE_API_KEY");
$baseUrl = getenv("SUPABASE_BASE_URL");

if (!$apiKey || !$baseUrl) {
    echo json_encode(["error" => "Missing Supabase environment variables"]);
    exit;
}

// ✅ Get MIME type of the file
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$contentType = finfo_file($finfo, $tempPath);
finfo_close($finfo);

// ✅ Prepare upload to Supabase
$uploadUrl = "$baseUrl/storage/v1/object/$bucket/$uniqueFileName";
$data = file_get_contents($tempPath);

$headers = [
    "apikey: $apiKey",
    "Authorization: Bearer $apiKey",
    "Content-Type: $contentType"
];

$ch = curl_init($uploadUrl);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$response = curl_exec($ch);

if (curl_errno($ch)) {
    echo json_encode(["error" => curl_error($ch)]);
    curl_close($ch);
    exit;
}

$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

if ($httpCode >= 200 && $httpCode < 300) {
    $publicUrl = "$baseUrl/storage/v1/object/public/$bucket/$uniqueFileName";
    echo json_encode(["url" => $publicUrl]);
} else {
    echo json_encode(["error" => "Supabase upload failed. Status code: $httpCode"]);
}
?>
