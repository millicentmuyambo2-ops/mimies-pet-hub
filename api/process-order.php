<?php
header('Content-Type: application/json');
require_once('../includes/config.php');

$data = json_decode(file_get_contents('php://input'), true);

if (!$data || empty($data['cart_items'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid order data']);
    exit();
}

$order_id = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));

try {
    // Create orders table if not exists
    $pdo->exec("CREATE TABLE IF NOT EXISTS `orders` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `order_number` VARCHAR(50) UNIQUE,
        `customer_name` VARCHAR(200),
        `customer_email` VARCHAR(200),
        `customer_phone` VARCHAR(50),
        `customer_id` VARCHAR(100),
        `delivery_address` TEXT,
        `payment_method` VARCHAR(50),
        `payment_plan` VARCHAR(50),
        `plan_months` INT,
        `subtotal` DECIMAL(10,2),
        `tax` DECIMAL(10,2),
        `total_amount` DECIMAL(10,2),
        `plan_total` DECIMAL(10,2),
        `monthly_payment` DECIMAL(10,2),
        `amount_paid` DECIMAL(10,2) DEFAULT 0,
        `status` VARCHAR(50) DEFAULT 'pending',
        `notes` TEXT,
        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Create payment_schedule table
    $pdo->exec("CREATE TABLE IF NOT EXISTS `payment_schedule` (
        `id` INT PRIMARY KEY AUTO_INCREMENT,
        `order_number` VARCHAR(50),
        `installment_no` INT,
        `due_date` DATE,
        `amount` DECIMAL(10,2),
        `status` VARCHAR(50) DEFAULT 'pending',
        `paid_date` DATE,
        FOREIGN KEY (`order_number`) REFERENCES `orders`(`order_number`)
    )");
    
    $pdo->beginTransaction();
    
    // Insert order
    $stmt = $pdo->prepare("INSERT INTO orders (order_number, customer_name, customer_email, customer_phone, customer_id, delivery_address, payment_method, payment_plan, plan_months, subtotal, tax, total_amount, plan_total, monthly_payment, status, notes) 
                           VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?)");
    $stmt->execute([
        $order_id,
        $data['fullname'],
        $data['email'],
        $data['phone'],
        $data['id_number'],
        $data['address'],
        $data['payment_method'],
        $data['plan_name'],
        $data['plan_months'],
        $data['subtotal'],
        $data['tax'],
        $data['total'],
        $data['plan_total'],
        $data['monthly_payment'],
        $data['notes']
    ]);
    
    // Create payment schedule
    for ($i = 1; $i <= $data['plan_months']; $i++) {
        $due_date = date('Y-m-d', strtotime("+$i months"));
        $stmt = $pdo->prepare("INSERT INTO payment_schedule (order_number, installment_no, due_date, amount, status) VALUES (?, ?, ?, ?, 'pending')");
        $stmt->execute([$order_id, $i, $due_date, $data['monthly_payment']]);
    }
    
    // Clear cart
    session_start();
    $_SESSION['cart'] = [];
    
    $pdo->commit();
    
    // Send confirmation email
    $to = $data['email'];
    $subject = "Order Confirmation - Mimie's Pet Hub";
    $message = "Dear {$data['fullname']},\n\n";
    $message .= "Thank you for your order!\n\n";
    $message .= "Order Number: $order_id\n";
    $message .= "Payment Plan: {$data['plan_name']}\n";
    $message .= "Monthly Payment: $" . number_format($data['monthly_payment'], 2) . "\n";
    $message .= "Total Amount: $" . number_format($data['plan_total'], 2) . "\n\n";
    $message .= "You will receive payment reminders each month.\n\n";
    $message .= "--\nMimie's Pet Hub\nBindura, Zimbabwe";
    
    mail($to, $subject, $message, "From: orders@mimiespethub.com");
    
    echo json_encode(['success' => true, 'message' => "Order placed successfully! Order #$order_id", 'order_id' => $order_id]);
    
} catch(PDOException $e) {
    $pdo->rollBack();
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}
?>
