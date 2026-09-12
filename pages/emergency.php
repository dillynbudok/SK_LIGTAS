<?php

require_once __DIR__ . '/../api/config.php';

$contacts = [];
$hasLogo = false;

try {

    $columns = $conn->query("SHOW COLUMNS FROM contacts");

    if ($columns) {

        while ($column = $columns->fetch_assoc()) {

            if (($column['Field'] ?? '') === 'logo') {
                $hasLogo = true;
                break;
            }

        }

    }

    $select = "SELECT * FROM contacts ORDER BY id ASC";

    $result = $conn->query($select);

    if ($result) {
        $contacts = $result->fetch_all(MYSQLI_ASSOC);
    }

} catch (Throwable $e) {

    $contacts = [];

}

$pdfHotlines = [
    ['MDRRM OFFICE', '0919-090-2713', '0905-812-1535', 'mdrrmo.png'],
    ['MLET STA. CRUZ', '0947-866-8394', '0917-568-2562', 'mlet.png'],
    ['PNP STA. CRUZ', '0917-796-5020', '0998-598-5080', 'pnp.png'],
    ['501ST IB, PH ARMY', '0916-743-6962 (CMO)', '0956-491-0888', 'army.png'],
    ['BFP STA. CRUZ', '0917-185-3911', '', 'bfp.png'],
    ['RHU STA. CRUZ', '0917-626-2287', '', 'rhu.png'],
    ['AMBULANCE STA. CRUZ', '0917-135-5390', '', 'ambulance.png'],
    ['PH COAST GUARD', '0969-271-1039', '', 'coast_guard.png']
];

$baranggay = [];

foreach ($contacts as $contact) {

    $category = strtolower(
        trim((string)($contact['category'] ?? ''))
    );

    if ($category === 'barangay') {
        $baranggay[] = $contact;
    }

}

$logoBase = '../uploads/logos/';

?>

<!doctype html>

<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1"
>

<title>Emergency Hotlines | SK LIGTAS</title>

<link
    rel="stylesheet"
    href="../assets/style.css"
>

<style>

body {
    background: #f4f7fb;
    color: #14233b;
}

.hotline-page {
    width: min(1180px, 94%);
    margin: 28px auto 55px;
}

.pdf-header {
    height: 74px;
    border-radius: 18px 18px 0 0;

    background:
        linear-gradient(
            135deg,
            #062b69 0 30%,
            #e51d2a 30% 62%,
            #f4c21f 62% 69%,
            #e51d2a 69% 100%
        );

    display: flex;
    align-items: center;

    padding: 0 24px;

    overflow: hidden;

    position: relative;
}

.pdf-header::after {
    content: "";

    position: absolute;

    right: 0;
    top: 0;

    width: 48%;
    height: 100%;

    background:
        linear-gradient(
            135deg,
            transparent 0 28%,
            #f4c21f 28% 38%,
            #e51d2a 38% 100%
        );

    opacity: .95;
}

.pdf-logo {
    position: relative;

    z-index: 2;

    color: #ffffff;

    font-family:
        "Arial Narrow",
        "Roboto Condensed",
        Arial,
        sans-serif;

    font-size: 31px;

    font-weight: 900;

    font-style: italic;

    letter-spacing: -1px;
}

.pdf-logo span {
    color: #f4c21f;
}

.hotline-back {
    position: absolute;

    right: 24px;
    top: 50%;

    transform: translateY(-50%);

    z-index: 10;

    display: inline-flex;

    align-items: center;
    justify-content: center;

    gap: 7px;

    color: #ffffff;

    text-decoration: none;

    font-weight: 800;

    font-size: 12px;

    background: rgba(255,255,255,.12);

    border: 1px solid rgba(255,255,255,.45);

    padding: 9px 13px;

    border-radius: 9px;

    transition: .2s ease;
}

.hotline-back:hover {
    background: #0754c7;

    color: #ffffff;

    border-color: #ffffff;
}

.hotline-card {
    background: #ffffff;

    border: 1px solid #e1e7ef;

    border-radius: 18px;

    padding: 26px;

    box-shadow:
        0 7px 25px rgba(18,47,82,.07);
}

.hotline-title {
    display: flex;

    align-items: center;

    gap: 12px;

    margin: 0 0 20px;
}

.hotline-title-icon {
    width: 48px;
    height: 48px;

    border-radius: 50%;

    display: grid;

    place-items: center;

    background: #e51d2a;

    color: #ffffff;

    font-size: 22px;

    box-shadow:
        0 4px 10px rgba(229,29,42,.2);
}

.hotline-title h1 {
    margin: 0;

    font-family:
        "Arial Narrow",
        "Roboto Condensed",
        Arial,
        sans-serif;

    font-size: 31px;

    color: #142f55;

    text-transform: uppercase;

    letter-spacing: .3px;
}

.hotline-list {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 9px 14px;
}

.hotline-item {
    min-height: 90px;

    background: #ffffff;

    border: 1px solid #e4e8ee;

    border-radius: 13px;

    padding: 10px 14px;

    display: flex;

    align-items: center;

    gap: 13px;

    box-shadow:
        0 3px 12px rgba(22,48,82,.05);
}

