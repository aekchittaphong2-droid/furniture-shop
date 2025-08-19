<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// ດຶງຄຳສັ່ງຊື້ຂອງຜູ້ໃຊ້
$stmt = $pdo->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
$stmt->execute([$_SESSION['user_id']]);
$orders = $stmt->fetchAll();

$pageTitle = 'ຄຳສັ່ງຊື້ຂອງຂ້ອຍ';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="mb-4">ຄຳສັ່ງຊື້ຂອງຂ້ອຍ</h2>
    
    <?php if(empty($orders)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
        <h4>ທ່ານຍັງບໍ່ມີຄຳສັ່ງຊື້</h4>
        <p>ເລີ່ມຊື້ສິນຄ້າກັບພວກເຮົາ</p>
        <a href="products.php" class="btn btn-primary">ເລືອກຊື້ສິນຄ້າ</a>
    </div>
    <?php else: ?>
    <div class="table-responsive">
        <table class="table table-hover">
            <thead>
                <tr>
                    <th>ເລກທີ</th>
                    <th>ວັນທີ</th>
                    <th>ຍອດລວມ</th>
                    <th>ສະຖານະ</th>
                    <th>ລາຍລະອຽດ</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach($orders as $order): ?>
                <tr>
                    <td>#<?php echo $order['id']; ?></td>
                    <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                    <td><?php echo formatPrice($order['total_amount']); ?></td>
                    <td>
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
                    </td>
                    <td>
                        <button class="btn btn-sm btn-info" onclick="viewOrderDetail(<?php echo $order['id']; ?>)">
                            <i class="fas fa-eye"></i> ເບິ່ງລາຍລະອຽດ
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>
</div>

<!-- Order Detail Modal -->
<div class="modal fade" id="orderDetailModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">ລາຍລະອຽດຄຳສັ່ງຊື້</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="orderDetailContent">
                <!-- Content will be loaded here -->
            </div>
        </div>
    </div>
</div>

<script>
function viewOrderDetail(orderId) {
    $('#orderDetailContent').html('<div class="text-center"><i class="fas fa-spinner fa-spin fa-3x"></i></div>');
    $('#orderDetailModal').modal('show');
    
    $.get('../ajax/get_order_detail.php', { order_id: orderId }, function(data) {
        $('#orderDetailContent').html(data);
    });
}
</script>

<?php include '../includes/footer.php'; ?>