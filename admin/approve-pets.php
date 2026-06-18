<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

if(isset($_GET['approve'])) {
    mysqli_query($conn, "UPDATE pets SET status = 'available' WHERE id = ".$_GET['approve']);
    header("Location: approve-pets.php");
    exit();
}

if(isset($_GET['reject'])) {
    mysqli_query($conn, "DELETE FROM pets WHERE id = ".$_GET['reject']);
    header("Location: approve-pets.php");
    exit();
}

$pending_pets = mysqli_query($conn, "SELECT p.*, u.username, u.email, u.phone FROM pets p JOIN users u ON p.user_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Approve Pets - Admin</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #fff5f7 0%, #ffe0e5 100%); }
        .admin-header { background: linear-gradient(135deg, #ff6b8b, #ff8da1); padding: 1rem 2rem; display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; }
        .logo { display: flex; align-items: center; gap: 0.8rem; }
        .logo img { height: 45px; border-radius: 50%; background: white; padding: 5px; }
        .logo h1 { color: white; font-size: 1.2rem; }
        .nav-links a { color: white; text-decoration: none; padding: 0.5rem 1rem; border-radius: 25px; background: rgba(255,255,255,0.15); margin-left: 0.5rem; }
        .container { max-width: 1200px; margin: 0 auto; padding: 2rem; }
        .pet-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.5rem; }
        .pet-card { background: white; border-radius: 20px; overflow: hidden; box-shadow: 0 5px 15px rgba(255,107,139,0.1); }
        .pet-card img { width: 100%; height: 200px; object-fit: cover; }
        .pet-info { padding: 1rem; }
        .pet-name { font-size: 1.2rem; font-weight: 700; color: #5a2a3a; }
        .actions { display: flex; gap: 0.5rem; margin-top: 1rem; }
        .btn-approve { flex:1; background: #4CAF50; color: white; text-align: center; padding: 0.5rem; border-radius: 25px; text-decoration: none; }
        .btn-reject { flex:1; background: #f44336; color: white; text-align: center; padding: 0.5rem; border-radius: 25px; text-decoration: none; }
        @media (max-width: 768px) { .admin-header { flex-direction: column; text-align: center; } }
    </style>
</head>
<body>
    <header class="admin-header">
        <div class="logo"><img src="../assets/images/logo.png" alt="Logo"><h1>Approve Pets</h1></div>
        <div class="nav-links"><a href="index.php">Dashboard</a><a href="../logout.php">Logout</a></div>
    </header>
    <div class="container">
        <h2 style="color:#ff6b8b; margin-bottom:1.5rem;"><i class="fas fa-clock"></i> Pending Approvals (<?php echo mysqli_num_rows($pending_pets); ?>)</h2>
        <?php if(mysqli_num_rows($pending_pets) == 0): ?>
            <div style="text-align:center; padding:3rem; background:white; border-radius:20px;">✅ No pending approvals!</div>
        <?php else: ?>
        <div class="pet-grid">
            <?php while($pet = mysqli_fetch_assoc($pending_pets)): ?>
            <div class="pet-card">
                <img src="<?php echo $pet['image_url'] ?: 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=300'; ?>">
                <div class="pet-info">
                    <div class="pet-name"><?php echo $pet['name']; ?></div>
                    <p><strong>Species:</strong> <?php echo $pet['species']; ?> | <?php echo $pet['breed']; ?></p>
                    <p><strong>Price:</strong> $<?php echo $pet['price']; ?> | <strong>Age:</strong> <?php echo $pet['age']; ?> months</p>
                    <p><strong>Seller:</strong> <?php echo $pet['username']; ?> (<?php echo $pet['email']; ?>)</p>
                    <p><?php echo substr($pet['description'],0,100); ?>...</p>
                    <div class="actions">
                        <a href="?approve=<?php echo $pet['id']; ?>" class="btn-approve"><i class="fas fa-check"></i> Approve</a>
                        <a href="?reject=<?php echo $pet['id']; ?>" class="btn-reject" onclick="return confirm('Reject this listing?')"><i class="fas fa-times"></i> Reject</a>
                    </div>
                </div>
            </div>
            <?php endwhile; ?>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>