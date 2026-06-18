<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get sales data
$sales = $pdo->query("
    SELECT s.*, p.name as pet_name, p.type 
    FROM sales s 
    LEFT JOIN pets p ON s.pet_id = p.id 
    ORDER BY s.sale_date DESC
");

$total_sales = $pdo->query("SELECT COUNT(*) as count FROM sales")->fetch()['count'];
$total_revenue = $pdo->query("SELECT SUM(amount) as total FROM sales")->fetch()['total'] ?? 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Sales - Admin</title>
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
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;
            box-shadow: 0 4px 15px rgba(255,107,139,0.2);
        }
        .logo { display: flex; align-items: center; gap: 0.8rem; }
        .logo img { height: 45px; border-radius: 50%; background: white; padding: 5px; }
        .logo h1 { color: white; font-size: 1.3rem; }
        .nav-links a {
            color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 25px;
            background: rgba(255,255,255,0.15); margin-left: 0.5rem; transition: 0.3s;
        }
        .nav-links a:hover { background: white; color: #ff6b8b; transform: translateY(-2px); }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .stats-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        .stat-card h2 { font-size: 2rem; font-weight: 700; color: #ff6b8b; }
        .stat-card p { color: #8a5a6a; }
        table { width: 100%; background: white; border-radius: 20px; overflow: hidden; border-collapse: collapse; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        th, td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid #ffe0e5; }
        th { background: #ff6b8b; color: white; font-weight: 600; }
        tr:hover { background: #fff5f7; }
        .back-link { display: inline-block; margin-top: 1rem; color: #ff6b8b; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        footer { text-align: center; padding: 2rem; background: linear-gradient(135deg, #ff6b8b, #ff8da1); color: white; margin-top: 2rem; }
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .container { padding: 1rem; }
            .stats-grid { grid-template-columns: 1fr; }
            table { font-size: 0.8rem; }
            th, td { padding: 0.5rem; }
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
            <a href="reports.php"><i class="fas fa-exclamation-triangle"></i> Reports</a>
            <a href="users.php"><i class="fas fa-user-cog"></i> Users</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card">
                <h2><?php echo $total_sales; ?></h2>
                <p>💰 Total Sales</p>
            </div>
            <div class="stat-card">
                <h2>$<?php echo number_format($total_revenue, 2); ?></h2>
                <p>💵 Total Revenue</p>
            </div>
        </div>

        <?php if($sales->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Pet</th>
                    <th>Buyer</th>
                    <th>Email</th>
                    <th>Amount</th>
                    <th>Payment</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php while($sale = $sales->fetch()): ?>
                <tr>
                    <td><?php echo $sale['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($sale['pet_name'] ?? 'Unknown'); ?></strong><br><small><?php echo ucfirst($sale['type'] ?? ''); ?></small></td>
                    <td><?php echo htmlspecialchars($sale['buyer_name']); ?></td>
                    <td><?php echo htmlspecialchars($sale['buyer_email']); ?></td>
                    <td>$<?php echo number_format($sale['amount'] ?? $sale['price'], 2); ?></td>
                    <td><?php echo ucfirst($sale['payment_method'] ?? 'N/A'); ?></td>
                    <td><?php echo date('M d, Y', strtotime($sale['sale_date'])); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center; padding:2rem; background:white; border-radius:20px; color:#888;">No sales recorded yet.</p>
        <?php endif; ?>

        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Mimie's Pet Hub | Admin Panel</p>
    </footer>
</body>
</html>