<?php
// สคริปต์สำหรับสร้างโฟลเดอร์และตั้งค่าระบบ
echo "<h2>🔧 ตั้งค่าระบบ Furniture Shop</h2>";

// สร้างโฟลเดอร์ที่จำเป็น
$folders = [
    'assets',
    'assets' . DIRECTORY_SEPARATOR . 'images',
    'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products',
    'assets' . DIRECTORY_SEPARATOR . 'css',
    'assets' . DIRECTORY_SEPARATOR . 'js'
];

echo "<h3>📁 สร้างโฟลเดอร์:</h3>";
foreach($folders as $folder) {
    $full_path = __DIR__ . DIRECTORY_SEPARATOR . $folder;
    
    if(!file_exists($full_path)) {
        if(mkdir($full_path, 0755, true)) {
            echo "<p style='color: green;'>✓ สร้างโฟลเดอร์: $folder</p>";
        } else {
            echo "<p style='color: red;'>✗ ไม่สามารถสร้างโฟลเดอร์: $folder</p>";
        }
    } else {
        echo "<p style='color: blue;'>→ โฟลเดอร์มีอยู่แล้ว: $folder</p>";
    }
}

// สร้างรูป default
echo "<h3>🖼️ สร้างรูปภาพ Default:</h3>";
$default_image_path = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products' . DIRECTORY_SEPARATOR . 'default.jpg';

if (extension_loaded('gd')) {
    $width = 300;
    $height = 250;
    
    $image = imagecreate($width, $height);
    $bg_color = imagecolorallocate($image, 224, 224, 224);
    $text_color = imagecolorallocate($image, 153, 153, 153);
    
    $text = "No Image";
    $font_size = 5;
    $text_width = imagefontwidth($font_size) * strlen($text);
    $text_height = imagefontheight($font_size);
    $x = ($width - $text_width) / 2;
    $y = ($height - $text_height) / 2;
    
    imagestring($image, $font_size, $x, $y, $text, $text_color);
    
    if(imagejpeg($image, $default_image_path, 80)) {
        echo "<p style='color: green;'>✓ สร้างรูป default.jpg สำเร็จ</p>";
    } else {
        echo "<p style='color: red;'>✗ ไม่สามารถสร้างรูป default.jpg ได้</p>";
    }
    
    imagedestroy($image);
} else {
    echo "<p style='color: orange;'>⚠ GD extension ไม่ได้ติดตั้ง - ข้ามการสร้างรูป default</p>";
}

// ตรวจสอบสิทธิ์การเขียนไฟล์
echo "<h3>🔐 ตรวจสอบสิทธิ์:</h3>";
$upload_dir = __DIR__ . DIRECTORY_SEPARATOR . 'assets' . DIRECTORY_SEPARATOR . 'images' . DIRECTORY_SEPARATOR . 'products';

if(is_writable($upload_dir)) {
    echo "<p style='color: green;'>✓ โฟลเดอร์ products สามารถเขียนไฟล์ได้</p>";
} else {
    echo "<p style='color: red;'>✗ โฟลเดอร์ products ไม่สามารถเขียนไฟล์ได้</p>";
    echo "<p>กรุณาตั้งค่าสิทธิ์ให้โฟลเดอร์: $upload_dir</p>";
}

// ตรวจสอบการตั้งค่า PHP
echo "<h3>⚙️ การตั้งค่า PHP:</h3>";
echo "<p>Upload Max Filesize: <strong>" . ini_get('upload_max_filesize') . "</strong></p>";
echo "<p>Post Max Size: <strong>" . ini_get('post_max_size') . "</strong></p>";
echo "<p>Max Execution Time: <strong>" . ini_get('max_execution_time') . " วินาที</strong></p>";
echo "<p>Memory Limit: <strong>" . ini_get('memory_limit') . "</strong></p>";

// ตรวจสอบการเชื่อมต่อฐานข้อมูล
echo "<h3>🗄️ ฐานข้อมูล:</h3>";
try {
    require_once 'config/database.php';
    echo "<p style='color: green;'>✓ เชื่อมต่อฐานข้อมูลสำเร็จ</p>";
    
    // ตรวจสอบตาราง
    $tables = ['users', 'categories', 'products', 'cart', 'orders', 'order_items'];
    foreach($tables as $table) {
        try {
            $stmt = $pdo->query("SELECT COUNT(*) FROM $table");
            $count = $stmt->fetchColumn();
            echo "<p style='color: green;'>✓ ตาราง $table: $count รายการ</p>";
        } catch(Exception $e) {
            echo "<p style='color: red;'>✗ ตาราง $table: ไม่พบหรือมีปัญหา</p>";
        }
    }
    
} catch(Exception $e) {
    echo "<p style='color: red;'>✗ เชื่อมต่อฐานข้อมูลล้มเหลว: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<h3>🚀 เสร็จสิ้นการตั้งค่า!</h3>";
echo "<p><a href='admin/dashboard.php' class='btn btn-primary'>ไปที่ Admin Dashboard</a></p>";
echo "<p><a href='user/index.php' class='btn btn-secondary'>ไปที่หน้าหลัก</a></p>";

// CSS สำหรับปุ่ม
echo "<style>
.btn {
    display: inline-block;
    padding: 10px 20px;
    margin: 5px;
    text-decoration: none;
    border-radius: 5px;
    color: white;
}
.btn-primary { background-color: #007bff; }
.btn-secondary { background-color: #6c757d; }
.btn:hover { opacity: 0.8; }
</style>";
?>