.hotline-logo {
    width: 62px;
    height: 62px;

    flex: 0 0 62px;

    object-fit: contain;
}

.hotline-info {
    flex: 1;

    min-width: 0;
}

.hotline-info strong {
    display: block;

    color: #132d50;

    font-size: 14px;

    font-weight: 900;
}

.hotline-info span {
    display: block;

    color: #344a67;

    font-size: 12px;

    line-height: 1.45;
}

.hotline-call {
    width: 42px;
    height: 42px;

    flex: 0 0 42px;

    border-radius: 50%;

    display: grid;

    place-items: center;

    background: #0754c7;

    color: #ffffff;

    font-size: 17px;

    text-decoration: none;

    transition: .2s ease;
}

.hotline-call:hover {
    background: #073b91;

    transform: scale(1.05);
}

.hotline-item:nth-child(odd) .hotline-call {
    background: #e51d2a;
}

.hotline-item:nth-child(odd) .hotline-call:hover {
    background: #c91420;
}

.pdf-divider {
    margin: 25px 0 16px;

    display: flex;

    align-items: center;

    gap: 10px;

    color: #0754c7;

    font-weight: 900;

    font-size: 14px;

    text-transform: uppercase;
}

.pdf-divider::before {
    content: "";

    width: 42px;
    height: 18px;

    border-radius: 50%;

    background: #0754c7;
}

.council-grid {
    display: grid;

    grid-template-columns: 1fr 1fr;

    gap: 12px;
}

.council-box {
    border: 1px solid #e1e7ef;

    border-radius: 12px;

    padding: 13px 15px;

    background: #fbfcfe;
}

.council-box strong {
    display: block;

    color: #173764;

    font-size: 12px;

    text-transform: uppercase;
}

.council-box span {
    display: block;

    margin-top: 4px;

    color: #4d5c70;

    font-size: 11px;
}

@media (max-width: 760px) {

    .hotline-page {
        width: calc(100% - 20px);

        margin: 10px auto 35px;
    }

    .hotline-card {
        padding: 17px;
    }

    .pdf-header {
        height: 62px;

        padding: 0 17px;
    }

    .pdf-logo {
        font-size: 25px;
    }

    .hotline-back {
        right: 15px;

        padding: 8px 11px;

        font-size: 11px;
    }

    .hotline-title h1 {
        font-size: 25px;
    }

    .hotline-list {
        grid-template-columns: 1fr;
    }

    .hotline-item {
        min-height: 82px;
    }

    .hotline-logo {
        width: 54px;
        height: 54px;

        flex-basis: 54px;
    }

    .council-grid {
        grid-template-columns: 1fr;
    }

}

</style>

</head>

<body>

<div class="hotline-page">

<header class="pdf-header">

<a
    class="hotline-back"
    href="../index.php"
    aria-label="Back to home"
>
    ← Back
</a>

<div class="pdf-logo">
    SK <span>LIGTAS</span>
</div>

</header>

<main class="hotline-card">

<div class="hotline-title">

<div class="hotline-title-icon">
    ☎
</div>

<h1>
    Emergency Hotlines
</h1>

</div>

<div class="hotline-list">

<?php foreach ($pdfHotlines as $hotline): ?>

<div class="hotline-item">

<img
    class="hotline-logo"
    src="<?= htmlspecialchars(
        $logoBase . $hotline[3],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    alt="<?= htmlspecialchars(
        $hotline[0],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>

<div class="hotline-info">

<strong>
    <?= htmlspecialchars(
        $hotline[0],
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</strong>

<span>
    <?= htmlspecialchars(
        $hotline[1],
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</span>

<?php if ($hotline[2] !== ''): ?>

<span>
    <?= htmlspecialchars(
        $hotline[2],
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</span>

<?php endif; ?>

</div>

<a
    class="hotline-call"
    href="tel:<?= htmlspecialchars(
        preg_replace(
            '/[^0-9+]/',
            '',
            $hotline[1]
        ),
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
    aria-label="Call <?= htmlspecialchars(
        $hotline[0],
        ENT_QUOTES,
        'UTF-8'
    ) ?>"
>
    ☎
</a>

</div>

<?php endforeach; ?>

</div>

<?php if (!empty($baranggay)): ?>

<div class="pdf-divider">
    Barangay Council
</div>

<div class="council-grid">

<?php foreach ($baranggay as $contact): ?>

<div class="council-box">

<strong>
    <?= htmlspecialchars(
        $contact['name'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</strong>

<span>
    <?= htmlspecialchars(
        $contact['phone'] ?? '',
        ENT_QUOTES,
        'UTF-8'
    ) ?>
</span>

</div>

<?php endforeach; ?>

</div>

<?php endif; ?>

<div class="pdf-divider">
    Sangguniang Kabataan Official
</div>

<div class="council-box">

<strong>
    SK CHAIRMAN
</strong>

<span>
    John Lloyd Christian C. Alvis &nbsp; 09569064717
</span>

</div>

</main>

</div>

</body>

</html>