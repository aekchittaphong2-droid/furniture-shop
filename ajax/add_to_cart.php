<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'ກະລຸນາເຂົ້າສູ່ລະບົບກ່ອນ']);
    exit();
}

if($_SERVER['REQUEST_METHOD'] != 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit();
}

$product_id = (int)$_POST['product_id'];
$quantity = (int)$_POST['quantity'];

if($quantity < 1) {
    echo json_encode(['success' => false, 'message' => 'ຈຳນວນບໍ່ຖືກຕ້ອງ']);
    exit();
}

// ກວດສອບສິນຄ້າ
$stmt = $pdo->prepare("SELECT * FROM products WHERE id = ?");
$stmt->execute([$product_id]);
$product = $stmt->fetch();

if(!$product) {
    echo json_encode(['success' => false, 'message' => 'ບໍ່ພົບສິນຄ້າ']);
    exit();
}

if($product['stock'] < $quantity) {
    echo json_encode(['success' => false, 'message' => 'ສິນຄ້າບໍ່ພຽງພໍ']);
    exit();
}

// ກວດສອບວ່າມີໃນຕະກ້າແລ້ວບໍ່
$stmt = $pdo->prepare("SELECT * FROM cart WHERE user_id = ? AND product_id = ?");
$stmt->execute([$_SESSION['user_id'], $product_id]);
$existingCart = $stmt->fetch();

if($existingCart) {
    // ອັບເດດຈຳນວນ
    $newQuantity = $existingCart['quantity'] + $quantity;
    
    if($newQuantity > $product['stock']) {
        echo json_encode(['success' => false, 'message' => 'ຈຳນວນສິນຄ້າເກີນສະຕ໋ອກ']);
        exit();
    }
    
    $stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ?");
    $stmt->execute([$newQuantity, $existingCart['id']]);
} else {
    // ເພີ່ມໃໝ່
    $stmt = $pdo->prepare("INSERT INTO cart (user_id, product_id, quantity) VALUES (?, ?, ?)");
    $stmt->execute([$_SESSION['user_id'], $product_id, $quantity]);
}

echo json_encode(['success' => true, 'message' => 'ເພີ່ມສິນຄ້າເຂົ້າຕະກ້າສຳເລັດ']);
?>
```

```php
// ajax/get_cart_count.php
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

if(!isLoggedIn()) {
    echo "0";
    exit();
}

$stmt = $pdo->prepare("SELECT COUNT(*) FROM cart WHERE user_id = ?");
$stmt->execute([$_SESSION['user_id']]);
echo $stmt->fetchColumn();
?>
```

```php
// ajax/remove_from_cart.php
<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$cart_id = (int)$_POST['cart_id'];

$stmt = $pdo->prepare("DELETE FROM cart WHERE id = ? AND user_id = ?");
$stmt->execute([$cart_id, $_SESSION['user_id']]);

echo json_encode(['success' => true]);
?>

