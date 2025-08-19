<?php
// config/database.php
$host = 'localhost';
$dbname = 'furniture_shop';
$username = 'root';
$password = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
    $pdo->exec("SET CHARACTER SET utf8mb4");
    $pdo->exec("SET SESSION collation_connection = 'utf8mb4_unicode_ci'");
} catch(PDOException $e) {
    die("ການເຊື່ອມຕໍ່ຖານຂໍ້ມູນລົ້ມເຫລວ: " . $e->getMessage());
}

// ເລີ່ມ session ຖ້າຍັງບໍ່ໄດ້ເລີ່ມ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>