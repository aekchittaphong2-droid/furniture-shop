<?php
// สคริปต์สำหรับสร้างโฟลเดอร์ที่จำเป็น
echo "<h2>กำลังสร้างโฟลเดอร์...</h2>";

$folders = [
    'assets',
    'assets/images', 
    'assets/images/products'
];

foreach($folders as $folder) {
    if(!file_exists($folder)) {
        if(mkdir($folder, 0755, true)) {
            echo "<p style='color: green;'>✓ สร้างโฟลเดอร์: $folder</p>";
        } else {
            echo "<p style='color: red;'>✗ ไม่สามารถสร้างโฟลเดอร์: $folder</p>";
        }
    } else {
        echo "<p style='color: blue;'>→ โฟลเดอร์มีอยู่แล้ว: $folder</p>";
    }
}

// สร้างรูป default
$width = 300;
$height = 250;

if (extension_loaded('gd')) {
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
    
    if(imagejpeg($image, 'assets/images/products/default.jpg', 80)) {
        echo "<p style='color: green;'>✓ สร้างรูป default: assets/images/products/default.jpg</p>";
    } else {
        echo "<p style='color: red;'>✗ ไม่สามารถสร้างรูป default ได้</p>";
    }
    
    imagedestroy($image);
} else {
    echo "<p style='color: orange;'>⚠ GD extension ไม่ได้ติดตั้ง - ข้ามการสร้างรูป default</p>";
}

echo "<h3>เสร็จสิ้น!</h3>";
echo "<p><a href='admin/dashboard.php'>ไปที่ Admin Dashboard</a></p>";
echo "<p><a href='user/index.php'>ไปที่หน้าหลัก</a></p>";
?>