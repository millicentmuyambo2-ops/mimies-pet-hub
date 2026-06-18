<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Update report status
if(isset($_GET['update']) && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    $status = $_GET['update'];
    $stmt = $pdo->prepare("UPDATE reports SET status = ? WHERE id = ?");
    $stmt->execute([$status, $id]);
    header("Location: reports.php");
    exit();
}

// Delete report
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM reports WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: reports.php");
    exit();
}

$reports = $pdo->query("SELECT * FROM reports ORDER BY reported_at DESC");
$pending = $pdo->query("SELECT COUNT(*) as count FROM reports WHERE status = 'pending'")->fetch()['count'];
$seen = $pdo->query("SELECT COUNT(*) as count FROM reports WHERE status = 'seen'")->fetch()['count'];
$rescued = $pdo->query("SELECT COUNT(*) as count FROM reports WHERE status = 'rescued'")->fetch()['count'];
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Reports - Admin</title>
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
        .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: white; padding: 1.5rem; border-radius: 20px; text-align: center; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        .stat-card h2 { font-size: 2rem; font-weight: 700; }
        .stat-card.pending h2 { color: #ff9800; }
        .stat-card.seen h2 { color: #2196f3; }
        .stat-card.rescued h2 { color: #4caf50; }
        .stat-card p { color: #8a5a6a; }
        table { width: 100%; background: white; border-radius: 20px; overflow: hidden; border-collapse: collapse; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        th, td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid #ffe0e5; }
        th { background: #ff6b8b; color: white; font-weight: 600; }
        tr:hover { background: #fff5f7; }
        .status-badge { padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .status-pending { background: #fff3e0; color: #ff9800; }
        .status-seen { background: #e3f2fd; color: #2196f3; }
        .status-rescued { background: #e8f5e9; color: #4caf50; }
        .btn-status { padding: 4px 12px; border-radius: 20px; text-decoration: none; font-size: 0.7rem; font-weight: 600; display: inline-block; margin: 2px; }
        .btn-seen { background: #2196f3; color: white; }
        .btn-seen:hover { background: #0b7dda; }
        .btn-rescued { background: #28a745; color: white; }
        .btn-rescued:hover { background: #218838; }
        .btn-delete { background: #dc3545; color: white; padding: 4px 12px; border-radius: 20px; text-decoration: none; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .btn-delete:hover { background: #c82333; }
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
            <a href="user-pets.php"><i class="fas fa-users"></i> User Pets</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="stats-grid">
            <div class="stat-card pending">
                <h2><?php echo $pending; ?></h2>
                <p>⏳ Pending</p>
            </div>
            <div class="stat-card seen">
                <h2><?php echo $seen; ?></h2>
                <p>👀 Seen</p>
            </div>
            <div class="stat-card rescued">
                <h2><?php echo $rescued; ?></h2>
                <p>🏠 Rescued</p>
            </div>
        </div>

        <?php if($reports->rowCount() > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Reporter</th>
                    <th>Location</th>
                    <th>Description</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($report = $reports->fetch()): ?>
                <tr>
                    <td><?php echo $report['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($report['name']); ?></strong><br><small><?php echo htmlspecialchars($report['email']); ?></small></td>
                    <td><?php echo htmlspecialchars($report['location']); ?></td>
                    <td><?php echo htmlspecialchars(substr($report['description'], 0, 40)); ?>...</td>
                    <td><span class="status-badge status-<?php echo $report['status']; ?>"><?php echo ucfirst($report['status']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($report['reported_at'])); ?></td>
                    <td>
                        <?php if($report['status'] == 'pending'): ?>
                            <a href="?update=seen&id=<?php echo $report['id']; ?>" class="btn-status btn-seen">👀 Seen</a>
                            <a href="?update=rescued&id=<?php echo $report['id']; ?>" class="btn-status btn-rescued">🏠 Rescued</a>
                        <?php endif; ?>
                        <a href="?delete=<?php echo $report['id']; ?>" class="btn-delete" onclick="return confirm('Delete report?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
        <p style="text-align:center; padding:2rem; background:white; border-radius:20px; color:#888;">No reports yet.</p>
        <?php endif; ?>

        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Mimie's Pet Hub | Admin Panel</p>
    </footer>
</body>
</html>