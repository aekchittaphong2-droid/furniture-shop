<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// ດຶງສິນຄ້າໃນຕະກ້າ
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

if(empty($cartItems)) {
    header('Location: cart.php');
    exit();
}

// ກວດສອບສະຕ໋ອກ ແລະ ຄຳນວນຍອດລວມ
$total = 0;
$hasStockIssue = false;

foreach($cartItems as $item) {
    if($item['quantity'] > $item['stock']) {
        $hasStockIssue = true;
    }
    $total += $item['price'] * $item['quantity'];
}

// ດຶງຂໍ້ມູນຜູ້ໃຊ້
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST' && !$hasStockIssue) {
    $shipping_address = trim($_POST['shipping_address']);
    
    if(empty($shipping_address)) {
        $errors[] = 'ກະລຸນາປ້ອນທີ່ຢູ່ຈັດສົ່ງ';
    }
    
    if(empty($errors)) {
        // ເລີ່ມ transaction
        $pdo->beginTransaction();
        
        try {
            // ສ້າງຄຳສັ່ງຊື້
            $stmt = $pdo->prepare("INSERT INTO orders (user_id, total_amount, shipping_address) VALUES (?, ?, ?)");
            $stmt->execute([$_SESSION['user_id'], $total, $shipping_address]);
            $order_id = $pdo->lastInsertId();
            
            // ເພີ່ມລາຍລະອຽດຄຳສັ່ງຊື້
            foreach($cartItems as $item) {
                $stmt = $pdo->prepare("INSERT INTO order_items (order_id, product_id, quantity, price) VALUES (?, ?, ?, ?)");
                $stmt->execute([$order_id, $item['product_id'], $item['quantity'], $item['price']]);
                
                // ຫຼຸດສະຕ໋ອກ
                $stmt = $pdo->prepare("UPDATE products SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['product_id']]);
            }
            
            // ລ້າງຕະກ້າ
            $stmt = $pdo->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$_SESSION['user_id']]);
            
            $pdo->commit();
            
            // ໄປໜ້າສຳເລັດ
            header("Location: order-success.php?order_id=$order_id");
            exit();
            
        } catch(Exception $e) {
            $pdo->rollBack();
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການສ້າງຄຳສັ່ງຊື້';
        }
    }
}

$pageTitle = 'ຊຳລະເງິນ';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="mb-4">ຊຳລະເງິນ</h2>
    
    <?php if($hasStockIssue): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-triangle"></i> ມີບາງສິນຄ້າໃນຕະກ້າຂອງທ່ານທີ່ມີຈຳນວນເກີນກວ່າສະຕ໋ອກ. ກະລຸນາກັບໄປແກ້ໄຂຕະກ້າຂອງທ່ານ.
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
    
    <div class="row">
        <div class="col-lg-8">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ຂໍ້ມູນການຈັດສົ່ງ</h5>
                </div>
                <div class="card-body">
                    <form method="POST" class="needs-validation" novalidate>
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">ຊື່ແທ້</label>
                                <input type="text" class="form-control" value="<?php echo escape($user['full_name']); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">ເບີໂທລະສັບ</label>
                                <input type="text" class="form-control" value="<?php echo escape($user['phone']); ?>" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ອີເມວ</label>
                            <input type="email" class="form-control" value="<?php echo escape($user['email']); ?>" readonly>
                        </div>
                        
                        <div class="mb-3">
                            <label for="shipping_address" class="form-label">ທີ່ຢູ່ຈັດສົ່ງ <span class="text-danger">*</span></label>
                            <textarea class="form-control" id="shipping_address" name="shipping_address" rows="3" required><?php echo isset($_POST['shipping_address']) ? escape($_POST['shipping_address']) : escape($user['address']); ?></textarea>
                            <div class="invalid-feedback">
                                ກະລຸນາປ້ອນທີ່ຢູ່ຈັດສົ່ງ
                            </div>
                        </div>
                        
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> ການຊຳລະເງິນ: ຈ່າຍເງິນສົດເມື່ອໄດ້ຮັບສິນຄ້າ (COD)
                        </div>
                        
                        <?php if(!$hasStockIssue): ?>
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-check"></i> ຢືນຢັນຄຳສັ່ງຊື້
                        </button>
                        <?php endif; ?>
                        
                        <a href="cart.php" class="btn btn-secondary btn-lg">
                            <i class="fas fa-arrow-left"></i> ກັບໄປຕະກ້າ
                        </a>
                    </form>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ສະຫຼຸບຄຳສັ່ງຊື້</h5>
                </div>
                <div class="card-body">
                    <?php foreach($cartItems as $item): ?>
                    <div class="d-flex justify-content-between mb-2">
                        <div>
                            <small><?php echo escape($item['name']); ?></small>
                            <small class="text-muted">x<?php echo $item['quantity']; ?></small>
                        </div>
                        <small><?php echo formatPrice($item['price'] * $item['quantity']); ?></small>
                    </div>
                    <?php endforeach; ?>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>ລວມສິນຄ້າ:</span>
                        <span><?php echo formatPrice($total); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span>ຄ່າຈັດສົ່ງ:</span>
                        <span>ຟຣີ</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between">
                        <h5>ຍອດລວມທັງໝົດ:</h5>
                        <h5 class="text-success"><?php echo formatPrice($total); ?></h5>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>