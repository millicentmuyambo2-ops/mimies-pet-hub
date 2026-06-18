<?php
session_start();
require_once('includes/config.php');

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// Add to cart
if (isset($_GET['add'])) {
    $pet_id = (int)$_GET['add'];
    
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch();
    
    if ($pet) {
        if (isset($_SESSION['cart'][$pet_id])) {
            $_SESSION['cart'][$pet_id]['quantity']++;
        } else {
            $_SESSION['cart'][$pet_id] = [
                'id' => $pet['id'],
                'name' => $pet['name'] ?? 'Unknown',
                'type' => $pet['type'] ?? 'Pet',
                'breed' => $pet['breed'] ?? 'Mixed',
                'age' => $pet['age'] ?? 'Adult',
                'price' => $pet['price'] ?? 0,
                'image' => $pet['image_url'] ?? null,
                'quantity' => 1
            ];
        }
    }
    header('Location: cart.php');
    exit();
}

// Update quantity
if (isset($_POST['update_cart'])) {
    foreach ($_POST['quantity'] as $id => $qty) {
        $qty = (int)$qty;
        if ($qty <= 0) {
            unset($_SESSION['cart'][$id]);
        } else {
            $_SESSION['cart'][$id]['quantity'] = $qty;
        }
    }
    header('Location: cart.php');
    exit();
}

// Remove from cart
if (isset($_GET['remove'])) {
    unset($_SESSION['cart'][$_GET['remove']]);
    header('Location: cart.php');
    exit();
}

// Clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = [];
    header('Location: cart.php?cleared=1');
    exit();
}

