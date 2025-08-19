<?php
// สคริปต์ตรวจสอบระบบ
echo "<h2>ตรวจสอบระบบ</h2>";

// ตรวจสอบ PHP extensions
echo "<h3>PHP Extensions:</h3>";
$required_extensions = ['pdo', 'pdo_mysql', 'gd', 'fileinfo'];
foreach($required_extensions as $ext) {
    if(extension_loaded($ext)) {
        echo "<p style='color: green;'>✓ $ext</p>";
    } else {
        echo "<p style='color: red;'>✗ $ext (จำเป็น)</p>";
    }
}

// ตรวจสอบการตั้งค่า PHP
echo "<h3>PHP Settings:</h3>";
echo "<p>Upload Max Filesize: " . ini_get('upload_max_filesize') . "</p>";
echo "<p>Post Max Size: " . ini_get('post_max_size') . "</p>";
echo "<p>Max Execution Time: " . ini_get('max_execution_time') . " seconds</p>";
echo "<p>Memory Limit: " . ini_get('memory_limit') . "</p>";

// ตรวจสอบโฟลเดอร์
echo "<h3>Directories:</h3>";
$dirs = ['assets', 'assets/images', 'assets/images/products'];
foreach($dirs as $dir) {
    if(file_exists($dir)) {
        if(is_writable($dir)) {
            echo "<p style='color: green;'>✓ $dir (เขียนได้)</p>";
        } else {
            echo "<p style='color: orange;'>⚠ $dir (อ่านได้แต่เขียนไม่ได้)</p>";
        }
    } else {
        echo "<p style='color: red;'>✗ $dir (ไม่มี)</p>";
    }
}

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
echo "<h3>Database Connection:</h3>";
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ เชื่อมต่อฐานข้อมูลสำเร็จ</p>";
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='create_folders.php'>สร้างโฟลเดอร์</a> | <a href='admin/dashboard.php'>Admin Dashboard</a></p>";
?>