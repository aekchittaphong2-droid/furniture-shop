<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$errors = [];
$success = false;

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $full_name = trim($_POST['full_name']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // Validation
    if(empty($username)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້';
    }
    
    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'ອີເມວບໍ່ຖືກຕ້ອງ';
    }
    
    if(strlen($password) < 6) {
        $errors[] = 'ລະຫັດຜ່ານຕ້ອງມີຢ່າງໜ້ອຍ 6 ຕົວອັກສອນ';
    }
    
    if($password !== $confirm_password) {
        $errors[] = 'ລະຫັດຜ່ານບໍ່ກົງກັນ';
    }
    
    // Check if username exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if($stmt->fetch()) {
        $errors[] = 'ຊື່ຜູ້ໃຊ້ນີ້ມີແລ້ວ';
    }
    
    // Check if email exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if($stmt->fetch()) {
        $errors[] = 'ອີເມວນີ້ມີແລ້ວ';
    }
    
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name, phone, address) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        
        if($stmt->execute([$username, $hashed_password, $email, $full_name, $phone, $address])) {
            $success = true;
        } else {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການລົງທະບຽນ';
        }
    }
}

$pageTitle = 'ລົງທະບຽນ';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-6">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">ລົງທະບຽນບັນຊີໃໝ່</h3>
                    
                    <?php if($success): ?>
                    <div class="alert alert-success">
                        ລົງທະບຽນສຳເລັດ! <a href="login.php">ເຂົ້າສູ່ລະບົບ</a>
                    </div>
                    <?php endif; ?>
                    
                    <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="username" class="form-label">ຊື່ຜູ້ໃຊ້ <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="username" name="username" 
                                       value="<?php echo isset($_POST['username']) ? escape($_POST['username']) : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="email" class="form-label">ອີເມວ <span class="text-danger">*</span></label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo isset($_POST['email']) ? escape($_POST['email']) : ''; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ຊື່ແທ້ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo isset($_POST['full_name']) ? escape($_POST['full_name']) : ''; ?>" required>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="password" class="form-label">ລະຫັດຜ່ານ <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">ຢືນຢັນລະຫັດຜ່ານ <span class="text-danger">*</span></label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">ເບີໂທລະສັບ</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo isset($_POST['phone']) ? escape($_POST['phone']) : ''; ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">ທີ່ຢູ່</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo isset($_POST['address']) ? escape($_POST['address']) : ''; ?></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">ລົງທະບຽນ</button>
                        
                        <div class="text-center">
                            <p>ມີບັນຊີແລ້ວ? <a href="login.php">ເຂົ້າສູ່ລະບົບ</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>