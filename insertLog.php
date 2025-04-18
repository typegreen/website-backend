<?php
header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);

$required = ["location", "date", "time", "image_code", "rice_crop_image", "classification", "user_id"];
foreach ($required as $key) {
    if (!isset($data[$key])) {
        echo json_encode(["error" => "Missing field: $key"]);
        exit;
    }
}

// 🔒 TODO: Replace with your actual MySQL credentials
$conn = new mysqli("your_host", "your_user", "your_password", "your_database");

// ✅ Connection error logging
if ($conn->connect_error) {
    echo json_encode(["error" => "Database connection failed", "details" => $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("INSERT INTO detection_logs (location, date_of_detection, time_of_detection, image_code, rice_crop_image, classification, user_id) VALUES (?, ?, ?, ?, ?, ?, ?)");

if (!$stmt) {
    echo json_encode(["error" => "Prepare failed", "details" => $conn->error]);
    $conn->close();
    exit;
}

$stmt->bind_param(
    "ssssssi",
    $data["location"],
    $data["date"],
    $data["time"],
    $data["image_code"],
    $data["rice_crop_image"],
    $data["classification"],
    $data["user_id"]
);

// ✅ Log execution result
if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Execute failed", "details" => $stmt->error]);
}

$stmt->close();
$conn->close();
?>
