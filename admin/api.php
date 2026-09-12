<?php

session_start();

if (!isset($_SESSION["admin_id"])) {
    http_response_code(403);

    echo json_encode([
        "success" => false,
        "message" => "Unauthorized"
    ]);

    exit;
}

require_once __DIR__ . "/../api/config.php";

header("Content-Type: application/json");

$action = $_GET["action"] ?? $_POST["action"] ?? "";

if ($action === "report_count") {
    $stmt = $pdo->query("SELECT COUNT(*) FROM reports WHERE status = 'Pending'");
    $pending = (int)$stmt->fetchColumn();
    echo json_encode(["ok" => true, "pending" => $pending]);
    exit;
}

if ($action === "list") {

    $type = $_GET["type"] ?? "";

    $tables = [
        "contacts" => "contacts",
        "hospitals" => "hospitals",
        "alerts" => "alerts",
        "evacuation" => "evacuation_centers",
        "firstaid" => "first_aid",
        "reports" => "reports"
    ];

    if (!isset($tables[$type])) {
        echo json_encode([]);
        exit;
    }

    $table = $tables[$type];

    $stmt = $pdo->query(
        "SELECT * FROM `$table` ORDER BY id DESC"
    );

    echo json_encode(
        $stmt->fetchAll()
    );

    exit;
}


if ($action === "save") {

    $type = $_POST["type"] ?? "";
    $id = (int)($_POST["id"] ?? 0);

    if ($type === "contacts") {

        $category = trim($_POST["category"] ?? "");
        $name = trim($_POST["name"] ?? "");
        $position = trim($_POST["position"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $location = trim($_POST["location"] ?? "");

        if ($id) {

            $stmt = $pdo->prepare(
                "UPDATE contacts
                 SET category=?,name=?,position=?,phone=?,location=?
                 WHERE id=?"
            );

            $stmt->execute([
                $category,
                $name,
                $position,
                $phone,
                $location,
                $id
            ]);

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO contacts
                (category,name,position,phone,location)
                VALUES (?,?,?,?,?)"
            );

            $stmt->execute([
                $category,
                $name,
                $position,
                $phone,
                $location
            ]);
        }
    }


    elseif ($type === "hospitals") {

        $name = trim($_POST["name"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $phone = trim($_POST["phone"] ?? "");
        $distance = trim($_POST["distance"] ?? "");
        $status = trim($_POST["status"] ?? "Open");

        if ($id) {

            $stmt = $pdo->prepare(
                "UPDATE hospitals
                 SET name=?,location=?,phone=?,distance=?,status=?
                 WHERE id=?"
            );

            $stmt->execute([
                $name,
                $location,
                $phone,
                $distance,
                $status,
                $id
            ]);

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO hospitals
                (name,location,phone,distance,status)
                VALUES (?,?,?,?,?)"
            );

            $stmt->execute([
                $name,
                $location,
                $phone,
                $distance,
                $status
            ]);
        }
    }


    elseif ($type === "alerts") {

        $title = trim($_POST["title"] ?? "");
        $message = trim($_POST["message"] ?? "");
        $alertType = trim($_POST["alert_type"] ?? "info");
        $alertDate = trim($_POST["alert_date"] ?? "");

        if ($id) {

            $stmt = $pdo->prepare(
                "UPDATE alerts
                 SET title=?,message=?,type=?,alert_date=?
                 WHERE id=?"
            );

            $stmt->execute([
                $title,
                $message,
                $alertType,
                $alertDate,
                $id
            ]);

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO alerts
                (title,message,type,alert_date)
                VALUES (?,?,?,?)"
            );

            $stmt->execute([
                $title,
                $message,
                $alertType,
                $alertDate
            ]);
        }
    }


    elseif ($type === "evacuation") {

        $name = trim($_POST["name"] ?? "");
        $location = trim($_POST["location"] ?? "");
        $capacity = (int)($_POST["capacity"] ?? 0);
        $status = trim($_POST["status"] ?? "Open");

        if ($id) {

            $stmt = $pdo->prepare(
                "UPDATE evacuation_centers
                 SET name=?,location=?,capacity=?,status=?
                 WHERE id=?"
            );

            $stmt->execute([
                $name,
                $location,
                $capacity,
                $status,
                $id
            ]);

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO evacuation_centers
                (name,location,capacity,status)
                VALUES (?,?,?,?)"
            );

            $stmt->execute([
                $name,
                $location,
                $capacity,
                $status
            ]);
        }
    }


    elseif ($type === "firstaid") {

        $title = trim($_POST["title"] ?? "");
        $description = trim($_POST["description"] ?? "");

        if ($id) {

            $stmt = $pdo->prepare(
                "UPDATE first_aid
                 SET title=?,description=?
                 WHERE id=?"
            );

            $stmt->execute([
                $title,
                $description,
                $id
            ]);

        } else {

            $stmt = $pdo->prepare(
                "INSERT INTO first_aid
                (title,description)
                VALUES (?,?)"
            );

            $stmt->execute([
                $title,
                $description
            ]);
        }
    }


    elseif ($type === "report_status") {

        $status = trim($_POST["status"] ?? "Pending");

        $stmt = $pdo->prepare(
            "UPDATE reports
             SET status=?
             WHERE id=?"
        );

        $stmt->execute([
            $status,
            $id
        ]);
    }


    echo json_encode([
        "success" => true
    ]);

    exit;
}


if ($action === "delete") {

    $type = $_POST["type"] ?? "";
    $id = (int)($_POST["id"] ?? 0);

    $tables = [
        "contacts" => "contacts",
        "hospitals" => "hospitals",
        "alerts" => "alerts",
        "evacuation" => "evacuation_centers",
        "firstaid" => "first_aid",
        "reports" => "reports"
    ];

    if (!isset($tables[$type])) {
        echo json_encode([
            "success" => false
        ]);
        exit;
    }

    $table = $tables[$type];

    $stmt = $pdo->prepare(
        "DELETE FROM `$table` WHERE id=?"
    );

    $stmt->execute([$id]);

    echo json_encode([
        "success" => true
    ]);

    exit;
}


echo json_encode([
    "success" => false,
    "message" => "Unknown action"
]);