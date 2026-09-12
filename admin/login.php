<?php
session_start();
require_once "../api/config.php";

if (isset($_SESSION['admin_id'])) {
    header("Location: index.php");
    exit;
}

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"] ?? "");
    $password = $_POST["password"] ?? "";

    if ($username === "" || $password === "") {
        $error = "Please enter your username and password.";
    } else {
        $stmt = $conn->prepare("SELECT id, username, password FROM admins WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();

        $result = $stmt->get_result();
        $admin = $result->fetch_assoc();

        if ($admin && (password_verify($password, $admin["password"]) || hash_equals((string)$admin["password"], $password))) {
            session_regenerate_id(true);

            $_SESSION["admin_id"] = $admin["id"];
            $_SESSION["admin_username"] = $admin["username"];

            header("Location: index.php");
            exit;
        } else {
            $error = "Invalid username or password.";
        }

        $stmt->close();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SK LIGTAS - Admin Login</title>

    <style>
*{box-sizing:border-box}:root{--navy:#062b69;--blue:#0754c7;--red:#ed1c24;--yellow:#f4c21f}
body{margin:0;min-height:100vh;display:flex;align-items:center;justify-content:center;font-family:Inter,Arial,sans-serif;background:#f4f7fb;background-image:linear-gradient(rgba(6,43,105,.025) 1px,transparent 1px),linear-gradient(90deg,rgba(6,43,105,.025) 1px,transparent 1px);background-size:22px 22px;padding:20px}.login-box{width:min(440px,100%);background:#fff;border:1px solid #dce5ef;border-radius:20px;overflow:hidden;box-shadow:0 18px 45px rgba(6,43,105,.14)}.login-box:before{content:"";display:block;height:7px;background:linear-gradient(90deg,var(--red) 0 32%,var(--yellow) 32% 46%,var(--navy) 46% 100%)}.login-box-inner{padding:34px 32px 30px}.logo{width:72px;height:72px;margin:0 auto 16px;border-radius:16px;background:var(--navy);color:#fff;display:flex;align-items:center;justify-content:center;font-size:25px;font-weight:900;box-shadow:0 8px 20px rgba(6,43,105,.2);border:5px solid #eaf2ff}.logo img{width:100%;height:100%;object-fit:contain;display:block}.logo{background:#fff!important}.logo img{border-radius:12px}h1{text-align:center;margin:0;font-size:28px;color:var(--navy)}.subtitle{text-align:center;color:#718096;margin:8px 0 28px;font-size:13px}label{display:block;margin:14px 0 7px;font-size:12px;font-weight:800;color:#263a56}input{width:100%;padding:13px 14px;border:1px solid #ccd8e5;border-radius:11px;font-size:15px;outline:none;background:#fbfcfe}input:focus{border-color:var(--blue);box-shadow:0 0 0 3px rgba(7,84,199,.11);background:#fff}button{width:100%;margin-top:22px;padding:13px;border:0;border-radius:11px;background:var(--navy);color:#fff;font-size:15px;font-weight:900;cursor:pointer;box-shadow:0 8px 18px rgba(6,43,105,.18)}button:hover{background:var(--blue)}.error{background:#fff0f1;color:#b51d2a;border:1px solid #ffcbd0;padding:11px;border-radius:10px;margin-bottom:12px;text-align:center;font-size:12px;font-weight:700}.back{display:block;text-align:center;margin-top:18px;color:var(--blue);text-decoration:none;font-size:12px;font-weight:800}.back:hover{text-decoration:underline}@media(max-width:480px){.login-box-inner{padding:28px 22px 24px}h1{font-size:24px}}
    </style>
</head>

<body>

<div class="login-box">

    <div class="logo"><img src="../asset/sk-logo.png" alt="Sangguniang Kabataan Logo"></div>

    <h1>SK LIGTAS</h1>
    <div class="subtitle">Administrator Login</div>

    <?php if ($error): ?>
        <div class="error">
            <?= htmlspecialchars($error) ?>
        </div>
    <?php endif; ?>

    <form method="POST">

        <label for="username">Username</label>

        <input
            type="text"
            id="username"
            name="username"
            placeholder="Enter username"
            required
        >

        <label for="password">Password</label>

        <input
            type="password"
            id="password"
            name="password"
            placeholder="Enter password"
            required
        >

        <button type="submit">LOGIN</button>

    </form>

    <a class="back" href="../index.php">
        ← Back to SK LIGTAS
    </a>

</div>

</body>
</html>