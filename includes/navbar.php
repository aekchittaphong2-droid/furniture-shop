<?php
// ກວດສອບວ່າມີ session ແລ້ວຫຼືຍັງ
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// ກຳນົດ path ໃຫ້ຖືກຕ້ອງ
$base_path = '';
if(strpos($_SERVER['REQUEST_URI'], '/admin/') !== false) {
    $base_path = '../';
} elseif(strpos($_SERVER['REQUEST_URI'], '/user/') !== false) {
    $base_path = '../';
}

// ຟັງຊັນສຳລັບສ້າງ URL
function navUrl($path) {
    global $base_path;
    return $base_path . $path;
}
?>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="<?php echo navUrl('user/index.php'); ?>">
            <i class="fas fa-couch"></i> ຮ້ານເຟີນິເຈີ
        </a>
        
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo navUrl('user/index.php'); ?>">ໜ້າຫຼັກ</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="<?php echo navUrl('user/products.php'); ?>">ສິນຄ້າ</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">ໝວດໝູ່</a>
                    <ul class="dropdown-menu">
                        <?php
                        // ດຶງໝວດໝູ່ຈາກຖານຂໍ້ມູນ
                        require_once $base_path . 'config/database.php';
                        $stmt = $pdo->query("SELECT * FROM categories ORDER BY name");
                        while($cat = $stmt->fetch()) {
                            echo '<li><a class="dropdown-item" href="'.navUrl('user/products.php?category='.$cat['id']).'">'.htmlspecialchars($cat['name'], ENT_QUOTES, 'UTF-8').'</a></li>';
                        }
                        ?>
                    </ul>
                </li>
            </ul>
            
            <ul class="navbar-nav">
                <?php if(isset($_SESSION['user_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo navUrl('user/cart.php'); ?>">
                            <i class="fas fa-shopping-cart"></i> ຕະກ້າ
                            <span class="badge bg-danger cart-count">0</span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user"></i> <?php echo htmlspecialchars($_SESSION['full_name'], ENT_QUOTES, 'UTF-8'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo navUrl('user/profile.php'); ?>">ໂປຣໄຟລ໌</a></li>
                            <li><a class="dropdown-item" href="<?php echo navUrl('user/orders.php'); ?>">ຄຳສັ່ງຊື້ຂອງຂ້ອຍ</a></li>
                            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo navUrl('admin/dashboard.php'); ?>">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo navUrl('user/logout.php'); ?>">ອອກຈາກລະບົບ</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo navUrl('user/login.php'); ?>">ເຂົ້າສູ່ລະບົບ</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo navUrl('user/register.php'); ?>">ລົງທະບຽນ</a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<script>
// ອັບເດດຈຳນວນສິນຄ້າໃນຕະກ້າ
$(document).ready(function() {
    updateCartCount();
});

function updateCartCount() {
    <?php if(isset($_SESSION['user_id'])): ?>
    $.get('<?php echo $base_path; ?>ajax/get_cart_count.php', function(count) {
        $('.cart-count').text(count);
    });
    <?php endif; ?>
}
</script>