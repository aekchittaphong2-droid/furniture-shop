<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// ດຶງຂໍ້ມູນຜູ້ໃຊ້
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$success = '';
$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $full_name = trim($_POST['full_name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone']);
    $address = trim($_POST['address']);
    
    // ກວດສອບການປ່ຽນລະຫັດຜ່ານ
    $change_password = false;
    if(!empty($_POST['new_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if(!password_verify($current_password, $user['password'])) {
            $errors[] = 'ລະຫັດຜ່ານປັດຈຸບັນບໍ່ຖືກຕ້ອງ';
        } elseif(strlen($new_password) < 6) {
            $errors[] = 'ລະຫັດຜ່ານໃໝ່ຕ້ອງມີຢ່າງໜ້ອຍ 6 ຕົວອັກສອນ';
        } elseif($new_password !== $confirm_password) {
            $errors[] = 'ລະຫັດຜ່ານໃໝ່ບໍ່ກົງກັນ';
        } else {
            $change_password = true;
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        }
    }
    
    // ກວດສອບອີເມວຊ້ຳ
    if($email !== $user['email']) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->execute([$email, $_SESSION['user_id']]);
        if($stmt->fetch()) {
            $errors[] = 'ອີເມວນີ້ມີຜູ້ໃຊ້ແລ້ວ';
        }
    }
    
    if(empty($errors)) {
        if($change_password) {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ?, password = ? WHERE id = ?");
            $result = $stmt->execute([$full_name, $email, $phone, $address, $hashed_password, $_SESSION['user_id']]);
        } else {
            $stmt = $pdo->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, address = ? WHERE id = ?");
            $result = $stmt->execute([$full_name, $email, $phone, $address, $_SESSION['user_id']]);
        }
        
        if($result) {
            $_SESSION['full_name'] = $full_name;
            $success = 'ອັບເດດຂໍ້ມູນສຳເລັດ!';
            
            // ໂຫຼດຂໍ້ມູນໃໝ່
            $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            $user = $stmt->fetch();
        }
    }
}

$pageTitle = 'ໂປຣໄຟລ໌ຂອງຂ້ອຍ';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row">
        <!-- Sidebar -->
        <div class="col-md-3">
            <div class="card">
                <div class="card-body text-center">
                    <i class="fas fa-user-circle fa-5x text-primary mb-3"></i>
                    <h5><?php echo escape($user['full_name']); ?></h5>
                    <p class="text-muted"><?php echo escape($user['email']); ?></p>
                </div>
                <div class="list-group list-group-flush">
                    <a href="profile.php" class="list-group-item active">
                        <i class="fas fa-user"></i> ຂໍ້ມູນສ່ວນຕົວ
                    </a>
                    <a href="orders.php" class="list-group-item">
                        <i class="fas fa-shopping-bag"></i> ຄຳສັ່ງຊື້ຂອງຂ້ອຍ
                    </a>
                    <a href="logout.php" class="list-group-item text-danger">
                        <i class="fas fa-sign-out-alt"></i> ອອກຈາກລະບົບ
                    </a>
                </div>
            </div>
        </div>
        
        <!-- Profile Form -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ແກ້ໄຂຂໍ້ມູນສ່ວນຕົວ</h5>
                </div>
                <div class="card-body">
                    <?php if($success): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <?php echo $success; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
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
                    
                    <form method="POST">
                        <h6 class="mb-3">ຂໍ້ມູນພື້ນຖານ</h6>
                        
                        <div class="mb-3">
                            <label for="username" class="form-label">ຊື່ຜູ້ໃຊ້</label>
                            <input type="text" class="form-control" id="username" value="<?php echo escape($user['username']); ?>" readonly>
                            <small class="text-muted">ຊື່ຜູ້ໃຊ້ບໍ່ສາມາດປ່ຽນໄດ້</small>
                        </div>
                        
                        <div class="mb-3">
                            <label for="full_name" class="form-label">ຊື່ແທ້</label>
                            <input type="text" class="form-control" id="full_name" name="full_name" 
                                   value="<?php echo escape($user['full_name']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="email" class="form-label">ອີເມວ</label>
                            <input type="email" class="form-control" id="email" name="email" 
                                   value="<?php echo escape($user['email']); ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="phone" class="form-label">ເບີໂທລະສັບ</label>
                            <input type="tel" class="form-control" id="phone" name="phone" 
                                   value="<?php echo escape($user['phone']); ?>">
                        </div>
                        
                        <div class="mb-3">
                            <label for="address" class="form-label">ທີ່ຢູ່</label>
                            <textarea class="form-control" id="address" name="address" rows="3"><?php echo escape($user['address']); ?></textarea>
                        </div>
                        
                        <hr>
                        
                        <h6 class="mb-3">ປ່ຽນລະຫັດຜ່ານ (ເວັ້ນວ່າງໄວ້ຖ້າບໍ່ຕ້ອງການປ່ຽນ)</h6>
                        
                        <div class="mb-3">
                            <label for="current_password" class="form-label">ລະຫັດຜ່ານປັດຈຸບັນ</label>
                            <input type="password" class="form-control" id="current_password" name="current_password">
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="new_password" class="form-label">ລະຫັດຜ່ານໃໝ່</label>
                                <input type="password" class="form-control" id="new_password" name="new_password">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="confirm_password" class="form-label">ຢືນຢັນລະຫັດຜ່ານໃໝ່</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> ບັນທຶກການປ່ຽນແປງ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>