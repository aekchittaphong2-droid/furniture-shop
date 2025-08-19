<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
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
    
    // Upload image
    $image = '';
    if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
        $uploadedImage = uploadImage($_FILES['image']);
        if($uploadedImage) {
            $image = $uploadedImage;
        } else {
            $errors[] = 'ອັບໂຫຼດຮູບພາບລົ້ມເຫລວ';
        }
    }
    
    if(empty($errors)) {
        $stmt = $pdo->prepare("INSERT INTO products (category_id, name, description, price, stock, image) 
                               VALUES (?, ?, ?, ?, ?, ?)");
        
        if($stmt->execute([$category_id, $name, $description, $price, $stock, $image])) {
            header('Location: products.php?added=1');
            exit();
        } else {
            $errors[] = 'ເກີດຂໍ້ຜິດພາດໃນການເພີ່ມສິນຄ້າ';
        }
    }
}

// ດຶງໝວດໝູ່
$categories = $pdo->query("SELECT * FROM categories ORDER BY name")->fetchAll();

$pageTitle = 'ເພີ່ມສິນຄ້າໃໝ່';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">ເພີ່ມສິນຄ້າໃໝ່</h5>
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
                            <label for="name" class="form-label">ຊື່ສິນຄ້າ <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name" 
                                   value="<?php echo isset($_POST['name']) ? escape($_POST['name']) : ''; ?>" required>
                        </div>
                        
                        <div class="mb-3">
                            <label for="category_id" class="form-label">ໝວດໝູ່ <span class="text-danger">*</span></label>
                            <select class="form-select" id="category_id" name="category_id" required>
                                <option value="">-- ເລືອກໝວດໝູ່ --</option>
                                <?php foreach($categories as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>" 
                                        <?php echo (isset($_POST['category_id']) && $_POST['category_id'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo escape($cat['name']); ?>
                                </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="description" class="form-label">ລາຍລະອຽດ</label>
                            <textarea class="form-control" id="description" name="description" rows="4"><?php echo isset($_POST['description']) ? escape($_POST['description']) : ''; ?></textarea>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="price" class="form-label">ລາຄາ (ກີບ) <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="price" name="price" step="1000" 
                                       value="<?php echo isset($_POST['price']) ? $_POST['price'] : ''; ?>" required>
                            </div>
                            
                            <div class="col-md-6 mb-3">
                                <label for="stock" class="form-label">ຈຳນວນສະຕ໋ອກ <span class="text-danger">*</span></label>
                                <input type="number" class="form-control" id="stock" name="stock" 
                                       value="<?php echo isset($_POST['stock']) ? $_POST['stock'] : '0'; ?>" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="image" class="form-label">ຮູບພາບສິນຄ້າ</label>
                            <input type="file" class="form-control" id="image" name="image" accept="image/*">
                            <div id="imagePreview" class="mt-2"></div>
                        </div>
                        
                        <div class="d-flex gap-2">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> ບັນທຶກ
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