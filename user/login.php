<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(isLoggedIn()) {
    header('Location: index.php');
    exit();
}

$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    
    $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $username]);
    $user = $stmt->fetch();
    
    if($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['full_name'] = $user['full_name'];
        $_SESSION['role'] = $user['role'];
        
        if($user['role'] == 'admin') {
            header('Location: ../admin/dashboard.php');
        } else {
            header('Location: index.php');
        }
        exit();
    } else {
        $error = 'ຊື່ຜູ້ໃຊ້ ຫຼື ລະຫັດຜ່ານບໍ່ຖືກຕ້ອງ';
    }
}

$pageTitle = 'ເຂົ້າສູ່ລະບົບ';
include '../includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow">
                <div class="card-body p-5">
                    <h3 class="text-center mb-4">ເຂົ້າສູ່ລະບົບ</h3>
                    
                    <?php if($error): ?>
                    <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="mb-3">
                            <label for="username" class="form-label">ຊື່ຜູ້ໃຊ້ ຫຼື ອີເມວ</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                            <div class="invalid-feedback">
                                ກະລຸນາປ້ອນຊື່ຜູ້ໃຊ້ ຫຼື ອີເມວ
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="password" class="form-label">ລະຫັດຜ່ານ</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                            <div class="invalid-feedback">
                                ກະລຸນາປ້ອນລະຫັດຜ່ານ
                            </div>
                        </div>
                        
                        <div class="mb-3 form-check">
                            <input type="checkbox" class="form-check-input" id="remember">
                            <label class="form-check-label" for="remember">
                                ຈື່ຂ້ອຍໄວ້
                            </label>
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100 mb-3">ເຂົ້າສູ່ລະບົບ</button>
                        
                        <div class="text-center">
                            <p>ຍັງບໍ່ມີບັນຊີ? <a href="register.php">ລົງທະບຽນ</a></p>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

