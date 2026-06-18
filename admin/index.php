<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get statistics
$stats = [];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM pets WHERE status = 'available'");
$stats['available_pets'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM user_pets WHERE status = 'pending'");
$stats['pending_user_pets'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM orders WHERE status = 'pending'");
$stats['pending_orders'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'");
$stats['pending_reports'] = $stmt->fetch()['count'];

$stmt = $pdo->query("SELECT COUNT(*) as count FROM sales");
$stats['total_sales'] = $stmt->fetch()['count'];

// Recent orders
$recent_orders = $pdo->query("SELECT * FROM orders ORDER BY created_at DESC LIMIT 5")->fetchAll();

// Recent user pets
$recent_user_pets = $pdo->query("SELECT * FROM user_pets ORDER BY created_at DESC LIMIT 5")->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffe0e5 100%);
            min-height: 100vh;
        }
        .main-header {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 15px rgba(255,107,139,0.2);
        }
        .logo { display: flex; align-items: center; gap: 0.8rem; }
        .logo img { height: 45px; border-radius: 50%; background: white; padding: 5px; }
        .logo h1 { color: white; font-size: 1.3rem; }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255,255,255,0.15);
            margin-left: 0.5rem;
            transition: 0.3s;
        }
        .nav-links a:hover { background: white; color: #ff6b8b; transform: translateY(-2px); }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(255,107,139,0.08);
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-card h2 { font-size: 2.2rem; color: #ff6b8b; font-weight: 700; }
        .stat-card p { color: #8a5a6a; font-size: 0.85rem; }
        .stat-card.pending h2 { color: #ff9800; }
        .recent-section {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 1.5rem;
            box-shadow: 0 5px 15px rgba(255,107,139,0.08);
        }
        .recent-section h3 { color: #ff6b8b; margin-bottom: 1rem; font-weight: 600; }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 0.8rem; text-align: left; border-bottom: 1px solid #ffe0e5; }
        th { color: #8a5a6a; font-weight: 600; font-size: 0.85rem; }
        .status-badge { padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .status-pending { background: #fff3e0; color: #ff9800; }
        .status-approved { background: #e8f5e9; color: #4caf50; }
        .status-paid { background: #e3f2fd; color: #2196f3; }
        .menu-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .menu-card {
            background: white;
            padding: 1.5rem;
            border-radius: 20px;
            text-align: center;
            text-decoration: none;
            color: #5a2a3a;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(255,107,139,0.08);
        }
        .menu-card:hover { transform: translateY(-5px); box-shadow: 0 10px 25px rgba(255,107,139,0.15); }
        .menu-card i { font-size: 2rem; color: #ff6b8b; display: block; margin-bottom: 0.5rem; }
        .menu-card span { font-weight: 500; }
        footer {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            margin-top: 2rem;
        }
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .container { padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr 1fr; }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/45?text=🐾'">
            <h1>Mimie's Pet Hub</h1>
        </div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="pets.php"><i class="fas fa-paw"></i> Pets</a>
            <a href="add-pet.php"><i class="fas fa-plus-circle"></i> Add Pet</a>
            <a href="user-pets.php"><i class="fas fa-users"></i> User Pets</a>
            <a href="reports.php"><i class="fas fa-exclamation-triangle"></i> Reports</a>
            <a href="sales.php"><i class="fas fa-chart-line"></i> Sales</a>
            <a href="users.php"><i class="fas fa-user-cog"></i> Users</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h2><?php echo $stats['available_pets']; ?></h2>
                <p>🐾 Available Pets</p>
            </div>
            <div class="stat-card pending">
                <h2><?php echo $stats['pending_user_pets']; ?></h2>
                <p>⏳ User Pets Pending</p>
            </div>
            <div class="stat-card pending">
                <h2><?php echo $stats['pending_orders']; ?></h2>
                <p>📦 Pending Orders</p>
            </div>
            <div class="stat-card pending">
                <h2><?php echo $stats['pending_reports']; ?></h2>
                <p>📢 Pending Reports</p>
            </div>
        </div>

        <div class="menu-grid">
            <a href="pets.php" class="menu-card">
                <i class="fas fa-paw"></i>
                <span>Manage Pets</span>
            </a>
            <a href="add-pet.php" class="menu-card">
                <i class="fas fa-plus-circle"></i>
                <span>Add Pet</span>
            </a>
            <a href="user-pets.php" class="menu-card">
                <i class="fas fa-users"></i>
                <span>User Pets</span>
            </a>
            <a href="sales.php" class="menu-card">
                <i class="fas fa-chart-line"></i>
                <span>Sales</span>
            </a>
            <a href="reports.php" class="menu-card">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Reports</span>
            </a>
            <a href="users.php" class="menu-card">
                <i class="fas fa-user-cog"></i>
                <span>Users</span>
            </a>
        </div>

        <div class="recent-section">
            <h3><i class="fas fa-clock"></i> Recent Orders</h3>
            <?php if(count($recent_orders) > 0): ?>
            <table>
                <thead><tr><th>Order #</th><th>Customer</th><th>Amount</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach($recent_orders as $order): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($order['order_number']); ?></td>
                        <td><?php echo htmlspecialchars($order['customer_name']); ?></td>
                        <td>$<?php echo number_format($order['total_amount'], 2); ?></td>
                        <td><span class="status-badge status-<?php echo $order['status']; ?>"><?php echo ucfirst($order['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="color:#888; text-align:center; padding:1rem;">No orders yet.</p>
            <?php endif; ?>
        </div>

        <div class="recent-section">
            <h3><i class="fas fa-paw"></i> Recent User Pets</h3>
            <?php if(count($recent_user_pets) > 0): ?>
            <table>
                <thead><tr><th>Pet</th><th>Seller</th><th>Price</th><th>Status</th></tr></thead>
                <tbody>
                    <?php foreach($recent_user_pets as $pet): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($pet['pet_name']); ?></td>
                        <td><?php echo htmlspecialchars($pet['seller_name']); ?></td>
                        <td>$<?php echo number_format($pet['price'], 2); ?></td>
                        <td><span class="status-badge status-<?php echo $pet['status']; ?>"><?php echo ucfirst($pet['status']); ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php else: ?>
            <p style="color:#888; text-align:center; padding:1rem;">No user pet listings yet.</p>
            <?php endif; ?>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Mimie's Pet Hub | Admin Panel</p>
    </footer>
</body>
</html>