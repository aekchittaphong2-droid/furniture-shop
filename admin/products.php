<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// ການລຶບສິນຄ້າ
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // ລຶບຮູບພາບ
    $stmt = $pdo->prepare("SELECT image FROM products WHERE id = ?");
    $stmt->execute([$id]);
    $product = $stmt->fetch();
    
    if($product && $product['image'] && file_exists("../assets/images/products/{$product['image']}")) {
        unlink("../assets/images/products/{$product['image']}");
    }
    
    // ລຶບສິນຄ້າ
    $stmt = $pdo->prepare("DELETE FROM products WHERE id = ?");
    $stmt->execute([$id]);
    
    header('Location: products.php?deleted=1');
    exit();
}

// ດຶງສິນຄ້າທັງໝົດ
$stmt = $pdo->query("SELECT p.*, c.name as category_name 
                     FROM products p 
                     LEFT JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.id DESC");
$products = $stmt->fetchAll();

$pageTitle = 'ຈັດການສິນຄ້າ';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">ຈັດການສິນຄ້າ</h1>
        <a href="add-product.php" class="btn btn-primary">
            <i class="fas fa-plus"></i> ເພີ່ມສິນຄ້າໃໝ່
        </a>
    </div>
    
    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ລຶບສິນຄ້າສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['added'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ເພີ່ມສິນຄ້າສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ອັບເດດສິນຄ້າສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th width="100">ຮູບພາບ</th>
                            <th>ຊື່ສິນຄ້າ</th>
                            <th>ໝວດໝູ່</th>
                            <th>ລາຄາ</th>
                            <th>ສະຕ໋ອກ</th>
                            <th>ວັນທີເພີ່ມ</th>
                            <th width="150">ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($products as $product): ?>
                        <tr>
                            <td><?php echo $product['id']; ?></td>
                            <td>
                                <img src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                                     width="60" class="rounded">
                            </td>
                            <td><?php echo escape($product['name']); ?></td>
                            <td><?php echo escape($product['category_name']); ?></td>
                            <td><?php echo formatPrice($product['price']); ?></td>
                            <td>
                                <?php if($product['stock'] <= 5): ?>
                                <span class="badge bg-danger"><?php echo $product['stock']; ?></span>
                                <?php else: ?>
                                <span class="badge bg-success"><?php echo $product['stock']; ?></span>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($product['created_at'])); ?></td>
                            <td>
                                <a href="edit-product.php?id=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-warning">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <a href="?delete=<?php echo $product['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບສິນຄ້ານີ້?')">
                                    <i class="fas fa-trash"></i>
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