<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn() || !isAdmin()) {
    header('Location: login.php');
    exit();
}

// ການລຶບຜູ້ໃຊ້
if(isset($_GET['delete']) && $_GET['delete'] != $_SESSION['user_id']) {
    $id = (int)$_GET['delete'];
    
    // ບໍ່ໃຫ້ລຶບ admin ຄົນສຸດທ້າຍ
    $stmt = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'admin'");
    $adminCount = $stmt->fetchColumn();
    
    $stmt = $pdo->prepare("SELECT role FROM users WHERE id = ?");
    $stmt->execute([$id]);
    $userToDelete = $stmt->fetch();
    
    if($userToDelete['role'] != 'admin' || $adminCount > 1) {
        $stmt = $pdo->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$id]);
        header('Location: users.php?deleted=1');
        exit();
    }
}

// ປ່ຽນສິດ
if(isset($_POST['change_role'])) {
    $user_id = (int)$_POST['user_id'];
    $role = $_POST['role'];
    
    if($user_id != $_SESSION['user_id']) {
        $stmt = $pdo->prepare("UPDATE users SET role = ? WHERE id = ?");
        $stmt->execute([$role, $user_id]);
    }
    
    header('Location: users.php?updated=1');
    exit();
}

// ດຶງຜູ້ໃຊ້ທັງໝົດ
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// ສະຖິຕິຜູ້ໃຊ້
$stats = [];
$stats['total'] = count($users);
$stats['admins'] = count(array_filter($users, function($u) { return $u['role'] == 'admin'; }));
$stats['users'] = count(array_filter($users, function($u) { return $u['role'] == 'user'; }));

$pageTitle = 'ຈັດການຜູ້ໃຊ້';
$isAdmin = true;
include '../includes/header.php';
include 'includes/admin-nav.php';
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3">ຈັດການຜູ້ໃຊ້</h1>
        <div>
            <span class="badge bg-primary">ທັງໝົດ: <?php echo $stats['total']; ?></span>
            <span class="badge bg-success">Admin: <?php echo $stats['admins']; ?></span>
            <span class="badge bg-info">User: <?php echo $stats['users']; ?></span>
        </div>
    </div>
    
    <?php if(isset($_GET['deleted'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ລຶບຜູ້ໃຊ້ສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <?php if(isset($_GET['updated'])): ?>
    <div class="alert alert-success alert-dismissible fade show">
        ອັບເດດສິດຜູ້ໃຊ້ສຳເລັດ!
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
    <?php endif; ?>
    
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>ຊື່ຜູ້ໃຊ້</th>
                            <th>ຊື່ແທ້</th>
                            <th>ອີເມວ</th>
                            <th>ເບີໂທ</th>
                            <th>ສິດ</th>
                            <th>ວັນທີລົງທະບຽນ</th>
                            <th>ຈັດການ</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($users as $user): ?>
                        <tr>
                            <td><?php echo $user['id']; ?></td>
                            <td><?php echo escape($user['username']); ?></td>
                            <td><?php echo escape($user['full_name']); ?></td>
                            <td><?php echo escape($user['email']); ?></td>
                            <td><?php echo escape($user['phone']); ?></td>
                            <td>
                                <?php if($user['id'] == $_SESSION['user_id']): ?>
                                    <span class="badge bg-<?php echo $user['role'] == 'admin' ? 'danger' : 'primary'; ?>">
                                        <?php echo ucfirst($user['role']); ?>
                                    </span>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?php echo $user['id']; ?>">
                                        <select name="role" class="form-select form-select-sm" onchange="this.form.submit()" style="width: auto;">
                                            <option value="user" <?php echo $user['role'] == 'user' ? 'selected' : ''; ?>>User</option>
                                            <option value="admin" <?php echo $user['role'] == 'admin' ? 'selected' : ''; ?>>Admin</option>
                                        </select>
                                        <input type="hidden" name="change_role" value="1">
                                    </form>
                                <?php endif; ?>
                            </td>
                            <td><?php echo date('d/m/Y', strtotime($user['created_at'])); ?></td>
                            <td>
                                <?php if($user['id'] != $_SESSION['user_id']): ?>
                                    <a href="?delete=<?php echo $user['id']; ?>" 
                                       class="btn btn-sm btn-danger"
                                       onclick="return confirm('ທ່ານແນ່ໃຈບໍ່ວ່າຕ້ອງການລຶບຜູ້ໃຊ້ນີ້?')">
                                        <i class="fas fa-trash"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
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

<?php include '../includes/footer.php'; ?>