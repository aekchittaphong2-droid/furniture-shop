<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isset($_GET['order_id'])) {
    exit('Unauthorized');
}

$order_id = (int)$_GET['order_id'];

// ດຶງຂໍ້ມູນຄຳສັ່ງຊື້
$stmt = $pdo->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$stmt->execute([$order_id, $_SESSION['user_id']]);
$order = $stmt->fetch();

if(!$order) {
    exit('Order not found');
}

// ດຶງລາຍລະອຽດສິນຄ້າ
$stmt = $pdo->prepare("SELECT oi.*, p.name, p.image 
                       FROM order_items oi 
                       JOIN products p ON oi.product_id = p.id 
                       WHERE oi.order_id = ?");
$stmt->execute([$order_id]);
$items = $stmt->fetchAll();

$statusText = [
    'pending' => 'ລໍຖ້າ',
    'processing' => 'ກຳລັງດຳເນີນການ',
    'completed' => 'ສຳເລັດ',
    'cancelled' => 'ຍົກເລີກ'
];
?>

<div class="order-detail">
    <div class="row mb-3">
        <div class="col-md-6">
            <p><strong>ເລກທີຄຳສັ່ງຊື້:</strong> #<?php echo $order['id']; ?></p>
            <p><strong>ວັນທີ:</strong> <?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
        </div>
        <div class="col-md-6">
            <p><strong>ສະຖານະ:</strong> <?php echo $statusText[$order['status']]; ?></p>
            <p><strong>ຍອດລວມ:</strong> <?php echo formatPrice($order['total_amount']); ?></p>
        </div>
    </div>
    
    <h6>ທີ່ຢູ່ຈັດສົ່ງ:</h6>
    <p><?php echo nl2br(escape($order['shipping_address'])); ?></p>
    
    <h6 class="mt-3">ລາຍການສິນຄ້າ:</h6>
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
                                 width="50" class="me-2 rounded">
                            <?php echo escape($item['name']); ?>
                        </div>
                    </td>
                    <td><?php echo formatPrice($item['price']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo formatPrice($item['price'] * $item['quantity']); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>