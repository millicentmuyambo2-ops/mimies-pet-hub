<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Get cart count
$cart_count = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $cart_count += $item['quantity'];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mimie's Pet Hub - Zimbabwe's Pet Marketplace</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: #f5f5f5; }
        
        .main-header {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 15px rgba(255,107,139,0.2);
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .logo {
            display: flex;
            align-items: center;
            gap: 0.8rem;
        }
        
        .logo img {
            height: 45px;
            border-radius: 50%;
            background: white;
            padding: 5px;
        }
        
        .logo h1 {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
        }
        
        .nav-links {
            display: flex;
            flex-wrap: wrap;
            gap: 0.3rem;
            align-items: center;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255,255,255,0.15);
            transition: all 0.3s ease;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
        }
        
        .nav-links a:hover {
            background: white;
            color: #ff6b8b;
            transform: translateY(-2px);
        }
        
        .cart-link {
            position: relative;
        }
        
        .cart-badge {
            position: absolute;
            top: -8px;
            right: -8px;
            background: #ff4757;
            color: white;
            border-radius: 50%;
            padding: 2px 6px;
            font-size: 10px;
            font-weight: bold;
            min-width: 18px;
            text-align: center;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        footer {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            margin-top: 2rem;
        }
        
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .nav-links {
                justify-content: center;
            }
            .nav-links a {
                padding: 0.4rem 0.8rem;
                font-size: 0.75rem;
            }
        }
    </style>
</head>
<body>
<header class="main-header">
    <div class="logo">
        <img src="assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/45?text=🐾'">
        <h1>Mimie's Pet Hub</h1>
    </div>
    <div class="nav-links">
        <a href="index.php"><i class="fas fa-home"></i> Home</a>
        <a href="cart.php" class="cart-link">
            <i class="fas fa-shopping-cart"></i> Cart
            <?php if($cart_count > 0): ?>
            <span class="cart-badge"><?php echo $cart_count; ?></span>
            <?php endif; ?>
        </a>
        <a href="sell-pet.php"><i class="fas fa-plus-circle"></i> Sell Pet</a>
        <a href="lost-found.php"><i class="fas fa-search"></i> Lost & Found</a>
        <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
        <a href="my-pets.php"><i class="fas fa-paw"></i> My Pets</a>
        <?php if(isset($_SESSION['user_id']) || isset($_SESSION['logged_in'])): ?>
            <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php"><i class="fas fa-shield-alt"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        <?php else: ?>
            <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <a href="register.php"><i class="fas fa-user-plus"></i> Register</a>
        <?php endif; ?>
    </div>
</header>