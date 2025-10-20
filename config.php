<?php
// config.php
$DB_HOST = 'localhost';
$DB_NAME = 'danapts';
$DB_USER = 'root';
$DB_PASS = ''; // isi sesuai password XAMPP (default kosong)

try {
    $pdo = new PDO("mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}
