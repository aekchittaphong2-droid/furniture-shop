<?php
// ເລີ່ມ session ຖ້າຍັງບໍ່ໄດ້ເລີ່ມ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ຟັງຊັນກວດສອບການເຂົ້າສູ່ລະບົບ
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// ຟັງຊັນກວດສອບສິດ Admin
function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] == 'admin';
}

// ຟັງຊັນປ້ອງກັນ XSS
function escape($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// ຟັງຊັນອັບໂຫຼດຮູບພາບ
function uploadImage($file, $target_dir = "../assets/images/products/") {
    // กำหนด path ให้ถูกต้องตามตำแหน่งไฟล์
    $current_dir = dirname(__FILE__);
    $root_dir = dirname($current_dir);
    
    // สร้าง absolute path
    if (strpos($target_dir, '../') === 0) {
        $target_dir = $root_dir . '/' . substr($target_dir, 3);
    }
    
    // สร้าง directory ถ้ายังไม่มี (สร้างทีละขั้น)
    $dirs_to_create = [
        $root_dir . '/assets',
        $root_dir . '/assets/images',
        $root_dir . '/assets/images/products'
    ];
    
    foreach ($dirs_to_create as $dir) {
        if (!file_exists($dir)) {
            if (!mkdir($dir, 0755, true)) {
                error_log("Failed to create directory: " . $dir);
                return false;
            }
        }
    }
    
    // สร้างชื่อไฟล์ใหม่
    $file_extension = strtolower(pathinfo($file["name"], PATHINFO_EXTENSION));
    $new_filename = time() . "_" . uniqid() . "." . $file_extension;
    $target_file = $target_dir . "/" . $new_filename;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
    
    // ກວດສອບວ່າເປັນຮູບພາບແທ້
    $check = getimagesize($file["tmp_name"]);
    if($check === false) {
        return false;
    }
    
    // ກວດສອບຂະໜາດໄຟລ໌ (5MB)
    if ($file["size"] > 5000000) {
        return false;
    }
    
    // ອະນຸຍາດສະເພາະໄຟລ໌ຮູບພາບ
    if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
        return false;
    }
    
    if (move_uploaded_file($file["tmp_name"], $target_file)) {
        // ส่งคืนเฉพาะชื่อไฟล์
        return $new_filename;
    } else {
        // Log error สำหรับ debugging
        error_log("Failed to move uploaded file from " . $file["tmp_name"] . " to " . $target_file);
        error_log("Upload error code: " . $file["error"]);
        return false;
    }
}

// ຟັງຊັນຈັດຮູບແບບລາຄາ
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ກີບ';
}

// ຟັງຊັນສ້າງ URL (ປັບປຸງໃໝ່)
function url($path) {
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https://' : 'http://';
    $host = $_SERVER['HTTP_HOST'];
    $base = dirname($_SERVER['SCRIPT_NAME']);
    
    // ຖ້າຢູ່ໃນ subfolder ໃຫ້ລຶບ /user ຫຼື /admin ອອກ
    $base = preg_replace('/(\/user|\/admin)$/', '', $base);
    
    return $protocol . $host . $base . '/' . $path;
}

// ຟັງຊັນສ້າງ breadcrumb
function generateBreadcrumb($items) {
    $html = '<nav aria-label="breadcrumb"><ol class="breadcrumb">';
    
    foreach($items as $index => $item) {
        if($index == count($items) - 1) {
            $html .= '<li class="breadcrumb-item active">' . escape($item['text']) . '</li>';
        } else {
            $html .= '<li class="breadcrumb-item"><a href="' . $item['link'] . '">' . escape($item['text']) . '</a></li>';
        }
    }
    
    $html .= '</ol></nav>';
    return $html;
}

// ຟັງຊັນແຈ້ງເຕືອນ
function setAlert($message, $type = 'success') {
    $_SESSION['alert'] = [
        'message' => $message,
        'type' => $type
    ];
}

// ຟັງຊັນສະແດງແຈ້ງເຕືອນ
function showAlert() {
    if(isset($_SESSION['alert'])) {
        $alert = $_SESSION['alert'];
        unset($_SESSION['alert']);
        
        return '<div class="alert alert-' . $alert['type'] . ' alert-dismissible fade show" role="alert">' .
               escape($alert['message']) .
               '<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>';
    }
    return '';
}

// ຟັງຊັນຄຳນວນສ່ວນຫຼຸດ
function calculateDiscount($price, $discountPercent) {
    return $price - ($price * $discountPercent / 100);
}

// ຟັງຊັນກວດສອບ email
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// ຟັງຊັນກວດສອບເບີໂທ
function isValidPhone($phone) {
    // ຮູບແບບເບີໂທລາວ: 020XXXXXXXX, 030XXXXXXXX, etc.
    return preg_match('/^(020|030|021|022|023)[0-9]{8}$/', $phone);
}

// ຟັງຊັນຕັດຂໍ້ຄວາມ
function truncateText($text, $length = 100, $suffix = '...') {
    if(mb_strlen($text) <= $length) {
        return $text;
    }
    return mb_substr($text, 0, $length) . $suffix;
}

// ຟັງຊັນສ້າງ random string
function generateRandomString($length = 10) {
    return substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1,$length);
}

// ຟັງຊັນ redirect
function redirect($url) {
    header("Location: $url");
    exit();
}

// ຟັງຊັນກວດສອບ CSRF token
function generateCSRFToken() {
    if(empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}
?>
