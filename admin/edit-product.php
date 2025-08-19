<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

if(!isset($_GET['id'])) {
    header('Location: products.php');
    exit();
}

$product_id = (int)$_GET['id'];

// ດຶງຂໍ້ມູນສິນຄ້າ
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    header('Location: products.php');
    exit();
}

$errors = [];

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $category_id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    $price = (float)$_POST['price'];
    $stock = (int)$_POST['stock'];
    
    // Validation
    if(empty($name)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ສິນຄ້າ';
    }
    
    if($price <= 0) {
        $errors[] = 'ລາຄາຕ້ອງຫຼາຍກວ່າ 0';
    }
    
    if($stock < 0) {
        $errors[] = 'ຈຳນວນສິນຄ້າບໍ່ຖືກຕ້ອງ';
    }
    
    // Upload image ຖ້າມີ
    $image = $product['image'];
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadedImage = uploadImage($_FILES['image'], "../assets/images/products/");
        if($uploadedImage) {
            // ລຶບຮູບເກົ່າ
            if($product['image'] && file_exists("../assets/images/products/{$product['image']}")) {
                unlink("../assets/images/products/{$product['image']}");
            }
            $image = $uploadedImage;
        } else {
            $errors[] = 'ອັບໂຫຼດຮູບພາບລົ້ມເຫລວ';
        }
    }
    
    if(empty($errors)) {
        $stmt = $pdo->prepare("UPDATE products SET category_id = ?, name = ?, description = ?, 
                               price = ?, stock = ?, image = ? WHERE id = ?");
        
        if($stmt->execute([$category_id, $name, $description, $price, $stock, $image, $product_id])) {
            header('Location: products.php?updated=1');
            exit();
        } else {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການອັບເດດສິນຄ້າ';
        }
    }
}

// ດຶງໝວດໝູ່
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$pageTitle = 'ແກ້ໄຂສິນຄ້າ';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ແກ້ໄຂສິນຄ້າ</h5>
                </div>
                <div class="card-body">
                    <?php if(!empty($errors)): ?>
                    <div class="alert alert-danger">
                        <ul class="mb-0">
                            <?php foreach($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    <?php endif; ?>
                    
                    <form method="POST" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label for="image" class="form-label">ຮູບພາບສິນຄ້າ</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <small class="text-muted">รองรับไฟล์: JPG, PNG, GIF (ขนาดไม่เกิน 5MB)</small>
                            <div class="mt-2">
                                <?php if($product['image']): ?>
                                <p>ຮູບພາບປັດຈຸບັນ:</p>
                                <img src="../assets/images/products/<?php echo $product['image']; ?>" width="200" class="img-thumbnail">
                                <?php endif; ?>
                            </div>
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ບັນທຶກການແກ້ໄຂ
                            </button>
                            <a href="products.php" class="btn btn-secondary">
                                <i class="fas fa-times"></i> ຍົກເລີກ
                            </a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>