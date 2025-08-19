<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// Update order status
if(isset($_POST['update_status'])) {
    $order_id = (int)$_POST['order_id'];
    $status = $_POST['status'];
    
    $stmt = $pdo->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$status, $order_id]);
    
    header('Location: orders.php?updated=1');
    exit();
}

// ດຶງຄຳສັ່ງຊື້ທັງໝົດ
$stmt = $pdo->query("SELECT o.*, u.full_name 
                     FROM orders o 
                     JOIN users u ON o.user_id = u.id 
                     ORDER BY o.order_date DESC");
$orders = $stmt->fetchAll();

$pageTitle = 'ຈັດການຄຳສັ່ງຊື້';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">ຈັດການຄຳສັ່ງຊື້</h1>
    
    <?php if(isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ອັບເດດສະຖານະຄຳສັ່ງຊື້ສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ເລກທີ</th>
                            <th>ລູກຄ້າ</th>
                            <th>ຍອດເງິນ</th>
                            <th>ສະຖານະ</th>
                            <th>ວັນທີ</th>
                            <th>ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($orders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo escape($order['full_name']); ?></td>
                            <td><?php echo formatPrice($order['total_amount']); ?></td>
                            <td>
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                    <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                                        <option value="pending" <?php echo $order['status'] == 'pending' ? 'selected' : ''; ?>>ລໍຖ້າ</option>
                                        <option value="processing" <?php echo $order['status'] == 'processing' ? 'selected' : ''; ?>>ກຳລັງດຳເນີນການ</option>
                                        <option value="completed" <?php echo $order['status'] == 'completed' ? 'selected' : ''; ?>>ສຳເລັດ</option>
                                        <option value="cancelled" <?php echo $order['status'] == 'cancelled' ? 'selected' : ''; ?>>ຍົກເລີກ</option>
                                    </select>
                                    <input type="hidden" name="update_status" value="1">
                                </form>
                            </td>
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td>
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i> ເບິ່ງລາຍລະອຽດ
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>