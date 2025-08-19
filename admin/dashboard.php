<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

// ກວດສອບສິດ Admin
if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// ດຶງສະຖິຕິ
$stats = [];

// ຈຳນວນສິນຄ້າ
$stats['products'] = $pdo->query("SELECT COUNT(*) FROM products")->fetchColumn();

// ຈຳນວນໝວດໝູ່
$stats['categories'] = $pdo->query("SELECT COUNT(*) FROM categories")->fetchColumn();

// ຈຳນວນຜູ້ໃຊ້
$stats['users'] = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'user'")->fetchColumn();

// ຈຳນວນຄຳສັ່ງຊື້
$stats['orders'] = $pdo->query("SELECT COUNT(*) FROM orders")->fetchColumn();

// ຄຳສັ່ງຊື້ໃໝ່
$stats['pending_orders'] = $pdo->query("SELECT COUNT(*) FROM orders WHERE status = 'pending'")->fetchColumn();

// ຍອດຂາຍລວມ
$stats['total_sales'] = $pdo->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'completed'")->fetchColumn();

// ຄຳສັ່ງຊື້ລ່າສຸດ
$recentOrders = $pdo->query("SELECT o.*, u.full_name 
                             FROM orders o 
                             JOIN users u ON o.user_id = u.id 
                             ORDER BY o.order_date DESC 
                             LIMIT 5")->fetchAll();

$pageTitle = 'Admin Dashboard';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <h1 class="h3 mb-4">Dashboard</h1>
    
    <!-- Statistics Cards -->
    <div class="row g-4 mb-4">
        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card primary">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ສິນຄ້າທັງໝົດ</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['products']); ?></h3>
                        </div>
                        <i class="fas fa-box fa-2x text-primary"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card success">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ຍອດຂາຍລວມ</h6>
                            <h3 class="mb-0"><?php echo formatPrice($stats['total_sales']); ?></h3>
                        </div>
                        <i class="fas fa-dollar-sign fa-2x text-success"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card warning">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ຄຳສັ່ງຊື້ລໍຖ້າ</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['pending_orders']); ?></h3>
                        </div>
                        <i class="fas fa-clock fa-2x text-warning"></i>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-md-6 col-lg-3">
            <div class="card dashboard-card danger">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-2">ຜູ້ໃຊ້ທັງໝົດ</h6>
                            <h3 class="mb-0"><?php echo number_format($stats['users']); ?></h3>
                        </div>
                        <i class="fas fa-users fa-2x text-danger"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Recent Orders -->
    <div class="card">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">ຄຳສັ່ງຊື້ລ່າສຸດ</h5>
        </div>
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
                        <?php foreach($recentOrders as $order): ?>
                        <tr>
                            <td>#<?php echo $order['id']; ?></td>
                            <td><?php echo escape($order['full_name']); ?></td>
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
                            <td><?php echo date('d/m/Y H:i', strtotime($order['order_date'])); ?></td>
                            <td>
                                <a href="order-detail.php?id=<?php echo $order['id']; ?>" class="btn btn-sm btn-info">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            
            <div class="text-center mt-3">
                <a href="orders.php" class="btn btn-primary">ເບິ່ງຄຳສັ່ງຊື້ທັງໝົດ</a>
            </div>
        </div>
    </div>
</div>
<?php include '../includes/footer.php'; ?>