<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

$errors = [];
$success = '';

// ເພີ່ມໝວດໝູ່
if(isset($_POST['add_category'])) {
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if(empty($name)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ໝວດໝູ່';
    } else {
        $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
        if($stmt->execute([$name, $description])) {
            $success = 'ເພີ່ມໝວດໝູ່ສຳເລັດ!';
        }
    }
}

// ແກ້ໄຂໝວດໝູ່
if(isset($_POST['edit_category'])) {
    $id = (int)$_POST['category_id'];
    $name = trim($_POST['name']);
    $description = trim($_POST['description']);
    
    if(empty($name)) {
        $errors[] = 'ກະລຸນາປ້ອນຊື່ໝວດໝູ່';
    } else {
        $stmt = $pdo->prepare("UPDATE categories SET name = ?, description = ? WHERE id = ?");
        if($stmt->execute([$name, $description, $id])) {
            $success = 'ອັບເດດໝວດໝູ່ສຳເລັດ!';
        }
    }
}

// ລຶບໝວດໝູ່
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    
    // ກວດສອບວ່າມີສິນຄ້າໃນໝວດໝູ່ນີ້ບໍ່
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM products WHERE category_id = ?");
    $stmt->execute([$id]);
    $productCount = $stmt->fetchColumn();
    
    if($productCount > 0) {
        $errors[] = "ບໍ່ສາມາດລຶບໝວດໝູ່ນີ້ໄດ້ ເພາະມີສິນຄ້າ $productCount ລາຍການ";
    } else {
        $stmt = $pdo->prepare("DELETE FROM categories WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: categories.php?deleted=1');
        exit();
    }
}

// ດຶງໝວດໝູ່ທັງໝົດ
$stmt = $pdo->query("SELECT c.*, COUNT(p.id) as product_count 
                     FROM categories c 
                     LEFT JOIN products p ON c.id = p.category_id 
                     GROUP BY c.id 
                     ORDER BY c.name");
$categories = $stmt->fetchAll();

$pageTitle = 'ຈັດການໝວດໝູ່';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">ຈັດການໝວດໝູ່</h1>
        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCategoryModal">
            <i class="fas fa-plus"></i> ເພີ່ມໝວດໝູ່ໃໝ່
        </button>
    </div>
    
    <?php if($success): ?>
    <div class="alert alert-success alert-dismissible fade show">
        <?php echo $success; ?>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ລຶບໝວດໝູ່ສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
        <ul class="mb-0">
            <?php foreach($errors as $error): ?>
            <li><?php echo $error; ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th width="50">ID</th>
                            <th>ຊື່ໝວດໝູ່</th>
                            <th>ລາຍລະອຽດ</th>
                            <th>ຈຳນວນສິນຄ້າ</th>
                            <th>ວັນທີສ້າງ</th>
                            <th width="150">ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($categories as $category): ?>
                        <tr>
                            <td><?php echo $category['id']; ?></td>
                            <td><?php echo escape($category['name']); ?></td>
                            <td><?php echo escape($category['description']); ?></td>
                            <td>
                                <span class="badge bg-info"><?php echo $category['product_count']; ?> ລາຍການ</span>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($category['created_at'])); ?></td>
                            <td>
                                <button class="btn btn-sm btn-warning" 
                                        onclick="editCategory(<?php echo $category['id']; ?>, '<?php echo escape($category['name']); ?>', '<?php echo escape($category['description']); ?>')">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <?php if($category['product_count'] == 0): ?>
                                <a href="?delete=<?php echo $category['id']; ?>" 
                                   class="btn btn-sm btn-danger"
                                   onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບໝວດໝູ່ນີ້?')">
                                    <i class="fas fa-trash"></i>
                                </a>
                                <?php else: ?>
                                <button class="btn btn-sm btn-danger" disabled title="ມີສິນຄ້າໃນໝວດໝູ່ນີ້">
                                    <i class="fas fa-trash"></i>
                                </button>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Add Category Modal -->
<div class="modal fade" id="addCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <div class="modal-header">
                    <h5 class="modal-title">ເພີ່ມໝວດໝູ່ໃໝ່</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label">ຊື່ໝວດໝູ່ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">ລາຍລະອຽດ</label>
                        <textarea class="form-control" id="description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="add_category" class="btn btn-primary">
                        <i class="fas fa-save"></i> ບັນທຶກ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Edit Category Modal -->
<div class="modal fade" id="editCategoryModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST">
                <input type="hidden" id="edit_category_id" name="category_id">
                <div class="modal-header">
                    <h5 class="modal-title">ແກ້ໄຂໝວດໝູ່</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label">ຊື່ໝວດໝູ່ <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="edit_description" class="form-label">ລາຍລະອຽດ</label>
                        <textarea class="form-control" id="edit_description" name="description" rows="3"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ຍົກເລີກ</button>
                    <button type="submit" name="edit_category" class="btn btn-primary">
                        <i class="fas fa-save"></i> ບັນທຶກການແກ້ໄຂ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function editCategory(id, name, description) {
    document.getElementById('edit_category_id').value = id;
    document.getElementById('edit_name').value = name;
    document.getElementById('edit_description').value = description;
    
    var modal = new bootstrap.Modal(document.getElementById('editCategoryModal'));
    modal.show();
}
</script>

<?php include '../includes/footer.php'; ?>