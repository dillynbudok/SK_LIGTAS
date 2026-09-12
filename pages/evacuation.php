<?php
require_once __DIR__ . '/../api/config.php';

$items = [];
$db_error = '';

try {
    $stmt = $pdo->query("SELECT * FROM evacuation_centers ORDER BY id ASC");
    $items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    $db_error = $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Evacuation Centers | SK LIGTAS</title>
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        .evac-page {
            width: min(950px, 92%);
            margin: 30px auto 60px;
        }

        .evac-header {
            margin-bottom: 25px;
        }

        .evac-header h1 {
            margin: 0;
            color: #152238;
            font-size: 30px;
            font-weight: 800;
        }

        .evac-header p {
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

        .empty-message {
            background: #ffffff;
            border: 1px solid #e3e8ef;
            border-radius: 16px;
            padding: 30px;
            text-align: center;
            color: #6f7b8d;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.05);
        }

        .evac-list {
            background: #ffffff;
            border: 1px solid #e2e7ee;
            border-radius: 18px;
            overflow: hidden;
            box-shadow: 0 6px 22px rgba(24, 56, 92, 0.06);
        }

        .evac-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 20px;
            border-bottom: 1px solid #edf0f4;
        }

        .evac-row:last-child {
            border-bottom: none;
        }

        .evac-main {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            min-width: 0;
        }

        .evac-icon {
            width: 50px;
            height: 50px;
            flex: 0 0 50px;
            border-radius: 14px;
            background: #eaf2ff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 25px;
        }

        .evac-info {
            min-width: 0;
        }

        .evac-info h2 {
            margin: 0;
            color: #152238;
            font-size: 16px;
            font-weight: 800;
        }

        .evac-location {
            margin-top: 7px;
            color: #687589;
            font-size: 12px;
            line-height: 1.5;
        }

        .evac-capacity {
            margin-top: 5px;
            color: #687589;
            font-size: 12px;
        }

        .evac-status {
            flex: 0 0 auto;
            padding: 6px 11px;
            border-radius: 20px;
            background: #dcf8e6;
            color: #18743c;
            font-size: 10px;
            font-weight: 800;
            text-transform: capitalize;
        }

        .evac-status.closed {
            background: #ffe5e8;
            color: #b91e2d;
        }

        .evac-status.full {
            background: #fff0d5;
            color: #a76700;
        }

        .evac-status.unavailable {
            background: #ffe5e8;
            color: #b91e2d;
        }

        @media (max-width: 700px) {
            .evac-page {
                width: calc(100% - 20px);
                margin: 20px auto 40px;
            }

            .evac-header h1 {
                font-size: 25px;
            }

            .evac-row {
                align-items: flex-start;
                padding: 16px;
                gap: 12px;
            }

            .evac-main {
                gap: 10px;
            }

            .evac-icon {
                width: 42px;
                height: 42px;
                flex-basis: 42px;
                font-size: 20px;
            }

            .evac-info h2 {
                font-size: 14px;
            }

            .evac-location,
            .evac-capacity {
                font-size: 11px;
            }

            .evac-status {
                font-size: 9px;
                padding: 5px 8px;
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
            <span>Evacuation Centers</span>
        </div>
    </div>
</header>

<main>
    <section class="evac-page">

        <div class="evac-header">
            <h1>Evacuation Centers</h1>
            <p>Safe locations during emergencies and disasters</p>
        </div>

        <?php if ($db_error !== ''): ?>

            <div class="database-error">
                <strong>Unable to load evacuation centers.</strong>
                <br>
                <small><?= htmlspecialchars($db_error) ?></small>
            </div>

        <?php elseif (empty($items)): ?>

            <div class="empty-message">
                🏠
                <br><br>
                No evacuation centers are currently available.
            </div>

        <?php else: ?>

            <div class="evac-list">

                <?php foreach ($items as $center): ?>

                    <?php
                    $name = $center['name']
                        ?? $center['center_name']
                        ?? $center['evacuation_center']
                        ?? 'Unnamed Evacuation Center';

                    $location = $center['location']
                        ?? $center['address']
                        ?? $center['area']
                        ?? 'Location unavailable';

                    $capacity = $center['capacity']
                        ?? $center['max_capacity']
                        ?? 0;

                    $status = $center['status']
                        ?? $center['availability']
                        ?? 'Open';

                    $statusClass = strtolower(trim((string)$status));

                    if ($statusClass === '') {
                        $statusClass = 'open';
                    }
                    ?>

                    <article class="evac-row">

                        <div class="evac-main">

                            <div class="evac-icon">
                                🏠
                            </div>

                            <div class="evac-info">

                                <h2>
                                    <?= htmlspecialchars($name) ?>
                                </h2>

                                <div class="evac-location">
                                    📍 <?= htmlspecialchars($location) ?>
                                </div>

                                <div class="evac-capacity">
                                    👥 Capacity: <?= number_format((int)$capacity) ?>
                                </div>

                            </div>

                        </div>

                        <span class="evac-status <?= htmlspecialchars($statusClass) ?>">
                            <?= htmlspecialchars($status) ?>
                        </span>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>
</main>

</body>
</html> 