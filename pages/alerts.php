<?php
require_once "../api/config.php";

$result = $conn->query("SELECT * FROM alerts ORDER BY id DESC");

if (!$result) {
    die("Error loading alerts: " . $conn->error);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Disaster Alerts - SK LIGTAS</title>
    <link rel="stylesheet" href="../assets/style.css">

    <style>
        body {
            margin: 0;
            font-family: Arial, sans-serif;
            background: #f4f7fb;
            color: #222;
        }

        .page {
            width: 100%;
            min-height: 100vh;
        }

        .header {
            background: #c9141b;
            color: white;
            padding: 18px 5%;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .header h1 {
            margin: 0;
            font-size: clamp(22px, 4vw, 32px);
        }

        .back {
            color: white;
            text-decoration: none;
            font-weight: bold;
            white-space: nowrap;
        }

        .content {
            width: min(1100px, 92%);
            margin: 35px auto;
        }

        .intro {
            margin-bottom: 25px;
        }

        .intro h2 {
            margin: 0 0 8px;
            font-size: clamp(25px, 4vw, 36px);
        }

        .intro p {
            margin: 0;
            color: #666;
        }

        .alerts {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }

        .alert-card {
            background: white;
            border-radius: 18px;
            padding: 22px;
            box-shadow: 0 6px 20px rgba(0,0,0,.08);
            border-left: 6px solid #c9141b;
        }

        .alert-card.warning {
            border-left-color: #f0a000;
        }

        .alert-card.info {
            border-left-color: #1769aa;
        }

        .alert-card.danger {
            border-left-color: #c9141b;
        }

        .alert-title {
            font-size: 21px;
            font-weight: 800;
            margin-bottom: 12px;
        }

        .alert-message {
            color: #555;
            line-height: 1.6;
            margin-bottom: 15px;
        }

        .alert-location {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
        }

        .alert-date {
            font-size: 13px;
            color: #999;
        }

        .empty {
            background: white;
            padding: 35px;
            text-align: center;
            border-radius: 18px;
            color: #777;
        }

        @media (max-width: 600px) {
            .header {
                padding: 16px 4%;
            }

            .content {
                width: 92%;
                margin: 25px auto;
            }

            .alert-card {
                padding: 18px;
            }
        }
    

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
.tiles{gap:16px!important}.tile{border-top:4px solid var(--sk-blue)!important;background:#fff!important;box-shadow:0 6px 22px rgba(24,56,92,.07)!important;border-color:var(--sk-border)!important}
.tile-icon,.hospital-icon,.evac-icon{background:#eaf2ff!important;color:var(--sk-blue)!important}.tile b,.hospital-card h2,.evac-info h2{color:var(--sk-navy)!important}
.tile>a,.hospital-call a{background:var(--sk-blue)!important}.tile>a:hover,.hospital-call a:hover{background:#0646a7!important}
.hospital-card,.evac-list,.empty-message{border-color:var(--sk-border)!important;box-shadow:0 6px 22px rgba(24,56,92,.07)!important}
.hospital-status,.evac-status{background:#eaf2ff!important;color:var(--sk-blue)!important;border:1px solid #cfe0f7}
.alert-card{border-left:6px solid var(--sk-blue)!important;background:#fff!important;border-top:4px solid var(--sk-blue);box-shadow:0 6px 22px rgba(24,56,92,.07)!important}.alert-card.info{border-left-color:var(--sk-blue)!important;border-top-color:var(--sk-blue)!important}.alert-card.warning{border-left-color:var(--sk-yellow)!important;border-top-color:var(--sk-yellow)!important}.alert-card.danger{border-left-color:var(--sk-red)!important;border-top-color:var(--sk-red)!important}
.hotline-page{margin-top:22px!important}.hotline-card{border-color:var(--sk-border)!important;box-shadow:0 6px 22px rgba(24,56,92,.07)!important}.hotline-title-icon{background:var(--sk-blue)!important}.hotline-title h1{color:var(--sk-navy)!important}.hotline-item{border-color:var(--sk-border)!important;border-top:3px solid var(--sk-blue);box-shadow:0 4px 15px rgba(24,56,92,.06)!important}.hotline-item:nth-child(odd){border-top-color:var(--sk-red)}.hotline-call,.hotline-item:nth-child(odd) .hotline-call{background:var(--sk-blue)!important}.pdf-divider{color:var(--sk-navy)!important}.pdf-divider:before{background:var(--sk-red)!important}.council-box{border-color:var(--sk-border)!important;background:#fff!important}.council-box strong{color:var(--sk-navy)!important}

</style>
    
</head>

<body>

<div class="page">

    <header class="header">
    <div class="brand">
        <a href="../index.php" class="back-link" aria-label="Back to home">←</a>
        <div class="shield"><img src="../asset/sk-logo.png" alt="SK LIGTAS Logo"></div>
        <div>
            <strong>SK LIGTAS</strong>
            <span>Disaster Alerts</span>
        </div>
    </div>
</header>

    <main class="content">

        <div class="intro">
            <h2>Disaster Alerts</h2>
            <p>Latest advisories</p>
        </div>

        <?php if ($result->num_rows > 0): ?>

            <div class="alerts">

                <?php while ($alert = $result->fetch_assoc()): ?>

                    <?php
                    $type = strtolower($alert['type'] ?? 'info');

                    if (!in_array($type, ['danger', 'warning', 'info'])) {
                        $type = 'info';
                    }
                    ?>

                    <article class="alert-card <?= htmlspecialchars($type) ?>">

                        <div class="alert-title">
                            <?php
                            if ($type === 'danger') {
                                echo '⚠ ';
                            } elseif ($type === 'warning') {
                                echo '⚠ ';
                            } else {
                                echo 'ℹ ';
                            }

                            echo htmlspecialchars($alert['title']);
                            ?>
                        </div>

                        <div class="alert-message">
                            <?= nl2br(htmlspecialchars($alert['message'])) ?>
                        </div>

                        <?php if (!empty($alert['alert_date'])): ?>
                            <div class="alert-location">
                                📍 <?= htmlspecialchars($alert['alert_date']) ?>
                            </div>
                        <?php endif; ?>

                        <div class="alert-date">
                            <?= htmlspecialchars($alert['created_at']) ?>
                        </div>

                    </article>

                <?php endwhile; ?>

            </div>

        <?php else: ?>

            <div class="empty">
                <h3>No active alerts</h3>
                <p>There are currently no disaster advisories.</p>
            </div>

        <?php endif; ?>

    </main>

</div>

</body>
</html>