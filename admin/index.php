<?php

require_once __DIR__ . '/../api/config.php';
require_once __DIR__ . '/../api/sms.php';

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$error = '';

$quickMessages = [
    'Responders are on the way. Please stay in a safe location and keep your phone available.',
    'Your emergency report has been received. The assigned responder has been notified.',
    'Please stay where you are if it is safe to do so. Responders may contact you shortly.',
    'Your emergency report has been resolved. Please contact 911 again if you still need immediate assistance.'
];

$hasReportMessages = false;

try {
    $check = $pdo->query("SHOW TABLES LIKE 'report_messages'");
    $hasReportMessages = (bool)$check->fetchColumn();
} catch (Throwable $e) {
    $hasReportMessages = false;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    if ($action === 'status') {

        $reportId = (int)($_POST['report_id'] ?? 0);
        $status = $_POST['status'] ?? 'Pending';

        $allowed = [
            'Pending',
            'Responding',
            'Resolved',
            'Cancelled'
        ];

        if ($reportId > 0 && in_array($status, $allowed, true)) {

            try {

                $stmt = $pdo->prepare(
                    'UPDATE reports SET status=? WHERE id=?'
                );

                $stmt->execute([
                    $status,
                    $reportId
                ]);

                $msg = 'Report status updated.';

            } catch (Throwable $e) {

                $error = 'Unable to update report status.';
            }
        }
    }

    if ($action === 'send_message') {

        $reportId = (int)($_POST['report_id'] ?? 0);
        $message = trim($_POST['message'] ?? '');
        $newStatus = $_POST['new_status'] ?? 'Responding';

        $allowed = [
            'Pending',
            'Responding',
            'Resolved',
            'Cancelled'
        ];

        if (!in_array($newStatus, $allowed, true)) {
            $newStatus = 'Responding';
        }

        if ($reportId <= 0 || $message === '') {

            $error = 'Please choose a report and enter a message.';

        } else {

            try {

                $stmt = $pdo->prepare(
                    'SELECT id, contact, name
                     FROM reports
                     WHERE id=?
                     LIMIT 1'
                );

                $stmt->execute([
                    $reportId
                ]);

                $report = $stmt->fetch(PDO::FETCH_ASSOC);

                if (!$report) {

                    $error = 'Report not found.';

                } else {

                    $phone = trim($report['contact'] ?? '');

                    if ($phone === '') {

                        $error = 'This report does not have a contact number.';

                    } else {

                        $sms = sendSms(
                            $phone,
                            'SK LIGTAS: ' . $message
                        );

                        $success = !empty($sms['success']);

                        $smsStatus = $success
                            ? 'Sent'
                            : 'Not Sent';

                        $providerResponse = json_encode(
                            $sms,
                            JSON_UNESCAPED_UNICODE |
                            JSON_UNESCAPED_SLASHES
                        );

                        if ($hasReportMessages) {

                            try {

                                $save = $pdo->prepare(
                                    'INSERT INTO report_messages
                                    (
                                        report_id,
                                        phone,
                                        message,
                                        sent_by,
                                        sms_status,
                                        provider_response
                                    )
                                    VALUES(?,?,?,?,?,?)'
                                );

                                $save->execute([
                                    $reportId,
                                    $phone,
                                    $message,
                                    (int)$_SESSION['admin_id'],
                                    $smsStatus,
                                    $providerResponse
                                ]);

                            } catch (Throwable $e) {
                            }
                        }

                        if ($success) {

                            $statusStmt = $pdo->prepare(
                                'UPDATE reports
                                 SET status=?
                                 WHERE id=?'
                            );

                            $statusStmt->execute([
                                $newStatus,
                                $reportId
                            ]);

                            $msg =
                                'SMS sent to ' .
                                $phone .
                                ' and the report was updated.';

                        } else {

                            $error =
                                $sms['message']
                                ?? 'SMS could not be sent.';
                        }
                    }
                }

            } catch (Throwable $e) {

                $error =
                    'Unable to send the message.';
            }
        }
    }
}

