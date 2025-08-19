<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isset($_GET['order_id'])) {
    header('Location: index.php');
    exit();
}

$order_id = (int)$_GET['order_id'];

// ດຶງຂໍ້ມູນຄຳສັ່ງຊື້
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if(!$order) {
    header('Location: index.php');
    exit();
}

$pageTitle = 'ສັ່ງຊື້ສຳເລັດ';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-md-8 text-center">
            <div class="mb-4">
                <i class="fas fa-check-circle text-success" style="font-size: 5rem;"></i>
            </div>
            
            <h1 class="h2 mb-3">ສັ່ງຊື້ສຳເລັດ!</h1>
            
            <p class="lead mb-4">
                ຂອບໃຈທີ່ໃຊ້ບໍລິການຂອງພວກເຮົາ. ຄຳສັ່ງຊື້ຂອງທ່ານໄດ້ຮັບການບັນທຶກແລ້ວ.
            </p>
            
            <div class="card mb-4">
                <div class="card-body">
                    <h5 class="card-title">ລາຍລະອຽດຄຳສັ່ງຊື້</h5>
                    <p><strong>ເລກທີຄຳສັ່ງຊື້:</strong> #<?php echo $order_id; ?></p>
                    <p><strong>ວັນທີ:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                    <p><strong>ຍອດລວມ:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
                    <p><strong>ສະຖານະ:</strong> <span class="badge bg-warning">ລໍຖ້າການຢືນຢັນ</span></p>
                </div>
            </div>
            
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ພວກເຮົາຈະຕິດຕໍ່ທ່ານເພື່ອຢືນຢັນຄຳສັ່ງຊື້ໃນໄວໆນີ້
            </div>
            
            <div class="d-flex gap-2 justify-content-center">
                <a href="orders.php" class="btn btn-primary">
                    <i class="fas fa-list"></i> ເບິ່ງຄຳສັ່ງຊື້ຂອງຂ້ອຍ
                </a>
                <a href="products.php" class="btn btn-outline-primary">
                    <i class="fas fa-shopping-bag"></i> ຊື້ສິນຄ້າຕໍ່
                </a>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>