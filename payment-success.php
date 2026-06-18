<?php
session_start();
$order_id = $_GET['order'] ?? '';
$method = $_GET['method'] ?? '';
$amount = $_GET['amount'] ?? 0;

// Clear cart if order was successful
if ($order_id) {
    unset($_SESSION['cart']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment Successful - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .success-container { max-width: 550px; margin: 20px; background: white; border-radius: 20px; padding: 40px; text-align: center; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .success-icon { font-size: 80px; color: #28a745; margin-bottom: 20px; }
        h1 { color: #28a745; margin-bottom: 10px; font-size: 28px; }
        .order-id { background: #f0f0f0; padding: 15px; border-radius: 10px; margin: 20px 0; font-family: monospace; }
        .payment-details { background: #f9f9f9; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: left; }
        .payment-instructions { background: #fff3e0; padding: 15px; border-radius: 10px; margin: 20px 0; text-align: left; }
        .btn { background: #ff7aa2; color: white; padding: 12px 25px; border-radius: 8px; text-decoration: none; display: inline-block; margin: 5px; transition: all 0.3s; }
        .btn:hover { background: #ff4f83; transform: translateY(-2px); }
        .btn-secondary { background: #6c757d; }
        .btn-secondary:hover { background: #5a6268; }
        .whatsapp-btn { background: #25D366; }
        .whatsapp-btn:hover { background: #1da851; }
        @media (max-width: 768px) { .success-container { margin: 15px; padding: 25px; } }
    </style>
</head>
<body>
<div class="success-container">
    <div class="success-icon">
        <i class="fas fa-check-circle"></i>
    </div>
    <h1>Payment Successful!</h1>
    <p>Thank you for your purchase from Mimie's Pet Hub!</p>
    
    <div class="order-id">
        <strong>Order Number:</strong> <?php echo htmlspecialchars($order_id); ?>
    </div>
    
    <?php if($method == 'cash'): ?>
    <div class="payment-instructions">
        <h4 style="color: #ff9800; margin-bottom: 10px;"><i class="fas fa-money-bill-wave"></i> Cash on Delivery</h4>
        <p>Your order has been confirmed for Cash on Delivery.</p>
        <p>Please have the exact amount of <strong>$<?php echo number_format($amount, 2); ?></strong> ready upon delivery.</p>
        <p>We will contact you within 24 hours to arrange delivery.</p>
    </div>
    <?php elseif($method == 'ecocash'): ?>
    <div class="payment-instructions">
        <h4 style="color: #25D366; margin-bottom: 10px;"><i class="fas fa-mobile-alt"></i> EcoCash Payment</h4>
        <p><strong>Payment Instructions:</strong></p>
        <ol style="margin-left: 20px; margin-top: 10px;">
            <li>Dial <strong>*151#</strong> on your EcoCash registered phone</li>
            <li>Select <strong>"Pay Bill"</strong> (Option 4)</li>
            <li>Enter Merchant Code: <strong>123456</strong></li>
            <li>Enter Amount: <strong>$<?php echo number_format($amount, 2); ?></strong></li>
            <li>Enter Reference: <strong><?php echo $order_id; ?></strong></li>
            <li>Enter your EcoCash PIN to confirm</li>
        </ol>
        <p style="margin-top: 10px;">After payment, your order will be processed immediately.</p>
    </div>
    <?php elseif($method == 'innbucks'): ?>
    <div class="payment-instructions">
        <h4 style="color: #ff9800; margin-bottom: 10px;"><i class="fas fa-shopping-basket"></i> InnBucks Payment</h4>
        <p><strong>Payment Instructions:</strong></p>
        <ol style="margin-left: 20px; margin-top: 10px;">
            <li>Visit any InnBucks outlet nationwide</li>
            <li>Provide your InnBucks voucher or cash</li>
            <li>Pay to: <strong>Mimie's Pet Hub</strong></li>
            <li>Reference: <strong><?php echo $order_id; ?></strong></li>
            <li>Amount: <strong>$<?php echo number_format($amount, 2); ?></strong></li>
        </ol>
    </div>
    <?php elseif($method == 'card'): ?>
    <div class="payment-instructions">
        <h4 style="color: #ffc107; margin-bottom: 10px;"><i class="fas fa-credit-card"></i> Card Payment</h4>
        <p>Your card payment has been processed successfully.</p>
        <p>A receipt has been sent to your email.</p>
    </div>
    <?php elseif($method == 'bank'): ?>
    <div class="payment-instructions">
        <h4 style="color: #2196f3; margin-bottom: 10px;"><i class="fas fa-university"></i> Bank Transfer</h4>
        <p><strong>Bank Details:</strong></p>
        <p>Bank: CBZ Bank</p>
        <p>Account Name: Mimie's Pet Hub</p>
        <p>Account Number: 1234567890</p>
        <p>Branch: Bindura</p>
        <p>Reference: <?php echo $order_id; ?></p>
        <p>Amount: $<?php echo number_format($amount, 2); ?></p>
    </div>
    <?php endif; ?>
    
    <div class="payment-details">
        <p><i class="fas fa-check-circle" style="color: #28a745;"></i> Your payment has been confirmed</p>
        <p><i class="fas fa-truck"></i> We will prepare your pet for delivery</p>
        <p><i class="fas fa-envelope"></i> A confirmation email has been sent to your inbox</p>
        <p><i class="fas fa-clock"></i> Delivery takes 2-5 business days</p>
    </div>
    
    <p>We will contact you within 24 hours to arrange delivery.</p>
    
    <div style="margin-top: 25px;">
        <a href="index.php" class="btn"><i class="fas fa-home"></i> Continue Shopping</a>
        <a href="my-orders.php" class="btn btn-secondary"><i class="fas fa-box"></i> View My Orders</a>
        <a href="https://wa.me/263771234567?text=Hello%20I%20have%20a%20question%20about%20order%20<?php echo $order_id; ?>" class="btn whatsapp-btn" target="_blank"><i class="fab fa-whatsapp"></i> WhatsApp Support</a>
    </div>
</div>
</body>
</html>