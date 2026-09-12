<?php

require_once __DIR__ . '/../api/config.php';

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$msg = '';
$error = '';
$logoDir = __DIR__ . '/../uploads/logos/';
$logoUrl = '../uploads/logos/';
$buttonDir = __DIR__ . '/../uploads/buttons/';
$buttonUrl = '../uploads/buttons/';
$buttonKeys = [
    'request' => 'Request Help',
    'hospitals' => 'Nearest Hospitals',
    'contacts' => 'Emergency Contacts',
    'incident' => 'Incident Map',
    'alerts' => 'Disaster Alerts',
    'evacuation' => 'Evacuation Centers',
    'firstaid' => 'First Aid'
];

if (!is_dir($logoDir)) {
    mkdir($logoDir, 0755, true);
}
if (!is_dir($buttonDir)) {
    mkdir($buttonDir, 0755, true);
}

if (isset($_GET['delete'], $_GET['table'], $_GET['id'])) {
    $allowed = [
        'contacts',
        'alerts',
        'hospitals',
        'evacuation_centers',
        'first_aid'
    ];

    if (in_array($_GET['table'], $allowed, true)) {
        $table = $_GET['table'];
        $id = (int)$_GET['id'];

        if ($table === 'contacts') {
            $find = $conn->prepare('SELECT logo FROM contacts WHERE id=?');
            $find->bind_param('i', $id);
            $find->execute();
            $old = $find->get_result()->fetch_assoc();

            if (!empty($old['logo'])) {
                $oldPath = $logoDir . basename($old['logo']);
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
        }

        $stmt = $conn->prepare("DELETE FROM `$table` WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();

        header('Location: settings.php');
        exit;
    }
}

if (isset($_POST['password_change'])) {
    $old = $_POST['old'] ?? '';
    $new = $_POST['new'] ?? '';

    $result = $conn->query(
        'SELECT password FROM admins WHERE id=' . (int)$_SESSION['admin_id']
    );

    $row = $result ? $result->fetch_assoc() : null;

    if (
        $row &&
        (password_verify($old, $row['password']) || hash_equals($row['password'], $old)) &&
        strlen($new) >= 6
    ) {
        $hash = password_hash($new, PASSWORD_DEFAULT);
        $stmt = $conn->prepare('UPDATE admins SET password=? WHERE id=?');
        $stmt->bind_param('si', $hash, $_SESSION['admin_id']);
        $stmt->execute();
        $msg = 'Password changed successfully.';
    } else {
        $error = 'Current password is incorrect or new password must be at least 6 characters.';
    }
}

if (isset($_POST['button_upload'])) {
    $key = $_POST['button_upload'];
    if (isset($buttonKeys[$key]) && !empty($_FILES['button_image']['name']) && $_FILES['button_image']['error'] === UPLOAD_ERR_OK) {
        $allowedMime = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/webp' => 'webp'
        ];
        $mime = mime_content_type($_FILES['button_image']['tmp_name']);
        if (isset($allowedMime[$mime]) && $_FILES['button_image']['size'] <= 4 * 1024 * 1024) {
            foreach (['jpg','jpeg','png','webp'] as $oldExt) {
                $oldPath = $buttonDir . $key . '.' . $oldExt;
                if (is_file($oldPath)) {
                    unlink($oldPath);
                }
            }
            $fileName = $key . '.' . $allowedMime[$mime];
            if (move_uploaded_file($_FILES['button_image']['tmp_name'], $buttonDir . $fileName)) {
                $msg = $buttonKeys[$key] . ' image updated successfully.';
            } else {
                $error = 'Unable to save the button image.';
            }
        } else {
            $error = 'Button image must be JPG, PNG, or WEBP and 4 MB or smaller.';
        }
    } else {
        $error = 'Please choose an image.';
    }
}

if (isset($_GET['delete_button'])) {
    $key = $_GET['delete_button'];
    if (isset($buttonKeys[$key])) {
        foreach (['jpg','jpeg','png','webp'] as $ext) {
            $path = $buttonDir . $key . '.' . $ext;
            if (is_file($path)) {
                unlink($path);
            }
        }
        header('Location: settings.php');
        exit;
    }
}

if (isset($_POST['add'])) {
    $type = $_POST['add'];

    if ($type === 'contact') {
        $name = trim($_POST['name'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $logoFile = null;

        if ($name === '' || $phone === '') {
            $error = 'Contact name and phone number are required.';
        } else {
            if (!empty($_FILES['logo']['name']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
                $allowedMime = [
                    'image/jpeg' => 'jpg',
                    'image/png' => 'png',
                    'image/webp' => 'webp'
                ];

                $mime = mime_content_type($_FILES['logo']['tmp_name']);

                if (isset($allowedMime[$mime]) && $_FILES['logo']['size'] <= 2 * 1024 * 1024) {
                    $logoFile = 'contact_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $allowedMime[$mime];
                    move_uploaded_file(
                        $_FILES['logo']['tmp_name'],
                        $logoDir . $logoFile
                    );
                } else {
                    $error = 'Logo must be JPG, PNG, or WEBP and 2 MB or smaller.';
                }
            }

            if ($error === '') {
                $stmt = $conn->prepare(
                    'INSERT INTO contacts(name,position,phone,logo) VALUES(?,?,?,?)'
                );
                $stmt->bind_param('ssss', $name, $position, $phone, $logoFile);

                if ($stmt->execute()) {
                    $msg = 'Emergency contact added successfully.';
                } else {
                    $error = 'Unable to add contact: ' . $stmt->error;
                }
            }
        }
    }

    if ($type === 'alert') {
        $title = trim($_POST['title'] ?? '');
        $message = trim($_POST['message'] ?? '');
        $level = $_POST['level'] ?? 'info';

        $stmt = $conn->prepare(
            'INSERT INTO alerts(title,message,type) VALUES(?,?,?)'
        );
        $stmt->bind_param('sss', $title, $message, $level);
        $stmt->execute();
        $msg = 'Alert added successfully.';
    }

    if ($type === 'hospital') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');

        $stmt = $conn->prepare(
            'INSERT INTO hospitals(name,location,phone) VALUES(?,?,?)'
        );
        $stmt->bind_param('sss', $name, $address, $phone);
        $stmt->execute();
        $msg = 'Hospital added successfully.';
    }

    if ($type === 'evac') {
        $name = trim($_POST['name'] ?? '');
        $address = trim($_POST['address'] ?? '');
        $capacity = (int)($_POST['capacity'] ?? 0);
        $status = $_POST['status'] ?? 'Open';

        $stmt = $conn->prepare(
            'INSERT INTO evacuation_centers(name,location,capacity,status) VALUES(?,?,?,?)'
        );
        $stmt->bind_param('ssis', $name, $address, $capacity, $status);
        $stmt->execute();
        $msg = 'Evacuation center added successfully.';
    }

    if ($type === 'aid') {
        $title = trim($_POST['title'] ?? '');
        $instructions = trim($_POST['instructions'] ?? '');

        $stmt = $conn->prepare(
            'INSERT INTO first_aid(title,description) VALUES(?,?)'
        );
        $stmt->bind_param('ss', $title, $instructions);
        $stmt->execute();
        $msg = 'First aid guide added successfully.';
    }
}

$contacts = $conn->query(
    'SELECT * FROM contacts ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);

$alerts = $conn->query(
    'SELECT * FROM alerts ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);

$hospitals = $conn->query(
    'SELECT * FROM hospitals ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);

$evacs = $conn->query(
    'SELECT * FROM evacuation_centers ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);

$aids = $conn->query(
    'SELECT * FROM first_aid ORDER BY id DESC'
)->fetch_all(MYSQLI_ASSOC);

function esc($value): string
{
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}
?>
<!doctype html>
<html lang="en">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Manage SK LIGTAS</title>
<link rel="stylesheet" href="admin.css">
<style>
.manage-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.manage-box {
    background: #fff;
    border: 1px solid #e1e7ef;
    border-radius: 15px;
    padding: 18px;
}

.manage-box h2 {
    font-size: 16px;
    color: #063b91;
}

.manage-box form {
    display: grid;
    gap: 7px;
}

.manage-box input,
.manage-box textarea,
.manage-box select {
    padding: 10px;
    border: 1px solid #d9e0e8;
    border-radius: 9px;
}

.manage-box textarea {
    min-height: 90px;
}

.manage-box button {
    background: #0754c7;
    color: #fff;
    border: 0;
    padding: 10px;
    border-radius: 9px;
    font-weight: bold;
    cursor: pointer;
}

.items {
    margin-top: 12px;
}

.item {
    padding: 10px;
    background: #f7f9fb;
    border-radius: 9px;
    margin-top: 7px;
    font-size: 11px;
}

.item a {
    float: right;
    color: #c6202d;
}

.contact-preview {
    width: 42px;
    height: 42px;
    object-fit: contain;
    background: #fff;
    border-radius: 8px;
    vertical-align: middle;
    margin-right: 7px;
}

.msg,
.error {
    padding: 10px;
    border-radius: 9px;
    margin-bottom: 15px;
}

.msg {
    background: #e4f8ec;
    color: #18763d;
}

.error {
    background: #ffe7e9;
    color: #b31c2b;
}

.button-manage-list{display:grid;gap:10px;margin-top:14px}.button-manage-row{display:grid;grid-template-columns:1fr auto;gap:8px;align-items:center;padding:10px;background:#f7f9fb;border-radius:10px}.button-manage-row strong{font-size:11px;color:#17375f}.button-manage-row form{grid-column:1/-1;display:flex;gap:7px;align-items:center}.button-manage-row input[type=file]{min-width:0;flex:1;font-size:10px}.button-manage-row button{padding:8px 12px!important;border-radius:8px!important}.button-preview{width:44px;height:44px;object-fit:cover;border-radius:8px;border:1px solid #dfe6ef;background:#fff}.button-delete{font-size:10px;color:#c6202d!important;text-align:right}
@media (max-width: 800px) {
    .manage-grid {
        grid-template-columns: 1fr;
    }
}
</style>
</head>
<body>
<header class="admin-header">
    <div>
        <b>🛡 SK LIGTAS</b>
        <span>Website Management</span>
    </div>

    <nav>
        <a href="index.php">Reports</a>
        <a href="settings.php">Manage</a>
        <a href="../index.php">Website</a>
        <a href="logout.php">Logout</a>
    </nav>
</header>

<main class="dashboard">
    <h1>Manage Website</h1>

    <?php if ($msg): ?>
        <div class="msg"><?= esc($msg) ?></div>
    <?php endif; ?>

    <?php if ($error): ?>
        <div class="error"><?= esc($error) ?></div>
    <?php endif; ?>

    <div class="manage-grid">
<div class="manage-box">
<h2>Dashboard Button Images</h2>
<p>Upload a picture for any dashboard button. The existing button size and layout stay the same.</p>
<div class="button-manage-list">
<?php foreach ($buttonKeys as $key => $label): ?>
<div class="button-manage-row">
<strong><?= esc($label) ?></strong>
<?php
$currentImage = '';
foreach (['jpg','jpeg','png','webp'] as $ext) {
    if (is_file($buttonDir . $key . '.' . $ext)) {
        $currentImage = $buttonUrl . $key . '.' . $ext;
        break;
    }
}
?>
<?php if ($currentImage): ?>
<img class="button-preview" src="<?= esc($currentImage) ?>" alt="">
<?php endif; ?>
<form method="post" enctype="multipart/form-data">
<input type="hidden" name="button_upload" value="<?= esc($key) ?>">
<input type="file" name="button_image" accept="image/jpeg,image/png,image/webp" required>
<button type="submit">Upload</button>
</form>
<?php if ($currentImage): ?>
<a class="button-delete" data-confirm="Remove this dashboard button image?" href="?delete_button=<?= urlencode($key) ?>">Remove</a>
<?php endif; ?>
</div>
<?php endforeach; ?>
</div>
</div>


        <div class="manage-box">
            <h2>Emergency Contacts & Logos</h2>

            <form method="post" enctype="multipart/form-data">
                <input type="hidden" name="add" value="contact">
                <input name="name" placeholder="Name" required>
                <input name="position" placeholder="Position / Role">
                <input name="phone" placeholder="Phone" required>
                <label>Logo</label>
                <input type="file" name="logo" accept="image/jpeg,image/png,image/webp">
                <button type="submit">Add Contact</button>
            </form>

            <div class="items">
                <?php foreach ($contacts as $contact): ?>
                    <div class="item">
                        <?php if (!empty($contact['logo'])): ?>
                            <img
                                class="contact-preview"
                                src="<?= esc($logoUrl . basename($contact['logo'])) ?>"
                                alt=""
                            >
                        <?php endif; ?>

                        <b><?= esc($contact['name']) ?></b>
                        <br>
                        <?= esc($contact['position'] ?? $contact['role'] ?? '') ?>
                        <br>
                        <?= esc($contact['phone']) ?>

                        <a
                            data-confirm="Delete this contact?"
                            href="?delete=1&table=contacts&id=<?= (int)$contact['id'] ?>"
                        >Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="manage-box">
            <h2>Disaster Alerts</h2>

            <form method="post">
                <input type="hidden" name="add" value="alert">
                <input name="title" placeholder="Title" required>
                <textarea name="message" placeholder="Message" required></textarea>

                <select name="level">
                    <option value="info">Info</option>
                    <option value="warning">Warning</option>
                    <option value="danger">Danger</option>
                </select>

                <button type="submit">Add Alert</button>
            </form>

            <div class="items">
                <?php foreach ($alerts as $alert): ?>
                    <div class="item">
                        <b><?= esc($alert['title']) ?></b>
                        <br>
                        <?= esc($alert['message']) ?>
                        <a
                            data-confirm="Delete this alert?"
                            href="?delete=1&table=alerts&id=<?= (int)$alert['id'] ?>"
                        >Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="manage-box">
            <h2>Hospitals</h2>

            <form method="post">
                <input type="hidden" name="add" value="hospital">
                <input name="name" placeholder="Hospital name" required>
                <input name="address" placeholder="Address" required>
                <input name="phone" placeholder="Phone">
                <button type="submit">Add Hospital</button>
            </form>

            <div class="items">
                <?php foreach ($hospitals as $hospital): ?>
                    <div class="item">
                        <b><?= esc($hospital['name']) ?></b>
                        <br>
                        <?= esc($hospital['location']) ?>
                        <a
                            data-confirm="Delete this hospital?"
                            href="?delete=1&table=hospitals&id=<?= (int)$hospital['id'] ?>"
                        >Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="manage-box">
            <h2>Evacuation Centers</h2>

            <form method="post">
                <input type="hidden" name="add" value="evac">
                <input name="name" placeholder="Center name" required>
                <input name="address" placeholder="Address" required>
                <input type="number" name="capacity" placeholder="Capacity" value="0">

                <select name="status">
                    <option value="Open">Open</option>
                    <option value="Full">Full</option>
                    <option value="Closed">Closed</option>
                </select>

                <button type="submit">Add Center</button>
            </form>

            <div class="items">
                <?php foreach ($evacs as $evac): ?>
                    <div class="item">
                        <b><?= esc($evac['name']) ?></b>
                        <br>
                        <?= esc($evac['status']) ?>
                        <a
                            data-confirm="Delete this center?"
                            href="?delete=1&table=evacuation_centers&id=<?= (int)$evac['id'] ?>"
                        >Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="manage-box">
            <h2>First Aid Guides</h2>
            <p>Clients can tap a guide to read the full instructions.</p>

            <form method="post">
                <input type="hidden" name="add" value="aid">
                <input name="title" placeholder="Guide title" required>
                <textarea name="instructions" placeholder="Instructions" required></textarea>
                <button type="submit">Add Guide</button>
            </form>

            <div class="items">
                <?php foreach ($aids as $aid): ?>
                    <div class="item">
                        <b><?= esc($aid['title']) ?></b>
                        <br>
                        <?= nl2br(esc($aid['description'])) ?>
                        <a
                            data-confirm="Delete this guide?"
                            href="?delete=1&table=first_aid&id=<?= (int)$aid['id'] ?>"
                        >Delete</a>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="manage-box">
            <h2>Admin Password</h2>

            <form method="post">
                <input type="hidden" name="password_change" value="1">
                <input type="password" name="old" placeholder="Current password" required>
                <input type="password" name="new" placeholder="New password (6+ characters)" required>
                <button type="submit">Change Password</button>
            </form>
        </div>

    </div>
</main>

<script src="admin.js"></script>
</body>
</html>