try {

    $stmt = $pdo->query(
        'SELECT *
         FROM reports
         ORDER BY created_at DESC'
    );

    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Throwable $e) {

    $reports = [];

    if ($error === '') {
        $error = 'Unable to load reports.';
    }
}

$messageCounts = [];
$lastMessages = [];

if ($hasReportMessages) {

    try {

        $result = $pdo->query(
            'SELECT
                report_id,
                COUNT(*) AS total
             FROM report_messages
             GROUP BY report_id'
        );

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

            $messageCounts[
                (int)$row['report_id']
            ] = (int)$row['total'];
        }

    } catch (Throwable $e) {
    }

    try {

        $result = $pdo->query(
            'SELECT rm.*
             FROM report_messages rm
             INNER JOIN
             (
                SELECT
                    report_id,
                    MAX(id) AS max_id
                FROM report_messages
                GROUP BY report_id
             ) x
             ON x.max_id = rm.id'
        );

        while ($row = $result->fetch(PDO::FETCH_ASSOC)) {

            $lastMessages[
                (int)$row['report_id']
            ] = $row;
        }

    } catch (Throwable $e) {
    }
}

$counts = [
    'Pending' => 0,
    'Responding' => 0,
    'Resolved' => 0,
    'Cancelled' => 0
];

foreach (array_keys($counts) as $status) {

    try {

        $stmt = $pdo->prepare(
            'SELECT COUNT(*) AS total
             FROM reports
             WHERE status=?'
        );

        $stmt->execute([
            $status
        ]);

        $counts[$status] = (int)$stmt->fetchColumn();

    } catch (Throwable $e) {

        $counts[$status] = 0;
    }
}

function esc($value): string
{
    return htmlspecialchars(
        (string)($value ?? ''),
        ENT_QUOTES,
        'UTF-8'
    );
}

?>

<!doctype html>
<html lang="en">

<head>

<meta charset="utf-8">

<meta
    name="viewport"
    content="width=device-width,initial-scale=1"
>

<title>SK LIGTAS Admin</title>

<link rel="stylesheet" href="admin.css?v=20260904">

</head>

<body>

<header class="admin-header">

    <div class="admin-brand">

        <div class="admin-shield"><img src="../asset/sk-logo.png" alt="SK LIGTAS Logo"></div>

        <div>
            <strong>SK LIGTAS</strong>

            <span>
                Emergency Response Administration
            </span>
        </div>

    </div>

    <nav>

        <a
            class="active"
            href="index.php"
        >
            Reports
        </a>

        <a href="settings.php">
            Manage
        </a>

        <a href="../index.php">
            Website
        </a>

        <a href="logout.php">
            Logout
        </a>

    </nav>

</header>

