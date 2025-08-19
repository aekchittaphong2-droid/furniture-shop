<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'ໜ້າຫຼັກ - ຮ້ານເຟີນິເຈີ';
include '../includes/header.php';
include '../includes/navbar.php';

// ດຶງສິນຄ້າໃໝ່ລ່າສຸດ
$stmt = $pdo->query("SELECT p.*, c.name as category_name 
                     FROM products p 
                     JOIN categories c ON p.category_id = c.id 
                     ORDER BY p.created_at DESC 
                     LIMIT 8");
$newProducts = $stmt->fetchAll();

// ດຶງໝວດໝູ່ທັງໝົດ
$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>

<!-- Hero Section -->
<section class="hero-section bg-primary text-white py-5">
    <div class="container">
        <div class="row align-items-center">
            <div class="col-lg-6">
                <h1 class="display-4 fw-bold mb-4">ຍິນດີຕ້ອນຮັບສູ່ຮ້ານເຟີນິເຈີ</h1>
                <p class="lead mb-4">ເຟີນິເຈີຄຸນນະພາບດີ ລາຄາຍຸຕິທຳ ຈັດສົ່ງທົ່ວລາວ</p>
                <a href="products.php" class="btn btn-light btn-lg">ເບິ່ງສິນຄ້າທັງໝົດ</a>
            </div>
            <div class="col-lg-6">
                <img src="../assets/images/furniture-hero.jpg" alt="Furniture" class="img-fluid rounded" 
                     onerror="this.src='data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAwIiBoZWlnaHQ9IjQwMCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjYwMCIgaGVpZ2h0PSI0MDAiIGZpbGw9IiNlMGUwZTAiLz4KPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIyNCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSI+RnVybml0dXJlPC90ZXh0Pgo8L3N2Zz4='">
            </div>
        </div>
    </div>
</section>

<!-- Categories Section -->
<section class="py-5">
    <div class="container">
        <h2 class="text-center mb-5">ໝວດໝູ່ສິນຄ້າ</h2>
        <div class="row g-4">
            <?php foreach($categories as $category): ?>
            <div class="col-md-4 col-lg-2">
                <a href="products.php?category=<?php echo $category['id']; ?>" class="text-decoration-none">
                    <div class="card text-center h-100 category-card">
                        <div class="card-body">
                            <i class="fas fa-couch fa-3x mb-3 text-primary"></i>
                            <h5 class="card-title"><?php echo escape($category['name']); ?></h5>
                        </div>
                    </div>
                </a>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
</section>

<!-- New Products Section -->
<section class="py-5 bg-light">
    <div class="container">
        <h2 class="text-center mb-5">ສິນຄ້າໃໝ່ລ່າສຸດ</h2>
        <div class="row g-4">
            <?php foreach($newProducts as $product): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card product-card h-100" data-price="<?php echo $product['price']; ?>">
                    <?php 
                    $imagePath = "../assets/images/products/" . ($product['image'] ?: 'default.jpg');
                    $defaultImage = "data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iMzAwIiBoZWlnaHQ9IjI1MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KPHJlY3Qgd2lkdGg9IjMwMCIgaGVpZ2h0PSIyNTAiIGZpbGw9IiNlMGUwZTAiLz4KPHRleHQgeD0iNTAlIiB5PSI1MCUiIGZvbnQtZmFtaWx5PSJBcmlhbCIgZm9udC1zaXplPSIxOCIgZmlsbD0iIzk5OSIgdGV4dC1hbmNob3I9Im1pZGRsZSIgZG9taW5hbnQtYmFzZWxpbmU9Im1pZGRsZSI+Tm8gSW1hZ2U8L3RleHQ+Cjwvc3ZnPg==";
                    ?>
                    <img src="<?php echo $imagePath; ?>" 
                         class="card-img-top product-image" 
                         alt="<?php echo escape($product['name']); ?>"
                         onerror="this.src='<?php echo $defaultImage; ?>'">
                    <div class="card-body d-flex flex-column">
                        <span class="badge bg-secondary mb-2"><?php echo escape($product['category_name']); ?></span>
                        <h5 class="card-title"><?php echo escape($product['name']); ?></h5>
                        <p class="card-text flex-grow-1"><?php echo escape(truncateText($product['description'], 100)); ?></p>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="price"><?php echo formatPrice($product['price']); ?></span>
                            <span class="text-muted">ເຫຼືອ <?php echo $product['stock']; ?> ຊິ້ນ</span>
                        </div>
                        <div class="mt-3">
                            <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary w-100">
                                <i class="fas fa-eye"></i> ເບິ່ງລາຍລະອຽດ
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <?php if(empty($newProducts)): ?>
        <div class="text-center">
            <p class="text-muted">ຍັງບໍ່ມີສິນຄ້າໃນລະບົບ</p>
        </div>
        <?php endif; ?>
    </div>
</section>

<!-- Features Section -->
<section class="py-5">
    <div class="container">
        <div class="row text-center">
            <div class="col-md-3">
                <i class="fas fa-truck fa-3x text-primary mb-3"></i>
                <h4>ຈັດສົ່ງຟຣີ</h4>
                <p>ສຳລັບຄຳສັ່ງຊື້ 500,000 ກີບຂຶ້ນໄປ</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-shield-alt fa-3x text-primary mb-3"></i>
                <h4>ຮັບປະກັນຄຸນນະພາບ</h4>
                <p>ສິນຄ້າທຸກຊິ້ນມີການຮັບປະກັນ</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-undo fa-3x text-primary mb-3"></i>
                <h4>ຄືນສິນຄ້າໄດ້</h4>
                <p>ພາຍໃນ 7 ວັນຫຼັງຮັບສິນຄ້າ</p>
            </div>
            <div class="col-md-3">
                <i class="fas fa-headset fa-3x text-primary mb-3"></i>
                <h4>ບໍລິການ 24/7</h4>
                <p>ພ້ອມໃຫ້ຄຳປຶກສາຕະຫຼອດເວລາ</p>
            </div>
        </div>
    </div>
</section>

<?php include '../includes/footer.php'; ?>