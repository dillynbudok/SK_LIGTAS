<?php
require_once __DIR__ . '/../api/config.php';

$items = [];
$db_error = '';

try {
    $columnsStmt = $pdo->query("SHOW COLUMNS FROM hospitals");
    $columns = $columnsStmt->fetchAll(PDO::FETCH_COLUMN);

    if (empty($columns)) {
        throw new Exception("The hospitals table does not exist.");
    }

    $nameColumn = in_array('name', $columns)
        ? 'name'
        : (in_array('hospital_name', $columns) ? 'hospital_name' : null);

    $locationColumn = in_array('location', $columns)
        ? 'location'
        : (in_array('address', $columns) ? 'address' : null);

    $phoneColumn = in_array('phone', $columns)
        ? 'phone'
        : (in_array('contact', $columns)
            ? 'contact'
            : (in_array('telephone', $columns) ? 'telephone' : null));

    $distanceColumn = in_array('distance', $columns)
        ? 'distance'
        : (in_array('distance_km', $columns) ? 'distance_km' : null);

    $statusColumn = in_array('status', $columns)
        ? 'status'
        : (in_array('availability', $columns) ? 'availability' : null);

    if (!$nameColumn) {
        throw new Exception("The hospitals table does not have a name column.");
    }

    $select = [
        "id",
        "`$nameColumn` AS hospital_name"
    ];

    if ($locationColumn) {
        $select[] = "`$locationColumn` AS hospital_location";
    } else {
        $select[] = "'' AS hospital_location";
    }

    if ($phoneColumn) {
        $select[] = "`$phoneColumn` AS hospital_phone";
    } else {
        $select[] = "'' AS hospital_phone";
    }

    if ($distanceColumn) {
        $select[] = "`$distanceColumn` AS hospital_distance";
    } else {
        $select[] = "'' AS hospital_distance";
    }

    if ($statusColumn) {
        $select[] = "`$statusColumn` AS hospital_status";
    } else {
        $select[] = "'Open' AS hospital_status";
    }

    $sql = "SELECT " . implode(", ", $select) . " FROM hospitals ORDER BY id ASC";

    $stmt = $pdo->query($sql);

    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {
    $db_error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Nearest Hospitals | SK LIGTAS</title>

    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .hospital-page {
            width: min(950px, 92%);
            margin: 30px auto 60px;
        }

        .hospital-header {
            margin-bottom: 25px;
        }

        .hospital-header h1 {
            margin: 0;
            color: #152238;
            font-size: 30px;
            font-weight: 800;
        }

        .hospital-header p {
            margin: 7px 0 0;
            color: #7b8493;
            font-size: 13px;
        }

        .database-error {
            background: #fff0f1;
            border: 1px solid #ffc9ce;
            color: #b51d2a;
            border-radius: 14px;
            padding: 16px;
            margin-bottom: 20px;
            font-size: 13px;
        }

        .database-error small {
            display: block;
            margin-top: 8px;
            word-break: break-word;
        }

        .empty-message {
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            color: #6f7b8d;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .hospital-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 18px;
        }

        .hospital-card {
            background: #ffffff;
            border: 1px solid #e2e7ee;
            border-radius: 18px;
            padding: 20px;
            box-shadow: 0 6px 22px rgba(24, 56, 92, 0.06);
            min-height: 230px;
            display: flex;
            flex-direction: column;
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .hospital-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 28px rgba(24, 56, 92, 0.10);
        }

        .hospital-icon {
            width: 50px;
            height: 50px;
            border-radius: 14px;
            background: #eaf2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
            margin-bottom: 14px;
        }

        .hospital-card h2 {
            margin: 0;
            color: #152238;
            font-size: 17px;
            font-weight: 800;
        }

        .hospital-info {
            margin-top: 10px;
            display: flex;
            flex-direction: column;
            gap: 6px;
        }

        .hospital-location,
        .hospital-phone,
        .hospital-distance {
            color: #687589;
            font-size: 12px;
            line-height: 1.5;
        }

        .hospital-distance {
            color: #0754c7;
            font-weight: 700;
        }

        .hospital-status {
            display: inline-block;
            width: fit-content;
            margin-top: 10px;
            padding: 5px 10px;
            border-radius: 20px;
            background: #dcf8e6;
            color: #18743c;
            font-size: 10px;
            font-weight: 800;
        }

        .hospital-call {
            margin-top: auto;
            padding-top: 17px;
        }

        .hospital-call a {
            display: block;
            text-align: center;
            text-decoration: none;
            background: #0754c7;
            color: white;
            padding: 11px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: 800;
            transition: background 0.2s ease;
        }

        .hospital-call a:hover {
            background: #0646a7;
        }

        @media (max-width: 700px) {
            .hospital-page {
                width: calc(100% - 20px);
                margin-top: 20px;
            }

            .hospital-header h1 {
                font-size: 25px;
            }

            .hospital-grid {
                grid-template-columns: 1fr;
            }

            .hospital-card {
                min-height: 210px;
            }
        }
    
/* SK LIGTAS UNIFIED PAGE THEME */
:root{--sk-navy:#062b69;--sk-blue:#0754c7;--sk-red:#ed1c24;--sk-yellow:#f4c21f;--sk-bg:#f4f7fb;--sk-border:#dfe7f1}
body{background:var(--sk-bg)!important;color:#142b49}
.header{background:linear-gradient(110deg,var(--sk-navy) 0 70%,#0b4fae 70% 100%)!important;color:#fff!important;border-bottom:4px solid var(--sk-yellow)!important;box-shadow:0 5px 18px rgba(6,43,105,.12)}
.header .brand strong,.header .brand span{color:#fff!important}
.header .back-link{color:#fff!important;width:38px;height:38px;border:1px solid rgba(255,255,255,.35);border-radius:10px;display:grid;place-items:center;margin-right:4px;background:rgba(255,255,255,.08)}
.header .shield{background:#fff!important;color:var(--sk-red)!important;border-radius:10px!important}
.page-shell,.hospital-page,.evac-page{margin-top:30px!important}
.section-title,.hospital-header,.evac-header{background:#fff;border-left:6px solid var(--sk-red);border-radius:16px;padding:20px 22px;box-shadow:0 5px 20px rgba(24,56,92,.06);margin-bottom:18px!important}
.section-title h1,.hospital-header h1,.evac-header h1{color:var(--sk-navy)!important}
.section-title p,.hospital-header p,.evac-header p{color:#718096!important}
.tiles{gap:16px!important}
.tile,.hospital-card,.evac-list,.empty-message,.empty{border-color:var(--sk-border)!important;box-shadow:0 6px 22px rgba(24,56,92,.07)!important}
.tile{border-top:4px solid var(--sk-blue)!important}
.tile-icon,.hospital-icon,.evac-icon{background:#eaf2ff!important;color:var(--sk-blue)!important}
.tile b,.hospital-card h2,.evac-info h2{color:var(--sk-navy)!important}
.tile>a,.hospital-call a{background:var(--sk-blue)!important}
.tile>a:hover,.hospital-call a:hover{background:#0646a7!important}
.hospital-status,.evac-status{background:#eaf2ff!important;color:var(--sk-blue)!important;border:1px solid #cfe0f7}

/* Keep alert severity colors while matching the same card language */
.alert-card{border-left:6px solid var(--sk-blue)!important;background:#fff!important;border-top:4px solid var(--sk-blue);box-shadow:0 6px 22px rgba(24,56,92,.07)!important}
.alert-card.info{border-left-color:var(--sk-blue)!important;border-top-color:var(--sk-blue)!important}
.alert-card.warning{border-left-color:var(--sk-yellow)!important;border-top-color:var(--sk-yellow)!important}
.alert-card.danger{border-left-color:var(--sk-red)!important;border-top-color:var(--sk-red)!important}

/* Hotlines use the exact same shell/header/card palette */
.hotline-page{margin-top:22px!important}
.hotline-card{border-color:var(--sk-border)!important;box-shadow:0 6px 22px rgba(24,56,92,.07)!important}
.hotline-title-icon{background:var(--sk-blue)!important;box-shadow:0 4px 10px rgba(7,84,199,.2)!important}
.hotline-title h1{color:var(--sk-navy)!important}
.hotline-item{border-color:var(--sk-border)!important;border-top:3px solid var(--sk-blue);box-shadow:0 4px 15px rgba(24,56,92,.06)!important}
.hotline-item:nth-child(odd){border-top-color:var(--sk-red)}
.hotline-call,.hotline-item:nth-child(odd) .hotline-call{background:var(--sk-blue)!important}
.pdf-divider{color:var(--sk-navy)!important}
.pdf-divider:before{background:var(--sk-red)!important}
.council-box{border-color:var(--sk-border)!important;background:#fff!important}
.council-box strong{color:var(--sk-navy)!important}

</style>
</head>

<body>

<header class="header">
    <div class="brand">
        <a href="../index.php" class="back-link" aria-label="Back to home">←</a>

        <div class="shield"><img src="../asset/sk-logo.png" alt="SK LIGTAS Logo"></div>

        <div>
            <strong>SK LIGTAS</strong>
            <span>Nearest Hospitals</span>
        </div>
    </div>
</header>

<main>
    <section class="hospital-page">

        <div class="hospital-header">
            <h1>Nearest Hospitals</h1>
            <p>Medical assistance and nearby healthcare facilities</p>
        </div>

        <?php if ($db_error !== ''): ?>

            <div class="database-error">
                <strong>Unable to load hospitals.</strong>
                <small><?= htmlspecialchars($db_error) ?></small>
            </div>

        <?php elseif (empty($items)): ?>

            <div class="empty-message">
                🏥
                <br><br>
                No hospitals are currently available.
            </div>

        <?php else: ?>

            <div class="hospital-grid">

                <?php foreach ($items as $hospital): ?>

                    <?php
                    $name = $hospital['hospital_name'] ?? 'Unnamed Hospital';
                    $location = $hospital['hospital_location'] ?? 'Location unavailable';
                    $phone = $hospital['hospital_phone'] ?? '';
                    $distance = $hospital['hospital_distance'] ?? '';
                    $status = $hospital['hospital_status'] ?? 'Open';
                    ?>

                    <article class="hospital-card">

                        <div class="hospital-icon">
                            🏥
                        </div>

                        <h2>
                            <?= htmlspecialchars($name) ?>
                        </h2>

                        <div class="hospital-info">

                            <div class="hospital-location">
                                📍 <?= htmlspecialchars($location) ?>
                            </div>

                            <?php if ($phone !== ''): ?>
                                <div class="hospital-phone">
                                    ☎ <?= htmlspecialchars($phone) ?>
                                </div>
                            <?php endif; ?>

                            <?php if ($distance !== ''): ?>
                                <div class="hospital-distance">
                                    📏 <?= htmlspecialchars($distance) ?>
                                </div>
                            <?php endif; ?>

                        </div>

                        <span class="hospital-status">
                            <?= htmlspecialchars($status) ?>
                        </span>

                        <?php if ($phone !== ''): ?>

                            <div class="hospital-call">
                                <a href="tel:<?= htmlspecialchars($phone) ?>">
                                    ☎ Call Hospital
                                </a>
                            </div>

                        <?php endif; ?>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>
</main>

</body>
</html>