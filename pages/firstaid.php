<?php
require_once __DIR__ . '/../api/config.php';
$items = $conn->query('SELECT id, title, description FROM first_aid ORDER BY id')->fetch_all(MYSQLI_ASSOC);
?><!doctype html>
<html>
<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width,initial-scale=1">
	<title>First Aid</title>
	<link rel="stylesheet" href="../assets/style.css">
<style>
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
	<header class="header">
		<div class="brand">
			<a href="../index.php" class="back-link">&larr;</a>
			<div class="shield"><img src="../asset/sk-logo.png" alt="SK LIGTAS Logo"></div>
			<div><strong>SK LIGTAS</strong><span>First Aid Guide</span></div>
		</div>
	</header>
	<main>
		<section class="page-shell">
			<div class="section-title">
				<h1>Emergency First Aid</h1>
				<p>Basic guide</p>
			</div>
			<div class="tiles">
				<?php foreach ($items as $item): ?>
					<div class="tile">
						<div class="tile-icon">&#129657;</div>
						<b><?= htmlspecialchars($item['title'], ENT_QUOTES, 'UTF-8') ?></b>
						<div class="instruction-list">
							<?php foreach (preg_split('/\r\n|\r|\n/', trim($item['description'])) as $instruction): ?>
								<?php if (trim($instruction) !== ''): ?>
									<div class="instruction-line"><?= htmlspecialchars($instruction, ENT_QUOTES, 'UTF-8') ?></div>
								<?php endif; ?>
							<?php endforeach; ?>
						</div>
					</div>
				<?php endforeach; ?>
			</div>
		</section>
	</main>
</body>
</html>