// Calculate totals
$cart_items = $_SESSION['cart'];
$subtotal = 0;
$item_count = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
    $item_count += $item['quantity'];
}
$tax = $subtotal * 0.05;
$total = $subtotal + $tax;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Mimie's Pet Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        .cart-container { max-width: 1200px; margin: 40px auto; padding: 20px; display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
        .cart-items { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .cart-item { display: grid; grid-template-columns: 100px 1fr auto; gap: 20px; align-items: center; padding: 20px 0; border-bottom: 1px solid #eee; }
        .cart-item:last-child { border-bottom: none; }
        .cart-item img { width: 80px; height: 80px; object-fit: cover; border-radius: 12px; }
        .item-details h3 { color: #ff5c8a; margin-bottom: 5px; font-size: 18px; }
        .item-details p { color: #666; font-size: 14px; margin: 3px 0; }
        .item-price { font-weight: bold; color: #333; font-size: 16px; }
        .quantity-input { width: 60px; padding: 8px; text-align: center; border: 1px solid #ddd; border-radius: 8px; }
        .remove-btn { background: #dc3545; color: white; border: none; padding: 6px 12px; border-radius: 8px; cursor: pointer; font-size: 12px; margin-top: 8px; display: inline-block; text-decoration: none; }
        .cart-summary { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); position: sticky; top: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #eee; }
        .summary-row.total { font-weight: bold; font-size: 20px; color: #ff5c8a; border-top: 2px solid #ff7aa2; border-bottom: none; margin-top: 10px; padding-top: 15px; }
        
        .checkout-btn { background: #28a745; color: white; border: none; padding: 15px; border-radius: 10px; width: 100%; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; text-align: center; display: block; text-decoration: none; }
        .checkout-btn:hover { background: #218838; }
        .update-btn { background: #ffc107; color: #333; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; font-weight: 500; }
        .clear-btn { background: #dc3545; color: white; border: none; padding: 8px 20px; border-radius: 8px; cursor: pointer; margin-left: 10px; text-decoration: none; display: inline-block; }
        .action-buttons { margin-top: 20px; text-align: right; }
        
        .empty-cart { text-align: center; padding: 60px; background: white; border-radius: 20px; max-width: 600px; margin: 40px auto; }
        .btn-continue { background: #ff7aa2; color: white; padding: 12px 30px; text-decoration: none; border-radius: 30px; display: inline-block; margin-top: 20px; }
        h1 { color: #ff5c8a; margin-bottom: 25px; font-size: 28px; }
        .payment-methods { margin-top: 20px; padding-top: 20px; border-top: 1px solid #eee; }
        .payment-icons { display: flex; gap: 10px; flex-wrap: wrap; margin-top: 10px; }
        .payment-icons span { background: #f0f0f0; padding: 6px 12px; border-radius: 20px; font-size: 11px; display: inline-flex; align-items: center; gap: 6px; }
        .success-msg { background: #d4edda; color: #155724; padding: 12px; border-radius: 10px; margin-bottom: 20px; text-align: center; }
        @media (max-width: 768px) { .cart-container { grid-template-columns: 1fr; } .cart-item { grid-template-columns: 1fr; text-align: center; } .cart-item img { margin: 0 auto; } }
    </style>
</head>
<body>

<?php require_once('header.php'); ?>

<div class="cart-container">
    <div class="cart-items">
        <h1><i class="fas fa-shopping-cart"></i> Your Shopping Cart</h1>
        
        <?php if(isset($_GET['cleared'])): ?>
        <div class="success-msg"><i class="fas fa-check-circle"></i> Cart has been cleared!</div>
        <?php endif; ?>
        
        <?php if(count($cart_items) > 0): ?>
        <form method="POST">
            <?php foreach($cart_items as $id => $item): ?>
            <div class="cart-item">
                <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/80x80?text=Pet'); ?>">
                <div class="item-details">
                    <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                    <p><?php echo ucfirst($item['type']); ?> | <?php echo htmlspecialchars($item['breed'] ?? 'Mixed'); ?></p>
                    <p>Age: <?php echo htmlspecialchars($item['age'] ?? 'Adult'); ?></p>
                    <p class="item-price">$<?php echo number_format($item['price'], 2); ?></p>
                </div>
                <div>
                    <input type="number" name="quantity[<?php echo $id; ?>]" value="<?php echo $item['quantity']; ?>" min="0" max="10" class="quantity-input">
                    <br>
                    <a href="?remove=<?php echo $id; ?>" class="remove-btn" onclick="return confirm('Remove this item?')"><i class="fas fa-trash"></i> Remove</a>
                </div>
            </div>
            <?php endforeach; ?>
            <div class="action-buttons">
                <button type="submit" name="update_cart" class="update-btn"><i class="fas fa-sync-alt"></i> Update Cart</button>
                <a href="?clear=1" class="clear-btn" onclick="return confirm('Clear entire cart?')"><i class="fas fa-trash-alt"></i> Clear Cart</a>
            </div>
        </form>
        <?php else: ?>
        <div class="empty-cart">
            <i class="fas fa-shopping-cart"></i>
            <h2>Your cart is empty</h2>
            <p>Looks like you haven't added any pets to your cart yet.</p>
            <a href="index.php" class="btn-continue"><i class="fas fa-arrow-left"></i> Continue Shopping</a>
        </div>
        <?php endif; ?>
    </div>
    
    <?php if(count($cart_items) > 0): ?>
    <div class="cart-summary">
        <h3>Order Summary</h3>
        <div class="summary-row">
            <span>Subtotal (<?php echo $item_count; ?> items):</span>
            <span>$<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="summary-row">
            <span>Tax (5%):</span>
            <span>$<?php echo number_format($tax, 2); ?></span>
        </div>
        <div class="summary-row total">
            <span>Total:</span>
            <span>$<?php echo number_format($total, 2); ?></span>
        </div>
        
        <div class="payment-methods">
            <h4>We Accept:</h4>
            <div class="payment-icons">
                <span><i class="fas fa-mobile-alt"></i> EcoCash</span>
                <span><i class="fas fa-shopping-basket"></i> InnBucks</span>
                <span><i class="fas fa-university"></i> Bank Transfer</span>
                <span><i class="fas fa-credit-card"></i> Card</span>
                <span><i class="fas fa-money-bill-wave"></i> Cash on Delivery</span>
            </div>
        </div>
        
        <a href="checkout.php" class="checkout-btn">
            <i class="fas fa-credit-card"></i> Proceed to Payment
        </a>
    </div>
    <?php endif; ?>
</div>

<?php require_once('footer.php'); ?>
</body>
</html>