<main class="dashboard">

    <div class="dashboard-title">

        <div>

            <span class="eyebrow">
                SK LIGTAS ADMIN
            </span>

            <h1>
                Emergency Reports
            </h1>

            <p>
                Review, manage, and respond to emergency reports.
            </p>

        </div>

        <button
            class="refresh-btn"
            onclick="location.reload()"
        >
            ↻ Refresh
        </button>

    </div>

    <?php if ($msg !== ''): ?>

        <div class="flash success">
            <?= esc($msg) ?>
        </div>

    <?php endif; ?>

    <?php if ($error !== ''): ?>

        <div class="flash error">
            <?= esc($error) ?>
        </div>

    <?php endif; ?>

    <section class="stats">

        <div class="stat pending">

            <span>
                PENDING
            </span>

            <strong>
                <?= $counts['Pending'] ?>
            </strong>

            <small>
                Reports waiting
            </small>

        </div>

        <div class="stat responding">

            <span>
                RESPONDING
            </span>

            <strong>
                <?= $counts['Responding'] ?>
            </strong>

            <small>
                Being handled
            </small>

        </div>

        <div class="stat resolved">

            <span>
                RESOLVED
            </span>

            <strong>
                <?= $counts['Resolved'] ?>
            </strong>

            <small>
                Completed reports
            </small>

        </div>

        <div class="stat total">

            <span>
                TOTAL REPORTS
            </span>

            <strong>
                <?= array_sum($counts) ?>
            </strong>

            <small>
                All reports
            </small>

        </div>

    </section>

    <section class="admin-card">

        <div class="card-top">

            <div>

                <h2>
                    Emergency Reports
                </h2>

                <small>
                    Review reports and communicate with residents.
                </small>

            </div>

            <button
                class="notification-btn"
                onclick="location.reload()"
            >
                Refresh Reports
            </button>

        </div>

        <?php if (empty($reports)): ?>

            <div class="empty-state">

                <div>
                    ✓
                </div>

                <h3>
                    No Emergency Reports
                </h3>

                <p>
                    There are currently no emergency reports.
                </p>

            </div>

        <?php else: ?>

            <div class="report-list">

                <?php foreach ($reports as $r): ?>

                    <?php

                    $rid = (int)($r['id'] ?? 0);

                    $status =
                        $r['status']
                        ?? 'Pending';

                    $contact =
                        $r['contact']
                        ?? '';

                    $name =
                        $r['name']
                        ?? 'Unknown';

                    $category =
                        $r['category']
                        ?? 'Emergency';

                    $location =
                        $r['location']
                        ?? '';

                    $description =
                        $r['description']
                        ?? '';

                    $latitude =
                        $r['latitude']
                        ?? null;

                    $longitude =
                        $r['longitude']
                        ?? null;

                    $createdAt =
                        $r['created_at']
                        ?? '';

                    $statusClass =
                        strtolower(
                            preg_replace(
                                '/[^a-zA-Z0-9_-]/',
                                '',
                                $status
                            )
                        );

                    $messageTotal =
                        $messageCounts[$rid]
                        ?? 0;

                    ?>

                    <article
                        class="report-card <?= esc($statusClass) ?>"
                    >

                        <div class="report-top">

                            <div>

                                <span class="report-id">
                                    REPORT #<?= $rid ?>
                                </span>

                                <h3>
                                    <?= esc($r['category'] ?? 'Emergency') ?>
                                </h3>

                                <time>
                                    <?= esc($createdAt) ?>
                                </time>

                            </div>

                            <span
                                class="status-pill <?= esc($statusClass) ?>"
                            >
                                <?= esc($status) ?>
                            </span>

                        </div>

                        <div class="report-grid">

                            <div>

                                <label>
                                    NAME
                                </label>

                                <strong>
                                    <?= esc($name) ?>
                                </strong>

                            </div>

                            <div>

                                <label>
                                    PHONE
                                </label>

                                <?php if ($contact !== ''): ?>

                                    <a
                                        href="tel:<?= esc($contact) ?>"
                                    >
                                        ☎ <?= esc($contact) ?>
                                    </a>

                                <?php else: ?>

                                    <span>
                                        No phone number
                                    </span>

                                <?php endif; ?>

                            </div>

                            <div class="wide">

                                <label>
                                    LOCATION
                                </label>

                                <strong>
                                    <?= esc($location) ?>
                                </strong>

                                <?php if (
                                    $latitude !== null &&
                                    $longitude !== null &&
                                    $latitude !== '' &&
                                    $longitude !== ''
                                ): ?>

                                    <a
                                        target="_blank"
                                        href="https://www.google.com/maps?q=<?= urlencode($latitude . ',' . $longitude) ?>"
                                    >
                                        View Map
                                    </a>

                                <?php elseif ($location !== ''): ?>

                                    <a
                                        target="_blank"
                                        href="https://www.google.com/maps/search/?api=1&query=<?= urlencode($location) ?>"
                                    >
                                        View Map
                                    </a>

                                <?php endif; ?>

                            </div>

                            <div class="wide">

                                <label>
                                    DESCRIPTION
                                </label>

                                <p>
                                    <?= nl2br(esc($description)) ?>
                                </p>

                            </div>

                        </div>

                        <div class="report-actions">

                            <form
                                method="POST"
                            >

                                <input
                                    type="hidden"
                                    name="action"
                                    value="status"
                                >

                                <input
                                    type="hidden"
                                    name="report_id"
                                    value="<?= $rid ?>"
                                >

                                <select
                                    name="status"
                                    onchange="this.form.submit()"
                                >

                                    <?php foreach (
                                        [
                                            'Pending',
                                            'Responding',
                                            'Resolved',
                                            'Cancelled'
                                        ] as $s
                                    ): ?>

                                        <option
                                            value="<?= esc($s) ?>"
                                            <?= $status === $s ? 'selected' : '' ?>
                                        >
                                            <?= esc($s) ?>
                                        </option>

                                    <?php endforeach; ?>

                                </select>

                            </form>

                            <?php if ($contact !== ''): ?>

                                <a
                                    href="tel:<?= esc($contact) ?>"
                                >
                                    ☎ Call
                                </a>

                                <button
                                    type="button"
                                    onclick="openSmsModal(
                                        <?= $rid ?>,
                                        <?= htmlspecialchars(
                                            json_encode($name),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>,
                                        <?= htmlspecialchars(
                                            json_encode($contact),
                                            ENT_QUOTES,
                                            'UTF-8'
                                        ) ?>
                                    )"
                                >
                                    ✉ SMS
                                </button>

                            <?php endif; ?>

                            <span>

                                <?= $messageTotal ?>

                                message<?= $messageTotal === 1 ? '' : 's' ?>

                            </span>

                        </div>

                    </article>

                <?php endforeach; ?>

            </div>

        <?php endif; ?>

    </section>

