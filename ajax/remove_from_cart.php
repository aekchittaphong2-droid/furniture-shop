<?php
require_once '../config/database.php';
require_once '../includes/functions.php';

header('Content-Type: application/json');

if(!isLoggedIn()) {
    echo json_encode(['success' => false]);
    exit();
}

$cart_id = (int)$_POST['cart_id'];
$quantity = (int)$_POST['quantity'];

if($quantity < 1) {
    echo json_encode(['success' => false]);
    exit();
}

$stmt = $pdo->prepare("UPDATE cart SET quantity = ? WHERE id = ? AND user_id = ?");
$stmt->execute([$quantity, $cart_id, $_SESSION['user_id']]);

echo json_encode(['success' => true]);
?>