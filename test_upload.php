<?php
// ไฟล์ทดสอบการอัปโหลด
require_once 'includes/functions.php';

echo "<h2>🧪 ทดสอบระบบอัปโหลด</h2>";

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['test_image'])) {
    echo "<h3>📤 กำลังทดสอบอัปโหลด...</h3>";
    
    $result = uploadImage($_FILES['test_image']);
    
    if ($result) {
        echo "<p style='color: green;'>✅ อัปโหลดสำเร็จ!</p>";
        echo "<p>ชื่อไฟล์: <strong>$result</strong></p>";
        echo "<p>ตำแหน่งไฟล์: assets/images/products/$result</p>";
        
        // แสดงรูปภาพ
        $image_path = "assets/images/products/$result";
        if (file_exists($image_path)) {
            echo "<p>รูปภาพที่อัปโหลด:</p>";
            echo "<img src='$image_path' style='max-width: 300px; border: 1px solid #ddd; padding: 10px;'>";
        }
    } else {
        echo "<p style='color: red;'>❌ อัปโหลดล้มเหลว!</p>";
        echo "<p>กรุณาตรวจสอบ:</p>";
        echo "<ul>";
        echo "<li>โฟลเดอร์ assets/images/products มีอยู่หรือไม่</li>";
        echo "<li>โฟลเดอร์มีสิทธิ์เขียนไฟล์หรือไม่</li>";
        echo "<li>ไฟล์เป็นรูปภาพ (JPG, PNG, GIF) หรือไม่</li>";
        echo "<li>ขนาดไฟล์ไม่เกิน 5MB</li>";
        echo "</ul>";
    }
}
?>

<form method="POST" enctype="multipart/form-data" style="margin: 20px 0; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
    <h3>📁 เลือกไฟล์ทดสอบ:</h3>
    <p>
        <input type="file" name="test_image" accept="image/*" required style="margin: 10px 0;">
    </p>
    <p>
        <button type="submit" style="background: #007bff; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer;">
            🚀 ทดสอบอัปโหลด
        </button>
    </p>
    <small style="color: #666;">
        รองรับไฟล์: JPG, PNG, GIF (ขนาดไม่เกิน 5MB)
    </small>
</form>

<hr>
<p><a href="setup_folders.php">🔧 ตั้งค่าระบบ</a> | <a href="admin/add-product.php">➕ เพิ่มสินค้า</a></p>