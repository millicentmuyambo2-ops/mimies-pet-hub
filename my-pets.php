<?php
session_start();
require_once('includes/config.php');

// Check if user is logged in
if(!isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get user email from session
$user_email = $_SESSION['email'] ?? $_SESSION['username'] ?? '';

// Get user's pets from user_pets table using PDO
$stmt = $pdo->prepare("SELECT * FROM user_pets WHERE seller_email = ? ORDER BY created_at DESC");
$stmt->execute([$user_email]);
$pets = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Pets - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #fff5f7 0%, #ffe0e5 50%, #fff0f3 100%);
            min-height: 100vh;
        }
        
        /* Header */
        .main-header {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            padding: 1rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            box-shadow: 0 4px 20px rgba(255, 107, 139, 0.25);
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
            height: 50px;
            width: auto;
            background: white;
            border-radius: 50%;
            padding: 5px;
        }
        
        .logo h1 {
            color: white;
            font-size: 1.5rem;
            font-weight: 700;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
        }
        
        .nav-links {
            display: flex;
            gap: 0.5rem;
            flex-wrap: wrap;
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            transition: all 0.3s ease;
            font-weight: 500;
            background: rgba(255, 255, 255, 0.15);
        }
        
        .nav-links a:hover {
            background: white;
            color: #ff6b8b;
            transform: translateY(-2px);
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 2rem;
        }
        
        /* Page Header */
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .page-header h2 {
            color: #ff6b8b;
            font-size: 1.8rem;
            font-weight: 700;
        }
        
        .page-header h2 i {
            margin-right: 0.5rem;
        }
        
        /* Add Button */
        .add-btn {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            padding: 0.8rem 1.5rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        
        .add-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 139, 0.4);
        }
        
        /* Pet Grid */
        .pet-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 1.5rem;
        }
        
        .pet-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 25px rgba(255, 107, 139, 0.1);
            transition: all 0.3s ease;
            border: 1px solid rgba(255, 107, 139, 0.1);
        }
        
        .pet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(255, 107, 139, 0.15);
        }
        
        .pet-card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .pet-info {
            padding: 1.2rem;
        }
        
        .pet-name {
            color: #5a2a3a;
            font-size: 1.2rem;
            font-weight: 700;
            margin-bottom: 0.3rem;
        }
        
        .pet-details {
            color: #8a5a6a;
            font-size: 0.85rem;
            margin-bottom: 0.5rem;
        }
        
        .pet-price {
            color: #ff6b8b;
            font-size: 1.3rem;
            font-weight: 700;
            margin: 0.5rem 0;
        }
        
        .status {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 600;
        }
        
        .status-available, .status-approved {
            background: rgba(76, 175, 80, 0.15);
            color: #4CAF50;
        }
        
        .status-pending {
            background: rgba(255, 152, 0, 0.15);
            color: #ff9800;
        }
        
        .status-sold {
            background: rgba(244, 67, 54, 0.15);
            color: #f44336;
        }
        
        .card-actions {
            display: flex;
            gap: 0.5rem;
            margin-top: 1rem;
        }
        
        .btn-edit, .btn-delete {
            flex: 1;
            padding: 8px;
            text-align: center;
            text-decoration: none;
            border-radius: 12px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: all 0.3s;
        }
        
        .btn-edit {
            background: rgba(255, 107, 139, 0.1);
            color: #ff6b8b;
            border: 1px solid #ff6b8b;
        }
        
        .btn-edit:hover {
            background: #ff6b8b;
            color: white;
        }
        
        .btn-delete {
            background: rgba(244, 67, 54, 0.1);
            color: #f44336;
            border: 1px solid #f44336;
        }
        
        .btn-delete:hover {
            background: #f44336;
            color: white;
        }
        
        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            background: white;
            border-radius: 30px;
            box-shadow: 0 8px 25px rgba(255, 107, 139, 0.08);
        }
        
        .empty-state i {
            font-size: 5rem;
            color: #ff6b8b;
            opacity: 0.5;
            margin-bottom: 1rem;
        }
        
        .empty-state h3 {
            color: #5a2a3a;
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
        }
        
        .empty-state p {
            color: #8a5a6a;
            margin-bottom: 1.5rem;
        }
        
        .empty-state .btn-primary {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            padding: 0.8rem 2rem;
            border-radius: 30px;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            transition: all 0.3s;
        }
        
        .empty-state .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255, 107, 139, 0.4);
        }
        
        /* Tracking Code */
        .tracking-code {
            font-family: monospace;
            font-size: 0.7rem;
            background: #f5f5f5;
            padding: 3px 6px;
            border-radius: 5px;
            display: inline-block;
            margin-top: 5px;
        }
        
        /* Footer */
        footer {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            margin-top: 3rem;
        }
        
        footer p {
            opacity: 0.9;
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
            .page-header {
                flex-direction: column;
                text-align: center;
            }
            .container {
                padding: 1rem;
            }
            .pet-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <header class="main-header">
        <div class="logo">
            <img src="assets/images/logo.png" alt="Mimie's Pet Hub" onerror="this.src='https://via.placeholder.com/50?text=🐾'">
            <h1>Mimie's Pet Hub</h1>
        </div>
        <div class="nav-links">
            <a href="index.php"><i class="fas fa-home"></i> Home</a>
            <a href="sell-pet.php"><i class="fas fa-plus-circle"></i> Sell Pet</a>
            <a href="lost-found.php"><i class="fas fa-search"></i> Lost & Found</a>
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                <a href="admin/index.php"><i class="fas fa-shield-alt"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    
    <div class="container">
        <div class="page-header">
            <h2><i class="fas fa-paw"></i> My Listed Pets</h2>
            <a href="sell-pet.php" class="add-btn"><i class="fas fa-plus"></i> List New Pet</a>
        </div>
        
        <?php if(count($pets) > 0): ?>
        <div class="pet-grid">
            <?php foreach($pets as $pet): ?>
            <div class="pet-card">
                <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=300'; ?>" alt="<?php echo htmlspecialchars($pet['pet_name']); ?>">
                <div class="pet-info">
                    <div class="pet-name"><?php echo htmlspecialchars($pet['pet_name']); ?></div>
                    <div class="pet-details">
                        <?php echo htmlspecialchars($pet['pet_breed'] ?: $pet['pet_type']); ?> • <?php echo htmlspecialchars($pet['pet_age'] ?: 'Adult'); ?>
                    </div>
                    <div class="pet-price">$<?php echo number_format($pet['price'], 2); ?></div>
                    <span class="status status-<?php echo $pet['status'] == 'approved' ? 'available' : $pet['status']; ?>">
                        <?php echo ucfirst($pet['status']); ?>
                    </span>
                    <?php if($pet['tracking_code']): ?>
                        <div class="tracking-code">📋 <?php echo $pet['tracking_code']; ?></div>
                    <?php endif; ?>
                    <div class="card-actions">
                        <a href="edit-pet.php?id=<?php echo $pet['id']; ?>&type=user" class="btn-edit"><i class="fas fa-edit"></i> Edit</a>
                        <a href="delete-pet.php?id=<?php echo $pet['id']; ?>&type=user_pet" class="btn-delete" onclick="return confirm('Are you sure you want to delete this pet?')"><i class="fas fa-trash"></i> Delete</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="empty-state">
            <i class="fas fa-dog"></i>
            <h3>No Pets Listed Yet</h3>
            <p>You haven't listed any pets for sale. Start sharing your pets with the community!</p>
            <a href="sell-pet.php" class="btn-primary">🐾 List Your First Pet</a>
        </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2024 Mimie's Pet Hub | Zimbabwe's Trusted Pet Marketplace 🇿🇼</p>
        <p style="font-size: 0.8rem; margin-top: 0.5rem;">Made with <i class="fas fa-heart"></i> for our furry friends</p>
    </footer>
</body>
</html>