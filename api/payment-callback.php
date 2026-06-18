<?php
session_start();
require_once('../includes/config.php');

// Get PayNow callback data
$status = $_GET['status'] ?? '';
$reference = $_GET['reference'] ?? '';
$paynow_reference = $_GET['paynowreference'] ?? '';
$amount = $_GET['amount'] ?? 0;
$result = $_GET['result'] ?? '';

// Log the callback for debugging
$log_data = [
    'time' => date('Y-m-d H:i:s'),
    'reference' => $reference,
    'status' => $status,
    'amount' => $amount,
    'result' => $result,
    'full_data' => $_GET
];
file_put_contents('../paynow_callback.log', json_encode($log_data) . "\n", FILE_APPEND);

if ($status == 'Ok') {
    // Payment successful
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'paid', payment_reference = ?, updated_at = NOW() WHERE order_number = ?");
        $stmt->execute([$paynow_reference, $reference]);
        
        // Clear the cart
        unset($_SESSION['cart']);
        
        // Send email confirmation
        $stmt = $pdo->prepare("SELECT * FROM orders WHERE order_number = ?");
        $stmt->execute([$reference]);
        $order = $stmt->fetch();
        
        if ($order) {
            $to = $order['customer_email'];
            $subject = "Payment Confirmation - Mimie's Pet Hub";
            $message = "Dear " . $order['customer_name'] . ",\n\n";
            $message .= "Your payment has been confirmed!\n\n";
            $message .= "Order Number: " . $reference . "\n";
            $message .= "Amount Paid: $" . $order['total_amount'] . "\n\n";
            $message .= "We will prepare your pet for delivery.\n\n";
            $message .= "Thank you for choosing Mimie's Pet Hub!\n";
            $message .= "--\nMimie's Pet Hub\nBindura, Zimbabwe";
            
            mail($to, $subject, $message, "From: payments@mimiespethub.com");
        }
        
        echo "OK";
    } catch(PDOException $e) {
        echo "Error updating order: " . $e->getMessage();
    }
} else {
    // Payment failed
    try {
        $stmt = $pdo->prepare("UPDATE orders SET status = 'failed', updated_at = NOW() WHERE order_number = ?");
        $stmt->execute([$reference]);
        echo "Payment failed";
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>