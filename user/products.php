<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

$pageTitle = 'ສິນຄ້າທັງໝົດ';
include '../includes/header.php';
include '../includes/navbar.php';

// ການກັ່ນຕອງ
$where = [];
$params = [];

// ກັ່ນຕອງຕາມໝວດໝູ່
if(isset($_GET['category']) && $_GET['category']) {
    $where[] = "p.category_id = ?";
    $params[] = $_GET['category'];
}

// ກັ່ນຕອງຕາມການຄົ້ນຫາ
if(isset($_GET['search']) && $_GET['search']) {
    $where[] = "(p.name LIKE ? OR p.description LIKE ?)";
    $searchTerm = "%{$_GET['search']}%";
    $params[] = $searchTerm;
    $params[] = $searchTerm;
}

// ກັ່ນຕອງຕາມລາຄາ
if(isset($_GET['min_price']) && $_GET['min_price']) {
    $where[] = "p.price >= ?";
    $params[] = $_GET['min_price'];
}

if(isset($_GET['max_price']) && $_GET['max_price']) {
    $where[] = "p.price <= ?";
    $params[] = $_GET['max_price'];
}

// ສ້າງ query
$sql = "SELECT p.*, c.name as category_name 
        FROM products p 
        JOIN categories c ON p.category_id = c.id";

if(!empty($where)) {
    $sql .= " WHERE " . implode(" AND ", $where);
}

// ການຈັດລຽງ
$sort = isset($_GET['sort']) ? $_GET['sort'] : 'newest';
switch($sort) {
    case 'price_low':
        $sql .= " ORDER BY p.price ASC";
        break;
    case 'price_high':
        $sql .= " ORDER BY p.price DESC";
        break;
    case 'name':
        $sql .= " ORDER BY p.name ASC";
        break;
    default:
        $sql .= " ORDER BY p.created_at DESC";
}

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$products = $stmt->fetchAll();

// ດຶງໝວດໝູ່ທັງໝົດສຳລັບ filter
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();
?>

<div class="container py-5">
    <h2 class="mb-4">ສິນຄ້າທັງໝົດ</h2>
    
    <div class="row">
        <!-- Sidebar Filter -->
        <div class="col-lg-3">
            <div class="card mb-4">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ຕົວກັ່ນຕອງ</h5>
                </div>
                <div class="card-body">
                    <form method="GET" id="filterForm">
                        <!-- Search -->
                        <div class="mb-3">
                            <label class="form-label">ຄົ້ນຫາ</label>
                            <input type="text" class="form-control" name="search" 
                                   value="<?php echo isset($_GET['search']) ? escape($_GET['search']) : ''; ?>"
                                   placeholder="ຄົ້ນຫາສິນຄ້າ...">
                        </div>
                        
                        <!-- Categories -->
                        <div class="mb-3">
                            <label class="form-label">ໝວດໝູ່</label>
                            <select class="form-select" name="category">
                                <option value="">ທັງໝົດ</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($_GET['category']) && $_GET['category'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <!-- Price Range -->
                        <div class="mb-3">
                            <label class="form-label">ລາຄາຕໍ່າສຸດ</label>
                            <input type="number" class="form-control" name="min_price" 
                                   value="<?php echo isset($_GET['min_price']) ? $_GET['min_price'] : ''; ?>"
                                   placeholder="0">
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label">ລາຄາສູງສຸດ</label>
                            <input type="number" class="form-control" name="max_price" 
                                   value="<?php echo isset($_GET['max_price']) ? $_GET['max_price'] : ''; ?>"
                                   placeholder="50000000">
                        </div>
                        
                        <button type="submit" class="btn btn-primary w-100">ກັ່ນຕອງ</button>
                        <a href="products.php" class="btn btn-secondary w-100 mt-2">ລ້າງຕົວກັ່ນຕອງ</a>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="col-lg-9">
            <!-- Sort Options -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <p class="mb-0">ພົບ <strong><?php echo count($products); ?></strong> ສິນຄ້າ</p>
                <select class="form-select w-auto" id="sortSelect" onchange="changeSort()">
                    <option value="newest" <?php echo $sort == 'newest' ? 'selected' : ''; ?>>ໃໝ່ລ່າສຸດ</option>
                    <option value="price_low" <?php echo $sort == 'price_low' ? 'selected' : ''; ?>>ລາຄາຕໍ່າ - ສູງ</option>
                    <option value="price_high" <?php echo $sort == 'price_high' ? 'selected' : ''; ?>>ລາຄາສູງ - ຕໍ່າ</option>
                    <option value="name" <?php echo $sort == 'name' ? 'selected' : ''; ?>>ຊື່ສິນຄ້າ</option>
                </select>
            </div>
            
            <?php if(empty($products)): ?>
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> ບໍ່ພົບສິນຄ້າທີ່ຄົ້ນຫາ
            </div>
            <?php else: ?>
            <div class="row g-4">
                <?php foreach($products as $product): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card product-card h-100" data-price="<?php echo $product['price']; ?>">
                        <img src="../assets/images/products/<?php echo $product['image'] ?: 'default.jpg'; ?>" 
                             class="card-img-top product-image" 
                             alt="<?php echo escape($product['name']); ?>">
                        <div class="card-body d-flex flex-column">
                            <span class="badge bg-secondary mb-2"><?php echo escape($product['category_name']); ?></span>
                            <h5 class="card-title"><?php echo escape($product['name']); ?></h5>
                            <p class="card-text flex-grow-1"><?php echo escape(mb_substr($product['description'], 0, 100)) . '...'; ?></p>
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <span class="price"><?php echo formatPrice($product['price']); ?></span>
                                <span class="text-muted">ເຫຼືອ <?php echo $product['stock']; ?> ຊິ້ນ</span>
                            </div>
                            <div class="d-grid gap-2">
                                <a href="product-detail.php?id=<?php echo $product['id']; ?>" class="btn btn-outline-primary">
                                    <i class="fas fa-eye"></i> ເບິ່ງລາຍລະອຽດ
                                </a>
                                <?php if($product['stock'] > 0): ?>
                                <button class="btn btn-primary add-to-cart-quick" data-product-id="<?php echo $product['id']; ?>">
                                    <i class="fas fa-cart-plus"></i> ເພີ່ມເຂົ້າຕະກ້າ
                                </button>
                                <?php else: ?>
                                <button class="btn btn-secondary" disabled>
                                    <i class="fas fa-times"></i> ສິນຄ້າໝົດ
                                </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function changeSort() {
    var sort = document.getElementById('sortSelect').value;
    var url = new URL(window.location.href);
    url.searchParams.set('sort', sort);
    window.location.href = url.toString();
}

// Quick add to cart
$(document).on('click', '.add-to-cart-quick', function() {
    var productId = $(this).data('product-id');
    var button = $(this);
    
    button.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> ກຳລັງເພີ່ມ...');
    
    $.ajax({
        url: '../ajax/add_to_cart.php',
        type: 'POST',
        data: {
            product_id: productId,
            quantity: 1
        },
        success: function(response) {
            var data = JSON.parse(response);
            if(data.success) {
                button.html('<i class="fas fa-check"></i> ເພີ່ມແລ້ວ');
                updateCartCount();
                setTimeout(function() {
                    button.prop('disabled', false).html('<i class="fas fa-cart-plus"></i> ເພີ່ມເຂົ້າຕະກ້າ');
                }, 1500);
            } else {
                alert(data.message);
                button.prop('disabled', false).html('<i class="fas fa-cart-plus"></i> ເພີ່ມເຂົ້າຕະກ້າ');
            }
        }
    });
});
</script>