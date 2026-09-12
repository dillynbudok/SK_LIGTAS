<?php

require_once __DIR__ . '/location_service.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

$lat = filter_input(
    INPUT_GET,
    'lat',
    FILTER_VALIDATE_FLOAT
);

$lon = filter_input(
    INPUT_GET,
    'lon',
    FILTER_VALIDATE_FLOAT
);

if (
    $lat === false ||
    $lat === null ||
    $lon === false ||
    $lon === null ||
    $lat < -90 ||
    $lat > 90 ||
    $lon < -180 ||
    $lon > 180
) {

    http_response_code(400);

    echo json_encode([
        'success' => false,
        'inside_service_area' => false,
        'message' => 'Invalid GPS coordinates.'
    ], JSON_UNESCAPED_UNICODE);

    exit;
}

try {

    $result = sk_resolve_location(
        $lat,
        $lon
    );

    if (!is_array($result)) {

        throw new Exception(
            'Invalid location result.'
        );
    }

    $result['inside_service_area'] =
        !empty($result['inside']);

    echo json_encode(
        $result,
        JSON_UNESCAPED_UNICODE |
        JSON_UNESCAPED_SLASHES
    );

} catch (Throwable $e) {

    http_response_code(500);

    echo json_encode([
        'success' => false,
        'inside_service_area' => false,
        'message' =>
            'Unable to verify the GPS location right now.'
    ], JSON_UNESCAPED_UNICODE);
}