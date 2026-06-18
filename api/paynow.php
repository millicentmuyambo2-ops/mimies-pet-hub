<?php
session_start();
require_once('../includes/config.php');

/*
|--------------------------------------------------------------------------
| PAYNOW CONFIGURATION
|--------------------------------------------------------------------------
*/
$PAYNOW_INTEGRATION_ID = '25126';
$PAYNOW_INTEGRATION_KEY = '5dc97723a8-39ba-42-82a6-2d360747fc1e';

// FIXED: Correct URLs for your project
$PAYNOW_RETURN_URL = 'http://localhost/Mimie\'sPetHub/payment-success.php';
$PAYNOW_RESULT_URL = 'http://localhost/Mimie\'sPetHub/api/payment-callback.php';

/*
|--------------------------------------------------------------------------
| GET FORM DATA
|--------------------------------------------------------------------------
*/
$fullname = trim($_POST['fullname'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$location = trim($_POST['location'] ?? '');
$address = trim($_POST['address'] ?? '');
$payment_method = trim($_POST['payment_method'] ?? '');
$reference = trim($_POST['reference'] ?? '');
$amount = floatval($_POST['amount'] ?? 0);
$subtotal = floatval($_POST['subtotal'] ?? 0);
$delivery_fee = floatval($_POST['delivery_fee'] ?? 0);
$notes = trim($_POST['notes'] ?? '');
$mobile_number = trim($_POST['mobile_number'] ?? '');

$cart_items = [];

if (!empty($_POST['cart_items'])) {
    $cart_items = json_decode($_POST['cart_items'], true);
    if (!is_array($cart_items)) {
        $cart_items = [];
    }
}

/*
|--------------------------------------------------------------------------
| VALIDATION
|--------------------------------------------------------------------------
*/
if (empty($fullname) || empty($email) || empty($phone) || empty($location) || empty($address) || empty($payment_method)) {
    $_SESSION['payment_error'] = "Please fill in all required fields.";
    header('Location: ../checkout.php?error=validation');
    exit();
}

if ($amount <= 0) {
    $_SESSION['payment_error'] = "Invalid payment amount.";
    header('Location: ../checkout.php?error=amount');
    exit();
}

/*
|--------------------------------------------------------------------------
| NORMALIZE PHONE NUMBER
|--------------------------------------------------------------------------
*/
if (preg_match('/^0\d{9}$/', $phone)) {
    $phone = '263' . substr($phone, 1);
}

if (!empty($mobile_number) && preg_match('/^0\d{9}$/', $mobile_number)) {
    $mobile_number = '263' . substr($mobile_number, 1);
}

$order_id = !empty($reference) ? $reference : 'ORD-' . time();

/*
|--------------------------------------------------------------------------
| SAVE ORDER TO DATABASE
|--------------------------------------------------------------------------
*/
try {
    // Check if orders table exists
    $stmt = $pdo->prepare("
        INSERT INTO orders (
            order_number, customer_name, customer_email, customer_phone,
            delivery_location, delivery_address, payment_method, subtotal,
            delivery_fee, total_amount, status, notes, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())
    ");
    $stmt->execute([
        $order_id, $fullname, $email, $phone, $location, $address,
        $payment_method, $subtotal, $delivery_fee, $amount, $notes
    ]);

    foreach ($cart_items as $item) {
        $stmt = $pdo->prepare("
            INSERT INTO order_items (order_number, pet_name, pet_type, quantity, price)
            VALUES (?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $order_id,
            $item['name'] ?? '',
            $item['type'] ?? '',
            $item['quantity'] ?? 1,
            $item['price'] ?? 0
        ]);
    }
} catch (PDOException $e) {
    error_log("ORDER SAVE ERROR: " . $e->getMessage());
    $_SESSION['payment_error'] = "Database error.";
    header('Location: ../checkout.php?error=db');
    exit();
}

/*
|--------------------------------------------------------------------------
| CASH ON DELIVERY - No PayNow needed
|--------------------------------------------------------------------------
*/
if ($payment_method === 'cash') {
    $stmt = $pdo->prepare("UPDATE orders SET status = 'cash_on_delivery' WHERE order_number = ?");
    $stmt->execute([$order_id]);
    unset($_SESSION['cart']);
    header('Location: ../payment-success.php?order=' . urlencode($order_id) . '&method=cash');
    exit();
}

/*
|--------------------------------------------------------------------------
| PAYNOW REQUEST DATA
|--------------------------------------------------------------------------
*/
$data = [
    'id' => $PAYNOW_INTEGRATION_ID,
    'key' => $PAYNOW_INTEGRATION_KEY,
    'reference' => $order_id,
    'amount' => $amount,
    'return_url' => $PAYNOW_RETURN_URL,
    'result_url' => $PAYNOW_RESULT_URL,
    'additional_info' => $fullname . ' - ' . $address,
    'email' => $email,
    'phonenumber' => $phone
];

switch ($payment_method) {
    case 'ecocash':
        $data['method'] = 'ecocash';
        $data['ecocash'] = !empty($mobile_number) ? $mobile_number : $phone;
        break;
    case 'innbucks':
        $data['method'] = 'innbucks';
        break;
    case 'card':
        $data['method'] = 'card';
        break;
    case 'bank':
        $data['method'] = 'bank';
        break;
}

/*
|--------------------------------------------------------------------------
| SEND TO PAYNOW
|--------------------------------------------------------------------------
*/
$url = 'https://www.paynow.co.zw/interface/initiatetransaction';

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);

$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curl_error = curl_error($ch);
curl_close($ch);

/*
|--------------------------------------------------------------------------
| DEBUG LOGS
|--------------------------------------------------------------------------
*/
error_log("PAYNOW HTTP CODE: " . $http_code);
error_log("PAYNOW RESPONSE: " . $response);

/*
|--------------------------------------------------------------------------
| HANDLE CURL FAILURE
|--------------------------------------------------------------------------
*/
if ($response === false || !empty($curl_error)) {
    $_SESSION['payment_error'] = "Connection failed: " . $curl_error;
    header('Location: ../checkout.php?error=curl');
    exit();
}

/*
|--------------------------------------------------------------------------
| PARSE PAYNOW RESPONSE
|--------------------------------------------------------------------------
*/
$output = [];
parse_str($response, $output);
error_log("PAYNOW PARSED: " . print_r($output, true));

/*
|--------------------------------------------------------------------------
| CHECK PAYNOW SUCCESS
|--------------------------------------------------------------------------
*/
if (isset($output['status']) && strtolower($output['status']) === 'ok') {
    if (!empty($output['browserurl'])) {
        // Clear cart before redirecting to PayNow
        unset($_SESSION['cart']);
        header('Location: ' . $output['browserurl']);
        exit();
    }
    $_SESSION['payment_error'] = "PayNow did not return a browser URL.";
    header('Location: ../checkout.php?error=no_browser_url');
    exit();
}

/*
|--------------------------------------------------------------------------
| PAYNOW ERROR
|--------------------------------------------------------------------------
*/
$error_message = $output['error'] ?? 'Unknown PayNow error';
$_SESSION['payment_error'] = $error_message;
header('Location: ../checkout.php?error=paynow');
exit();
?>