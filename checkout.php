<?php
session_start();
require_once('includes/config.php');

// Check for payment errors
if (isset($_GET['error'])) {
    $error_message = $_SESSION['payment_error'] ?? '';
    if ($error_message) {
        echo '<script>alert("Payment Error: ' . addslashes($error_message) . '");</script>';
        unset($_SESSION['payment_error']);
    }
}

// Get cart items
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: cart.php');
    exit();
}

$cart_items = $_SESSION['cart'];
$subtotal = 0;
foreach ($cart_items as $item) {
    $subtotal += $item['price'] * $item['quantity'];
}
$delivery_fee = 10;
$total = $subtotal + $delivery_fee;

// Generate unique order reference
$reference = 'INV-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout - Mimie's Pet Hub</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        .checkout-container { max-width: 1200px; margin: 40px auto; padding: 20px; display: grid; grid-template-columns: 1fr 400px; gap: 30px; }
        .checkout-form { background: white; border-radius: 20px; padding: 30px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); }
        .order-summary { background: white; border-radius: 20px; padding: 25px; box-shadow: 0 2px 15px rgba(0,0,0,0.08); position: sticky; top: 20px; }
        .form-group { margin-bottom: 20px; }
        .form-group label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        .form-group input, .form-group select, .form-group textarea { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 10px; font-family: inherit; }
        .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 15px; }
        h2, h3 { color: #ff5c8a; margin-bottom: 20px; }
        .summary-row { display: flex; justify-content: space-between; padding: 10px 0; border-bottom: 1px solid #eee; }
        .summary-row.total { font-weight: bold; font-size: 18px; color: #ff5c8a; border-top: 2px solid #ff7aa2; margin-top: 10px; padding-top: 15px; }
        .btn-place-order { background: #28a745; color: white; border: none; padding: 15px; border-radius: 10px; width: 100%; font-size: 18px; font-weight: bold; cursor: pointer; margin-top: 20px; transition: background 0.3s; }
        .btn-place-order:hover { background: #218838; }
        .btn-place-order:disabled { opacity: 0.7; cursor: not-allowed; }
        .payment-methods { margin: 20px 0; }
        .payment-option { border: 2px solid #eee; border-radius: 12px; padding: 12px; margin-bottom: 10px; cursor: pointer; display: flex; align-items: center; gap: 12px; transition: all 0.3s; }
        .payment-option.selected { border-color: #ff7aa2; background: #fff0f5; }
        .payment-option i { font-size: 24px; width: 40px; }
        .payment-option:hover { transform: translateX(5px); }
        .cart-item { display: flex; gap: 15px; padding: 10px 0; border-bottom: 1px solid #eee; }
        .cart-item img { width: 50px; height: 50px; object-fit: cover; border-radius: 8px; }
        .loader { display: none; text-align: center; margin-top: 20px; padding: 20px; background: #f9f9f9; border-radius: 10px; }
        .loader i { font-size: 30px; color: #ff7aa2; animation: spin 1s linear infinite; }
        .loader p { margin-top: 10px; color: #666; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .error-message { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .success-message { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 15px; }
        .payment-badge { display: inline-block; background: #ff7aa2; color: white; padding: 2px 8px; border-radius: 20px; font-size: 10px; margin-left: 8px; }
        @media (max-width: 768px) { .checkout-container { grid-template-columns: 1fr; } .row-2 { grid-template-columns: 1fr; } }
    </style>
</head>
<body>

<?php require_once('header.php'); ?>

<div class="checkout-container">
    <div class="checkout-form">
        <h2><i class="fas fa-credit-card"></i> Payment Details</h2>
        
        <div id="formMessage"></div>
        
        <form id="checkoutForm" action="api/paynow.php" method="POST">
            <div class="row-2">
                <div class="form-group">
                    <label>Full Name *</label>
                    <input type="text" id="fullname" name="fullname" required>
                </div>
                <div class="form-group">
                    <label>Email *</label>
                    <input type="email" id="email" name="email" required>
                </div>
            </div>
            
            <div class="row-2">
                <div class="form-group">
                    <label>Phone Number *</label>
                    <input type="tel" id="phone" name="phone" required placeholder="077XXXXXXX">
                </div>
                <div class="form-group">
                    <label>Delivery Location *</label>
                    <select id="location" name="location" required>
                        <option value="">Select Location</option>
                        <option value="Harare">Harare</option>
                        <option value="Bulawayo">Bulawayo</option>
                        <option value="Mutare">Mutare</option>
                        <option value="Gweru">Gweru</option>
                        <option value="Bindura">Bindura</option>
                        <option value="Masvingo">Masvingo</option>
                        <option value="Kwekwe">Kwekwe</option>
                        <option value="Kadoma">Kadoma</option>
                        <option value="Chitungwiza">Chitungwiza</option>
                        <option value="Other">Other (Specify below)</option>
                    </select>
                </div>
            </div>
            
            <div class="form-group">
                <label>Full Delivery Address *</label>
                <textarea id="address" name="address" rows="2" required placeholder="Street, Suburb, City"></textarea>
            </div>
            
            <div class="payment-methods">
                <label style="font-weight: 500; margin-bottom: 10px; display: block;">Select Payment Method *</label>
                
                <div class="payment-option" data-method="ecocash" data-name="EcoCash">
                    <i class="fas fa-mobile-alt" style="color: #25D366;"></i>
                    <div><strong>EcoCash</strong><br><small>Pay using EcoCash mobile money</small></div>
                    <span class="payment-badge">Popular</span>
                </div>
                
                <div class="payment-option" data-method="innbucks" data-name="InnBucks">
                    <i class="fas fa-shopping-basket" style="color: #ff9800;"></i>
                    <div><strong>InnBucks</strong><br><small>Pay using InnBucks voucher</small></div>
                </div>
                
                <div class="payment-option" data-method="card" data-name="Card">
                    <i class="fas fa-credit-card" style="color: #ffc107;"></i>
                    <div><strong>Credit/Debit Card</strong><br><small>Visa, Mastercard, American Express</small></div>
                    <span class="payment-badge">Secure</span>
                </div>
                
                <div class="payment-option" data-method="bank" data-name="Bank Transfer">
                    <i class="fas fa-university" style="color: #2196f3;"></i>
                    <div><strong>Bank Transfer</strong><br><small>Transfer to our bank account</small></div>
                </div>
                
                <div class="payment-option" data-method="cash" data-name="Cash on Delivery">
                    <i class="fas fa-money-bill-wave" style="color: #28a745;"></i>
                    <div><strong>Cash on Delivery</strong><br><small>Pay when pet is delivered</small></div>
                </div>
                
                <input type="hidden" id="payment_method" name="payment_method" value="">
            </div>
            
            <div class="form-group" id="mobileNumberField" style="display: none;">
                <label>EcoCash Mobile Number</label>
                <input type="tel" id="mobile_number" name="mobile_number" placeholder="077XXXXXXX">
                <small style="color: #666; display: block; margin-top: 5px;">Enter the EcoCash number you want to pay from</small>
            </div>
            
            <div class="form-group">
                <label>Notes (Optional)</label>
                <textarea id="notes" name="notes" rows="2" placeholder="Special instructions for delivery"></textarea>
            </div>
            
            <input type="hidden" name="reference" value="<?php echo $reference; ?>">
            <input type="hidden" name="amount" value="<?php echo $total; ?>">
            <input type="hidden" name="cart_items" value='<?php echo htmlspecialchars(json_encode($cart_items)); ?>'>
            <input type="hidden" name="subtotal" value="<?php echo $subtotal; ?>">
            <input type="hidden" name="delivery_fee" value="<?php echo $delivery_fee; ?>">
            
            <button type="submit" class="btn-place-order" id="placeOrderBtn">
                <i class="fas fa-lock"></i> Complete Payment - $<?php echo number_format($total, 2); ?>
            </button>
        </form>
        
        <div class="loader" id="loader">
            <i class="fas fa-spinner"></i>
            <p>Processing your payment... Please wait.</p>
        </div>
    </div>
    
    <div class="order-summary">
        <h3>Order Summary</h3>
        <?php foreach($cart_items as $item): ?>
        <div class="cart-item">
            <img src="<?php echo htmlspecialchars($item['image'] ?? 'https://via.placeholder.com/50x50?text=Pet'); ?>">
            <div>
                <strong><?php echo htmlspecialchars($item['name']); ?></strong>
                <div><?php echo $item['quantity']; ?> x $<?php echo number_format($item['price'], 2); ?></div>
            </div>
            <div style="margin-left: auto;">$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></div>
        </div>
        <?php endforeach; ?>
        
        <div class="summary-row"><span>Subtotal:</span><span>$<?php echo number_format($subtotal, 2); ?></span></div>
        <div class="summary-row"><span>Delivery Fee:</span><span>$<?php echo number_format($delivery_fee, 2); ?></span></div>
        <div class="summary-row total"><span>Total:</span><span>$<?php echo number_format($total, 2); ?></span></div>
        
        <div style="font-size: 12px; color: #666; margin-top: 15px; text-align: center;">
            <i class="fas fa-shield-alt"></i> Secure payment powered by PayNow
        </div>
        <div style="font-size: 11px; color: #999; margin-top: 10px; text-align: center;">
            <i class="fas fa-check-circle"></i> 100% Secure Transactions
        </div>
    </div>
</div>

<script>
// Payment method selection
let selectedPayment = '';
let selectedPaymentName = 'EcoCash';

document.querySelectorAll('.payment-option').forEach(opt => {
    opt.addEventListener('click', function() {
        document.querySelectorAll('.payment-option').forEach(o => o.classList.remove('selected'));
        this.classList.add('selected');
        selectedPayment = this.dataset.method;
        selectedPaymentName = this.dataset.name;
        document.getElementById('payment_method').value = selectedPayment;
        
        // Update button text
        const btn = document.getElementById('placeOrderBtn');
        const total = <?php echo $total; ?>;
        btn.innerHTML = `<i class="fas fa-lock"></i> Pay $${total.toFixed(2)} with ${selectedPaymentName}`;
        
        // Show/hide mobile number field for EcoCash
        const mobileField = document.getElementById('mobileNumberField');
        if (selectedPayment === 'ecocash') {
            mobileField.style.display = 'block';
        } else {
            mobileField.style.display = 'none';
        }
    });
});

// Form submission
document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    // Validate payment method
    if (!selectedPayment) {
        showMessage('Please select a payment method', 'error');
        return;
    }
    
    // Validate required fields
    const fullname = document.getElementById('fullname').value.trim();
    const email = document.getElementById('email').value.trim();
    const phone = document.getElementById('phone').value.trim();
    const location = document.getElementById('location').value;
    const address = document.getElementById('address').value.trim();
    
    if (!fullname) {
        showMessage('Please enter your full name', 'error');
        return;
    }
    if (!email) {
        showMessage('Please enter your email address', 'error');
        return;
    }
    if (!phone) {
        showMessage('Please enter your phone number', 'error');
        return;
    }
    if (!location) {
        showMessage('Please select delivery location', 'error');
        return;
    }
    if (!address) {
        showMessage('Please enter delivery address', 'error');
        return;
    }
    
    // Email validation
    const emailRegex = /^[^\s@]+@([^\s@]+\.)+[^\s@]+$/;
    if (!emailRegex.test(email)) {
        showMessage('Please enter a valid email address', 'error');
        return;
    }
    
    // Phone validation for Zimbabwe
    const phoneRegex = /^(07|07[3-8]|078|071)[0-9]{7}$/;
    if (!phoneRegex.test(phone)) {
        showMessage('Please enter a valid Zimbabwe phone number (e.g., 0771234567)', 'error');
        return;
    }
    
    // EcoCash mobile number validation
    if (selectedPayment === 'ecocash') {
        const mobile = document.getElementById('mobile_number').value.trim();
        if (!mobile) {
            showMessage('Please enter your EcoCash mobile number', 'error');
            return;
        }
        if (!phoneRegex.test(mobile)) {
            showMessage('Please enter a valid EcoCash mobile number', 'error');
            return;
        }
    }
    
    // Show loader and disable button
    document.getElementById('loader').style.display = 'block';
    const submitBtn = document.getElementById('placeOrderBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    // Submit the form to PayNow
    this.submit();
});

function showMessage(msg, type) {
    const div = document.getElementById('formMessage');
    div.innerHTML = `<div class="${type === 'success' ? 'success-message' : 'error-message'}">${msg}</div>`;
    setTimeout(() => {
        div.innerHTML = '';
    }, 5000);
}
</script>

<?php require_once('footer.php'); ?>
</body>
</html>