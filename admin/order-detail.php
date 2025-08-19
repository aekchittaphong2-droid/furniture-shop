<?php
// admin/order-detail.php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: orders.php');
    exit();
}

$order_id = (int)$_GET['id'];

// ດຶງຂໍ້ມູນຄຳສັ່ງຊື້
$stmt = $pdo->prepare("SELECT o.*, u.full_name, u.email, u.phone, u.address as user_address 
                       FROM orders o 
                       JOIN users u ON o.user_id = u.id 
                       WHERE o.id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch();

if(!$order) {
    header('Location: orders.php');
    exit();
}

// ດຶງລາຍລະອຽດສິນຄ້າ
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$pageTitle = 'ລາຍລະອຽດຄຳສັ່ງຊື້ #' . $order_id;
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">ລາຍລະອຽດຄຳສັ່ງຊື້ #<?php echo $order_id; ?></h1>
        <a href="orders.php" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> ກັບໄປ
        </a>
    </div>
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Order Items -->
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ລາຍການສິນຄ້າ</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ສິນຄ້າ</th>
                                    <th>ລາຄາ</th>
                                    <th>ຈຳນວນ</th>
                                    <th>ລວມ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($items as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                                 width="60" class="me-3 rounded">
                                            <?php echo escape($item['name']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td><?php echo $item['quantity']; ?></td>
                                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th colspan="3" class="text-end">ຍອດລວມ:</th>
                                    <th><?php echo formatPrice($order['total_amount']); ?></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <!-- Order Info -->
            <div class="card mb-4">
                <div class="card-header bg-info text-white">
                    <h5 class="mb-0">ຂໍ້ມູນຄຳສັ່ງຊື້</h5>
                </div>
                <div class="card-body">
                    <p><strong>ເລກທີຄຳສັ່ງຊື້:</strong> #<?php echo $order['id']; ?></p>
                    <p><strong>ວັນທີ:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
                    <p><strong>ສະຖານະ:</strong>
                        <?php
                        $statusClass = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            'cancelled' => 'danger'
                        ];
                        $statusText = [
                            'pending' => 'ລໍຖ້າ',
                            'processing' => 'ກຳລັງດຳເນີນການ',
                            'completed' => 'ສຳເລັດ',
                            'cancelled' => 'ຍົກເລີກ'
                        ];
                        ?>
                        <span class="badge bg-<?php echo $statusClass[$order['status']]; ?>">
                            <?php echo $statusText[$order['status']]; ?>
                        </span>
                    </p>
                    
                    <!-- Update Status Form -->
                    <form method="POST" action="orders.php">
                        <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                        <div class="mb-3">
                            <label class="form-label">ອັບເດດສະຖານະ:</label>
                            <select name="status" class="form-select">
                                <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>ລໍຖ້າ</option>
                                <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>ກຳລັງດຳເນີນການ</option>
                                <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>ສຳເລັດ</option>
                                <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>ຍົກເລີກ</option>
                            </select>
                        </div>
                        <button type="submit" name="update_status" class="btn btn-primary w-100">
                            <i class="fas fa-save"></i> ບັນທຶກ
                        </button>
                    </form>
                </div>
            </div>
            
            <!-- Customer Info -->
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">ຂໍ້ມູນລູກຄ້າ</h5>
                </div>
                <div class="card-body">
                    <p><strong>ຊື່:</strong> <?php echo escape($order['full_name']); ?></p>
                    <p><strong>ອີເມວ:</strong> <?php echo escape($order['email']); ?></p>
                    <p><strong>ເບີໂທ:</strong> <?php echo escape($order['phone']); ?></p>
                    <p><strong>ທີ່ຢູ່ຈັດສົ່ງ:</strong><br>
                        <?php echo nl2br(escape($order['shipping_address'])); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>