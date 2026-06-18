<?php
session_start();
require_once('includes/config.php');

if(!isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get user info from session or database
$user_id = $_SESSION['user_id'] ?? 0;
$username = $_SESSION['username'] ?? $_SESSION['full_name'] ?? 'User';
$role = $_SESSION['role'] ?? 'user';

// Get user info from database using PDO
$user = [];
if ($user_id > 0) {
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE id = ?");
    $stmt->execute([$user_id]);
    $user = $stmt->fetch();
}

// Get statistics using PDO
$my_pets = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_pets WHERE seller_email = ?");
$stmt->execute([$_SESSION['username'] ?? '']);
$my_pets = $stmt->fetch()['count'];

$my_reports = 0;
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM lost_found WHERE owner_email = ?");
$stmt->execute([$_SESSION['username'] ?? '']);
$my_reports = $stmt->fetch()['count'];

$recent_pets = [];
$stmt = $pdo->prepare("SELECT * FROM user_pets WHERE seller_email = ? ORDER BY created_at DESC LIMIT 4");
$stmt->execute([$_SESSION['username'] ?? '']);
$recent_pets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffe0e5 100%);
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
        
        .welcome-card {
            background: white;
            border-radius: 25px;
            padding: 1.5rem;
            margin-bottom: 2rem;
            box-shadow: 0 10px 30px rgba(255,107,139,0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
        }
        .welcome-text h2 { color: #ff6b8b; margin-bottom: 0.3rem; }
        .profile-mini {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .profile-mini img {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #ff6b8b;
        }
        .view-profile-btn {
            background: #ff6b8b;
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            text-decoration: none;
            font-size: 0.8rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1.5rem;
            margin-bottom: 2rem;
        }
        .stat-card {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            box-shadow: 0 5px 15px rgba(255,107,139,0.08);
            transition: 0.3s;
        }
        .stat-card:hover { transform: translateY(-3px); }
        .stat-number { font-size: 2rem; font-weight: 800; color: #ff6b8b; }
        .stat-label { color: #8a5a6a; margin-top: 0.3rem; }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }
        .action-btn {
            background: white;
            border-radius: 20px;
            padding: 1.2rem;
            text-align: center;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(255,107,139,0.08);
        }
        .action-btn:hover { transform: translateY(-3px); box-shadow: 0 10px 25px rgba(255,107,139,0.15); }
        .action-btn i { font-size: 2rem; color: #ff6b8b; margin-bottom: 0.5rem; display: block; }
        .action-btn span { color: #5a2a3a; font-weight: 600; }
        
        .recent-section {
            background: white;
            border-radius: 25px;
            padding: 1.5rem;
        }
        .recent-section h3 { color: #ff6b8b; margin-bottom: 1rem; }
        .pet-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 1rem;
        }
        .pet-item {
            background: #f9f9f9;
            border-radius: 15px;
            padding: 0.8rem;
            text-align: center;
            text-decoration: none;
            transition: 0.3s;
        }
        .pet-item:hover { transform: translateY(-3px); }
        .pet-item img { width: 100%; height: 120px; object-fit: cover; border-radius: 10px; }
        .pet-name { font-weight: 600; color: #5a2a3a; margin-top: 0.5rem; }
        
        footer {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .welcome-card { flex-direction: column; text-align: center; gap: 1rem; }
            .container { padding: 1rem; }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Logo" onerror="this.src='https://via.placeholder.com/45?text=🐾'">
            <h1>Mimie's Pet Hub🐾</h1>
        </div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="sell-pet.php"><i class="fas fa-plus-circle"></i> Sell Pet</a>
            <a href="lost-found.php"><i class="fas fa-search"></i> Lost & Found</a>
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
            <?php if($role == 'admin'): ?>
                <a href="admin/index.php"><i class="fas fa-shield-alt"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    
    <div class="container">
        <div class="welcome-card">
            <div class="welcome-text">
                <h2>Welcome back, <?php echo htmlspecialchars($username); ?>! 👋</h2>
                <p>Here's what's happening with your pet journey</p>
            </div>
            <div class="profile-mini">
                <img src="<?php echo !empty($user['profile_pic']) ? $user['profile_pic'] : 'https://ui-avatars.com/api/?background=ff6b8b&color=fff&name='.urlencode($username); ?>" alt="Profile">
                <a href="profile.php" class="view-profile-btn"><i class="fas fa-user-edit"></i> View Profile</a>
            </div>
        </div>
        
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-number"><?php echo $my_pets; ?></div>
                <div class="stat-label">Pets Listed</div>
                <a href="my-pets.php" style="color: #ff6b8b; font-size: 0.8rem;">View All →</a>
            </div>
            <div class="stat-card">
                <div class="stat-number"><?php echo $my_reports; ?></div>
                <div class="stat-label">Lost/Found Reports</div>
                <a href="lost-found.php" style="color: #ff6b8b; font-size: 0.8rem;">View Reports →</a>
            </div>
        </div>
        
        <div class="action-grid">
            <a href="sell-pet.php" class="action-btn">
                <i class="fas fa-plus-circle"></i>
                <span>Sell a Pet</span>
            </a>
            <a href="lost-found.php" class="action-btn">
                <i class="fas fa-search"></i>
                <span>Report Lost/Found</span>
            </a>
            <a href="my-pets.php" class="action-btn">
                <i class="fas fa-list"></i>
                <span>Manage My Pets</span>
            </a>
            <a href="profile.php" class="action-btn">
                <i class="fas fa-user-circle"></i>
                <span>My Profile</span>
            </a>
        </div>
        
        <div class="recent-section">
            <h3><i class="fas fa-paw"></i> My Recent Pets</h3>
            <?php if(count($recent_pets) > 0): ?>
            <div class="pet-list">
                <?php foreach($recent_pets as $pet): ?>
                <a href="view-pet.php?id=<?php echo $pet['id']; ?>" class="pet-item">
                    <img src="<?php echo !empty($pet['image_url']) ? $pet['image_url'] : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=200'; ?>" alt="<?php echo $pet['pet_name']; ?>">
                    <div class="pet-name"><?php echo htmlspecialchars($pet['pet_name']); ?></div>
                    <div style="color: #ff6b8b; font-weight: 600;">$<?php echo number_format($pet['price'], 2); ?></div>
                </a>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
                <p style="text-align: center; color: #888;">You haven't listed any pets yet. <a href="sell-pet.php" style="color: #ff6b8b;">List your first pet</a></p>
            <?php endif; ?>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2024 Mimie's Pet Hub | Zimbabwe's Trusted Pet Marketplace 🇿🇼</p>
        <p style="font-size: 0.8rem; margin-top: 0.5rem;">Made with <i class="fas fa-heart"></i> for our furry friends</p>
    </footer>
</body>
</html>