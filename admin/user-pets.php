<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Delete user
if(isset($_GET['delete'])) {
    $id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ? AND username != 'admin'");
    $stmt->execute([$id]);
    header("Location: users.php?deleted=1");
    exit();
}

$users = $pdo->query("SELECT * FROM admin_users ORDER BY created_at DESC");
$total_users = $users->rowCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users - Admin</title>
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
        .stats { background: white; padding: 1.5rem; border-radius: 20px; text-align: center; margin-bottom: 2rem; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        .stats h2 { font-size: 2rem; color: #ff6b8b; }
        .alert { background: #d4edda; color: #155724; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; font-weight: 500; }
        table { width: 100%; background: white; border-radius: 20px; overflow: hidden; border-collapse: collapse; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        th, td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid #ffe0e5; }
        th { background: #ff6b8b; color: white; font-weight: 600; }
        tr:hover { background: #fff5f7; }
        .role-badge { padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .role-admin { background: #ff6b8b; color: white; }
        .role-user { background: #e0e0e0; color: #333; }
        .btn-delete { color: #dc3545; text-decoration: none; font-weight: 600; }
        .btn-delete:hover { color: #c82333; text-decoration: underline; }
        .back-link { display: inline-block; margin-top: 1rem; color: #ff6b8b; text-decoration: none; font-weight: 500; }
        .back-link:hover { text-decoration: underline; }
        footer { text-align: center; padding: 2rem; background: linear-gradient(135deg, #ff6b8b, #ff8da1); color: white; margin-top: 2rem; }
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .container { padding: 1rem; }
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
            <a href="reports.php"><i class="fas fa-exclamation-triangle"></i> Reports</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert">✅ User deleted successfully!</div>
        <?php endif; ?>

        <div class="stats">
            <h2><?php echo $total_users; ?></h2>
            <p>👥 Total Users</p>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Username</th>
                    <th>Full Name</th>
                    <th>Email</th>
                    <th>Phone</th>
                    <th>Role</th>
                    <th>Joined</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while($user = $users->fetch()): ?>
                <tr>
                    <td><?php echo $user['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($user['username']); ?></strong></td>
                    <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                    <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['id'] != $_SESSION['user_id'] && $user['username'] != 'admin'): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i> Delete</a>
                        <?php else: ?>
                            <span style="color: #888; font-size: 0.8rem;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>

        <a href="index.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Mimie's Pet Hub | Admin Panel</p>
    </footer>
</body>
</html>