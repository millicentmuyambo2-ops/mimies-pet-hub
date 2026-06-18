<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['delete'])) {
    $delete_id = (int)$_GET['delete'];
    $stmt = $pdo->prepare("DELETE FROM admin_users WHERE id = ? AND username != 'admin'");
    $stmt->execute([$delete_id]);
    header("Location: users.php?deleted=1");
    exit();
}

$users = $pdo->query("SELECT * FROM admin_users ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffe0e5 100%);
        }
        .admin-header {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .logo { display: flex; align-items: center; gap: 0.8rem; }
        .logo img { height: 45px; border-radius: 50%; background: white; padding: 5px; }
        .logo h1 { color: white; font-size: 1.2rem; }
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255,255,255,0.15);
            margin-left: 0.5rem;
            transition: 0.3s;
        }
        .nav-links a:hover { background: white; color: #ff6b8b; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .page-header h2 { color: #ff6b8b; margin-bottom: 1.5rem; }
        table { width: 100%; background: white; border-radius: 20px; overflow: hidden; border-collapse: collapse; box-shadow: 0 5px 15px rgba(255,107,139,0.1); }
        th, td { padding: 1rem; text-align: left; border-bottom: 1px solid #ffe0e5; }
        th { background: #ff6b8b; color: white; }
        .btn-delete { color: #f44336; text-decoration: none; padding: 5px 10px; border-radius: 5px; transition: 0.3s; }
        .btn-delete:hover { background: #f44336; color: white; }
        .role-badge { padding: 2px 10px; border-radius: 15px; font-size: 0.8rem; }
        .role-admin { background: #ff6b8b; color: white; }
        .role-user { background: #ddd; color: #333; }
        .alert { background: #d4edda; color: #155724; padding: 1rem; border-radius: 10px; margin-bottom: 1rem; text-align: center; }
        @media (max-width: 768px) { 
            .admin-header { flex-direction: column; text-align: center; gap: 1rem; }
            th, td { padding: 0.5rem; font-size: 0.75rem; }
        }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="logo">
            <img src="../assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/45?text=🐾'">
            <h1>Manage Users</h1>
        </div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-users"></i> All Users</h2>
        </div>
        
        <?php if(isset($_GET['deleted'])): ?>
            <div class="alert">✅ User deleted successfully!</div>
        <?php endif; ?>
        
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
                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                    <td><?php echo htmlspecialchars($user['full_name'] ?? ''); ?></td>
                    <td><?php echo htmlspecialchars($user['email']); ?></td>
                    <td><?php echo htmlspecialchars($user['phone'] ?? ''); ?></td>
                    <td><span class="role-badge role-<?php echo $user['role']; ?>"><?php echo ucfirst($user['role']); ?></span></td>
                    <td><?php echo date('M d, Y', strtotime($user['created_at'])); ?></td>
                    <td>
                        <?php if($user['id'] != $_SESSION['user_id'] && $user['username'] != 'admin'): ?>
                            <a href="?delete=<?php echo $user['id']; ?>" class="btn-delete" onclick="return confirm('Delete this user?')"><i class="fas fa-trash"></i> Delete</a>
                        <?php else: ?>
                            <span style="color: #888;">You</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</body>
</html>