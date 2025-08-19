<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    header('Location: login.php');
    exit();
}

// ດຶງສິນຄ້າໃນຕະກ້າ
$stmt = $pdo->prepare("SELECT c.*, p.name, p.price, p.image, p.stock 
                       FROM cart c 
                       JOIN products p ON c.product_id = p.id 
                       WHERE c.user_id = ? 
                       ORDER BY c.added_at DESC");
$stmt->execute([$_SESSION['user_id']]);
$cartItems = $stmt->fetchAll();

// ຄຳນວນຍອດລວມ
$total = 0;
foreach($cartItems as $item) {
    $total += $item['price'] * $item['quantity'];
}

$pageTitle = 'ຕະກ້າສິນຄ້າ';
include '../includes/header.php';
include '../includes/navbar.php';
?>

<div class="container py-5">
    <h2 class="mb-4">ຕະກ້າສິນຄ້າຂອງທ່ານ</h2>
    
    <?php if(empty($cartItems)): ?>
    <div class="alert alert-info text-center">
        <i class="fas fa-shopping-cart fa-3x mb-3"></i>
        <h4>ຕະກ້າຂອງທ່ານຍັງວ່າງເປົ່າ</h4>
        <p>ກະລຸນາເລືອກສິນຄ້າທີ່ທ່ານຕ້ອງການ</p>
        <a href="products.php" class="btn btn-primary">ເລືອກຊື້ສິນຄ້າ</a>
    </div>
    <?php else: ?>
    <div class="row">
        <div class="col-lg-8">
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>ສິນຄ້າ</th>
                                    <th>ລາຄາ</th>
                                    <th>ຈຳນວນ</th>
                                    <th>ລວມ</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($cartItems as $item): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <img src="../assets/images/products/<?php echo $item['image'] ?: 'default.jpg'; ?>" 
                                                 width="60" class="me-3 rounded">
                                            <div>
                                                <h6 class="mb-0"><?php echo escape($item['name']); ?></h6>
                                                <?php if($item['quantity'] > $item['stock']): ?>
                                                <small class="text-danger">ເຫຼືອພຽງ <?php echo $item['stock']; ?> ຊິ້ນ</small>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td><?php echo formatPrice($item['price']); ?></td>
                                    <td>
                                        <input type="number" class="form-control quantity-input" style="width: 80px"
                                               data-cart-id="<?php echo $item['id']; ?>"
                                               data-max="<?php echo $item['stock']; ?>"
                                               value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="<?php echo $item['stock']; ?>">
                                    </td>
                                    <td class="item-total" data-price="<?php echo $item['price'] * $item['quantity']; ?>">
                                        <?php echo formatPrice($item['price'] * $item['quantity']); ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-danger remove-from-cart" 
                                                data-cart-id="<?php echo $item['id']; ?>">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="col-lg-4">
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title">ສະຫຼຸບຄຳສັ່ງຊື້</h5>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>ລວມສິນຄ້າ:</span>
                        <span><?php echo count($cartItems); ?> ລາຍການ</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-3">
                        <span>ຄ່າຈັດສົ່ງ:</span>
                        <span>ຟຣີ</span>
                    </div>
                    
                    <hr>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <h5>ຍອດລວມທັງໝົດ:</h5>
                        <h5 class="text-success" id="cart-total"><?php echo formatPrice($total); ?></h5>
                    </div>
                    
                    <a href="checkout.php" class="btn btn-primary w-100 btn-lg">
                        <i class="fas fa-credit-card"></i> ດຳເນີນການຊຳລະເງິນ
                    </a>
                    
                    <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">
                        <i class="fas fa-shopping-bag"></i> ເລືອກຊື້ສິນຄ້າເພີ່ມ
                    </a>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<script>
// Update quantity
$('.quantity-input').change(function() {
    var cartId = $(this).data('cart-id');
    var quantity = $(this).val();
    var max = $(this).data('max');
    var input = $(this);
    
    if(quantity > max) {
        alert('ຈຳນວນສິນຄ້າເກີນກວ່າທີ່ມີໃນສະຕ໋ອກ');
        input.val(max);
        quantity = max;
    }
    
    $.ajax({
        url: '../ajax/update_cart.php',
        type: 'POST',
        data: {
            cart_id: cartId,
            quantity: quantity
        },
        success: function(response) {
            location.reload();
        }
    });
});
</script>