<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

// ດຶງຂໍ້ມູນສິນຄ້າ
$stmt = $pdo->prepare("SELECT p.*, c.name as category_name 
                       FROM products p 
                       JOIN categories c ON p.category_id = c.id 
                       WHERE p.id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header('Location: products.php');
    exit();
}

// ດຶງສິນຄ້າທີ່ກ່ຽວຂ້ອງ
$stmt = $pdo->prepare("SELECT * FROM products 
                       WHERE category_id = ? AND id != ? 
                       ORDER BY RAND() LIMIT 4");
$stmt->execute([$product['category_id'], $product_id]);
$relatedProducts = $stmt->fetchAll();

$pageTitle = $product['name'];
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb">
            <li class="breadcrumb-item"><a href="index.php">ໜ້າຫຼັກ</a></li>
            <li class="breadcrumb-item"><a href="products.php">ສິນຄ້າ</a></li>
            <li class="breadcrumb-item"><a href="products.php?category=<?php echo $product['category_id']; ?>">
                <?php echo escape($product['category_name']); ?></a></li>
            <li class="breadcrumb-item active"><?php echo escape($product['name']); ?></li>
        </ol>
    </nav>
    
    <div class="row">
        <!-- Product Image -->
        <div class="col-lg-5">
            <div class="product-image-container">
                <img src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                     class="img-fluid rounded" 
                     alt="<?php echo escape($product['name']); ?>">
            </div>
        </div>
        
        <!-- Product Info -->
        <div class="col-lg-7">
            <h1 class="h2 mb-3"><?php echo escape($product['name']); ?></h1>
            <p class="text-muted mb-3">
                <span class="badge bg-secondary"><?php echo escape($product['category_name']); ?></span>
            </p>
            
            <div class="price-box mb-4">
                <h3 class="price text-success"><?php echo formatPrice($product['price']); ?></h3>
            </div>
            
            <div class="stock-info mb-4">
                <?php if($product['stock'] > 0): ?>
                    <span class="badge bg-success">ມີສິນຄ້າ</span>
                    <span class="text-muted">ເຫຼືອ <?php echo $product['stock']; ?> ຊິ້ນ</span>
                <?php else: ?>
                    <span class="badge bg-danger">ສິນຄ້າໝົດ</span>
                <?php endif; ?>
            </div>
            
            <div class="description mb-4">
                <h5>ລາຍລະອຽດສິນຄ້າ</h5>
                <p><?php echo nl2br(escape($product['description'])); ?></p>
            </div>
            
            <?php if($product['stock'] > 0): ?>
            <form id="addToCartForm" class="mb-4">
                <input type="hidden" id="product_id" value="<?php echo $product['id']; ?>">
                
                <div class="row align-items-end mb-3">
                    <div class="col-md-3">
                        <label for="quantity" class="form-label">ຈຳນວນ</label>
                        <input type="number" class="form-control" id="quantity" value="1" min="1" max="<?php echo $product['stock']; ?>">
                    </div>
                    <div class="col-md-9">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-shopping-cart"></i> ເພີ່ມເຂົ້າຕະກ້າ
                        </button>
                        <button type="button" class="btn btn-outline-primary btn-lg" onclick="buyNow()">
                            <i class="fas fa-bolt"></i> ຊື້ເລີຍ
                        </button>
                    </div>
                </div>
            </form>
            <?php endif; ?>
            
            <!-- Product Features -->
            <div class="product-features">
                <h5 class="mb-3">ຄຸນສົມບັດພິເສດ</h5>
                <div class="row text-center">
                    <div class="col-6 col-md-3 mb-3">
                        <i class="fas fa-truck fa-2x text-primary mb-2"></i>
                        <p class="mb-0">ຈັດສົ່ງຟຣີ</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <i class="fas fa-shield-alt fa-2x text-primary mb-2"></i>
                        <p class="mb-0">ຮັບປະກັນ 1 ປີ</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <i class="fas fa-undo fa-2x text-primary mb-2"></i>
                        <p class="mb-0">ຄືນໄດ້ 7 ວັນ</p>
                    </div>
                    <div class="col-6 col-md-3 mb-3">
                        <i class="fas fa-certificate fa-2x text-primary mb-2"></i>
                        <p class="mb-0">ສິນຄ້າແທ້ 100%</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Related Products -->
    <?php if(!empty($relatedProducts)): ?>
    <div class="related-products mt-5">
        <h3 class="mb-4">ສິນຄ້າທີ່ກ່ຽວຂ້ອງ</h3>
        <div class="row g-4">
            <?php foreach($relatedProducts as $related): ?>
            <div class="col-md-6 col-lg-3">
                <div class="card product-card h-100">
                    <img src="../assets/images/products/<?php echo $related['image'] ?: 'default.jpg'; ?>" 
                         class="card-img-top product-image" 
                         alt="<?php echo escape($related['name']); ?>">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo escape($related['name']); ?></h5>
                        <p class="price"><?php echo formatPrice($related['price']); ?></p>
                        <a href="product-detail.php?id=<?php echo $related['id']; ?>" class="btn btn-outline-primary w-100">
                            ເບິ່ງລາຍລະອຽດ
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Add to cart
$('#addToCartForm').submit(function(e) {
    e.preventDefault();
    
    var productId = $('#product_id').val();
    var quantity = $('#quantity').val();
    
    $.ajax({
        url: '../ajax/add_to_cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            var data = JSON.parse(response);
            if(data.success) {
                showAlert('ເພີ່ມສິນຄ້າເຂົ້າຕະກ້າສຳເລັດ!', 'success');
                updateCartCount();
            } else {
                showAlert(data.message, 'danger');
            }
        }
    });
});

// Buy now
function buyNow() {
    var productId = $('#product_id').val();
    var quantity = $('#quantity').val();
    
    $.ajax({
        url: '../ajax/add_to_cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: quantity
        },
        success: function(response) {
            var data = JSON.parse(response);
            if(data.success) {
                window.location.href = 'cart.php';
            } else {
                showAlert(data.message, 'danger');
            }
        }
    });
}
</script>