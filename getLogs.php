<?php
require_once __DIR__ . '/authUtils.php';
require_once __DIR__ . '/db.php';
$conn = getDBConnection();

$authHeader = getAuthorizationHeader();
$userId = null;

if (!empty($authHeader)) {
    if (preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) {
        $userId = $matches[1];
    } else {
        $userId = $authHeader;
    }
}

if (!$userId || !is_numeric($userId)) {
    http_response_code(401);
    die(json_encode([
        "error" => "Unauthorized",
        "message" => "Valid authorization token required"
    ]));
}

$sql = "SELECT 
          IMAGE_CODE as imageCode,
          TO_CHAR(DATE_OF_DETECTION, 'YYYY-MM-DD') as date,
          CASE
            WHEN TIME_OF_DETECTION IS NULL THEN '00:00:00'
            ELSE TO_CHAR(TIME_OF_DETECTION, 'HH24:MI:SS')
          END as rawTime,
          LOCATION as location,
          CLASSIFICATION as classification,
          RICE_CROP_IMAGE as img
        FROM DETECTION_LOGS
        WHERE USER_ID = ?
        ORDER BY LOG_ID ASC";

$stmt = $conn->prepare($sql);
$stmt->execute([$userId]);
$results = [];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $timeDisplay = 'N/A';
    if (!empty($row['rawTime'])) {
        $timeParts = explode(':', $row['rawTime']);
        if (count($timeParts) >= 2) {
            $hours = (int)$timeParts[0];
            $minutes = $timeParts[1];
            $period = $hours >= 12 ? 'PM' : 'AM';
            $displayHours = $hours % 12;
            $displayHours = $displayHours ? $displayHours : 12;
            $timeDisplay = sprintf("%02d:%02d %s", $displayHours, $minutes, $period);
        }
    }

    $results[] = [
        'imageCode' => htmlspecialchars($row['imageCode']),
        'date' => htmlspecialchars($row['date']),
        'time' => htmlspecialchars($timeDisplay),
        'location' => htmlspecialchars($row['location']),
        'classification' => htmlspecialchars($row['classification']),
        'img' => 'webapp/Thesis/website-backend/assets/' . 
                htmlspecialchars(basename($row['img']))
    ];
}

http_response_code(200);
echo json_encode([
    "success" => true,
    "data" => $results,
    "count" => count($results)
], JSON_UNESCAPED_SLASHES);
?>