</main>

<div
    id="smsModal"
    class="modal"
>

    <div
        class="modal-backdrop"
        onclick="closeSmsModal()"
    ></div>

    <div class="sms-dialog">

        <button
            type="button"
            class="modal-close"
            onclick="closeSmsModal()"
        >
            ×
        </button>

        <h2>
            Send SMS
        </h2>

        <p>
            Send an emergency response message to the resident.
        </p>

        <form method="POST">

            <input
                type="hidden"
                name="action"
                value="send_message"
            >

            <input
                type="hidden"
                id="smsReportId"
                name="report_id"
            >

            <label>
                RESIDENT
            </label>

            <input
                type="text"
                id="smsName"
                readonly
            >

            <label>
                PHONE
            </label>

            <input
                type="text"
                id="smsPhone"
                readonly
            >

            <label>
                QUICK MESSAGE
            </label>

            <div class="quick-messages">

                <?php foreach ($quickMessages as $quick): ?>

                    <button
                        type="button"
                        onclick='setSmsMessage(<?= json_encode($quick) ?>)'
                    >
                        <?= esc($quick) ?>
                    </button>

                <?php endforeach; ?>

            </div>

            <label>
                MESSAGE
            </label>

            <textarea
                id="smsMessage"
                name="message"
                maxlength="320"
                required
            ></textarea>

            <div class="message-meta">

                <span>
                    Maximum 320 characters
                </span>

                <span id="smsCount">
                    0/320
                </span>

            </div>

            <label>
                UPDATE STATUS TO
            </label>

            <select name="new_status">

                <option value="Responding">
                    Responding
                </option>

                <option value="Resolved">
                    Resolved
                </option>

                <option value="Pending">
                    Keep Pending
                </option>

            </select>

            <button
                class="send-real-sms"
                type="submit"
            >
                📱 Send SMS to Client
            </button>

            <small class="sms-note">
                The SMS is sent through your configured
                SMS provider to the actual mobile number above.
            </small>

        </form>

    </div>

</div>

<script src="admin.js"></script>

</body>
</html>