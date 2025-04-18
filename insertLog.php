header("Content-Type: application/json");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Headers: Content-Type");

$data = json_decode(file_get_contents("php://input"), true);

// Validate required fields
$required = ["location", "date", "time", "image_code", "rice_crop_image", "classification", "user_id"];
foreach ($required as $key) {
    if (!isset($data[$key])) {
        echo json_encode(["error" => "Missing field: $key"]);
        exit;
    }
}

// 🔧 Replace with your real credentials
$host = getenv("DB_HOST");
$user = getenv("DB_USER");
$pass = getenv("DB_PASS");
$db = getenv("DB_NAME");

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    echo json_encode(["error" => "Connection failed: " . $conn->connect_error]);
    exit;
}

$stmt = $conn->prepare("
    INSERT INTO detection_logs 
    (location, date_of_detection, time_of_detection, image_code, rice_crop_image, classification, user_id) 
    VALUES (?, ?, ?, ?, ?, ?, ?)
");

if (!$stmt) {
    echo json_encode(["error" => "Prepare failed: " . $conn->error]);
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

if ($stmt->execute()) {
    echo json_encode(["success" => true]);
} else {
    echo json_encode(["error" => "Execute failed: " . $stmt->error]);
}

$stmt->close();
$conn->close();
