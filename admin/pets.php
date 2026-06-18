<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

// Get all pets
$pets = $pdo->query("SELECT * FROM pets ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Pets - Admin</title>
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
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; flex-wrap: wrap; gap: 1rem; }
        .btn-add {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
            padding: 0.6rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: 0.3s;
        }
        .btn-add:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(40,167,69,0.4); }
        table { width: 100%; background: white; border-radius: 20px; overflow: hidden; border-collapse: collapse; box-shadow: 0 5px 15px rgba(255,107,139,0.08); }
        th, td { padding: 0.8rem 1rem; text-align: left; border-bottom: 1px solid #ffe0e5; }
        th { background: #ff6b8b; color: white; font-weight: 600; }
        tr:hover { background: #fff5f7; }
        img { width: 50px; height: 50px; object-fit: cover; border-radius: 10px; }
        .btn-edit {
            background: #ffc107;
            color: #333;
            padding: 4px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            margin-right: 5px;
            transition: 0.3s;
        }
        .btn-edit:hover { background: #e0a800; }
        .btn-delete {
            background: #dc3545;
            color: white;
            padding: 4px 12px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 0.75rem;
            font-weight: 600;
            display: inline-block;
            transition: 0.3s;
        }
        .btn-delete:hover { background: #c82333; }
        .status-badge { padding: 3px 12px; border-radius: 20px; font-size: 0.7rem; font-weight: 600; display: inline-block; }
        .status-available { background: #e8f5e9; color: #4caf50; }
        .status-sold { background: #fce4ec; color: #e91e63; }
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
            <a href="add-pet.php"><i class="fas fa-plus-circle"></i> Add Pet</a>
            <a href="user-pets.php"><i class="fas fa-users"></i> User Pets</a>
            <a href="reports.php"><i class="fas fa-exclamation-triangle"></i> Reports</a>
            <a href="sales.php"><i class="fas fa-chart-line"></i> Sales</a>
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="top-bar">
            <h2 style="color: #ff6b8b;"><i class="fas fa-paw"></i> All Pets</h2>
            <a href="add-pet.php" class="btn-add"><i class="fas fa-plus"></i> Add New Pet</a>
        </div>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Image</th>
                    <th>Name</th>
                    <th>Type</th>
                    <th>Breed</th>
                    <th>Age</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while($pet = $pets->fetch()): ?>
                <tr>
                    <td><?php echo $pet['id']; ?></td>
                    <td>
                        <?php if($pet['image_url']): ?>
                            <img src="<?php echo htmlspecialchars($pet['image_url']); ?>">
                        <?php else: ?>
                            <div style="width:50px;height:50px;background:#f0f0f0;border-radius:10px;display:flex;align-items:center;justify-content:center;color:#ccc;">No</div>
                        <?php endif; ?>
                    </td>
                    <td><strong><?php echo htmlspecialchars($pet['name']); ?></strong></td>
                    <td><?php echo ucfirst($pet['type']); ?></td>
                    <td><?php echo htmlspecialchars($pet['breed']); ?></td>
                    <td><?php echo htmlspecialchars($pet['age']); ?></td>
                    <td>$<?php echo number_format($pet['price'], 2); ?></td>
                    <td><span class="status-badge status-<?php echo $pet['status']; ?>"><?php echo ucfirst($pet['status']); ?></span></td>
                    <td>
                        <a href="edit-pet.php?id=<?php echo $pet['id']; ?>" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="delete-pet.php?id=<?php echo $pet['id']; ?>" class="btn-delete" onclick="return confirm('Are you sure you want to delete this pet?')"><i class="fas fa-trash"></i> Delete</a>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Mimie's Pet Hub | Admin Panel</p>
    </footer>
</body>
</html>