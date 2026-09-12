<?php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/location_service.php';

header("Content-Type: application/json; charset=utf-8");

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    echo json_encode(["success" => false, "message" => "Invalid request."]);
    exit;
}

$category = trim($_POST["category"] ?? "");
$name = trim($_POST["name"] ?? "");
$contact = trim($_POST["contact"] ?? ($_POST["phone"] ?? ""));
$location = trim($_POST["location"] ?? "");
$description = trim($_POST["description"] ?? "");
$latitude = $_POST["latitude"] ?? null;
$longitude = $_POST["longitude"] ?? null;
$latitude = is_numeric($latitude) && $latitude >= -90 && $latitude <= 90 ? (float)$latitude : null;
$longitude = is_numeric($longitude) && $longitude >= -180 && $longitude <= 180 ? (float)$longitude : null;

if ($category === "" || $contact === "" || $description === "") {
    echo json_encode(["success" => false, "message" => "Please complete the required fields."]);
    exit;
}

// If GPS was supplied, verify the coordinates against the official
// barangay boundary before saving the report.
if ($latitude !== null && $longitude !== null) {
    $resolved = sk_resolve_location($latitude, $longitude);
    if (empty($resolved['success']) || empty($resolved['inside'])) {
        echo json_encode([
            "success" => false,
            "message" => $resolved['message'] ?? "The GPS location could not be verified inside Santa Cruz, Ilocos Sur."
        ]);
        exit;
    }
    $location = trim((string)($resolved['address'] ?? $location));
}

try {
    $stmt = $pdo->prepare(
        "INSERT INTO reports (category, name, contact, location, latitude, longitude, description)
         VALUES (?, ?, ?, ?, ?, ?, ?)"
    );
    $stmt->execute([$category, $name, $contact, $location, $latitude, $longitude, $description]);

    echo json_encode([
        "success" => true,
        "message" => "Emergency report submitted successfully.",
        "report_id" => (int)$pdo->lastInsertId()
    ]);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "message" => "Unable to save the emergency report."
    ]);
}
