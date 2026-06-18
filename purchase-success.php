<?php
session_start();
require_once('includes/config.php');

if(!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$pet_id = $_GET['pet_id'] ?? 0;
$query = "UPDATE pets SET status = 'sold' WHERE id = $pet_id";
mysqli_query($conn, $query);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Purchase Successful</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <div class="success-container">
        <h2>Thank You for Your Purchase!</h2>
        <p>Your transaction has been completed successfully.</p>
        <a href="dashboard.php">Return to Dashboard</a>
    </div>
</body>
</html>