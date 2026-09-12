<?php
// Shared database configuration for SK LIGTAS.
// Provides both PDO ($pdo) and MySQLi ($conn) because older pages use MySQLi.

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$host = "localhost";
$dbname = "sk_ligtas";
$username = "root";
$password = "";

try {
    $pdo = new PDO(
        "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );

    // Backward compatibility for pages/admin code using MySQLi.
    $conn = new mysqli($host, $username, $password, $dbname);
    if ($conn->connect_errno) {
        throw new RuntimeException("MySQLi connection failed: " . $conn->connect_error);
    }
    $conn->set_charset("utf8mb4");

} catch (Throwable $e) {
    http_response_code(500);
    die("Database connection failed: " . htmlspecialchars($e->getMessage(), ENT_QUOTES, "UTF-8"));
}
