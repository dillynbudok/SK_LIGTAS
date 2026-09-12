<?php

require_once __DIR__ . "/config.php";

function getContacts($category = null)
{
    global $pdo;

    if ($category) {
        $stmt = $pdo->prepare(
            "SELECT * FROM contacts WHERE category = ? ORDER BY id DESC"
        );
        $stmt->execute([$category]);
    } else {
        $stmt = $pdo->query(
            "SELECT * FROM contacts ORDER BY id DESC"
        );
    }

    return $stmt->fetchAll();
}

function getHospitals()
{
    global $pdo;

    $stmt = $pdo->query(
        "SELECT * FROM hospitals ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function getAlerts()
{
    global $pdo;

    $stmt = $pdo->query(
        "SELECT * FROM alerts ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function getEvacuationCenters()
{
    global $pdo;

    $stmt = $pdo->query(
        "SELECT * FROM evacuation_centers ORDER BY id DESC"
    );

    return $stmt->fetchAll();
}

function getFirstAid()
{
    global $pdo;

    $stmt = $pdo->query(
        "SELECT * FROM first_aid ORDER BY id ASC"
    );

    return $stmt->fetchAll();
}

function getSettings()
{
    global $pdo;

    $stmt = $pdo->query(
        "SELECT * FROM settings WHERE id = 1 LIMIT 1"
    );

    return $stmt->fetch();
}

// When requested directly by the public website, return all public data as JSON.
if (basename($_SERVER['SCRIPT_FILENAME'] ?? '') === 'data.php') {
    header('Content-Type: application/json; charset=utf-8');
    try {
        echo json_encode([
            'contacts' => getContacts(),
            'hospitals' => getHospitals(),
            'alerts' => getAlerts(),
            'evacuation' => getEvacuationCenters(),
            'first_aid' => getFirstAid(),
            'settings' => getSettings()
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    } catch (Throwable $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Unable to load system data.']);
    }
}
