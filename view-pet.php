<?php
session_start();
require_once('includes/config.php');
require_once('header.php');

$pet_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if($pet_id == 0) {
    header("Location: index.php");
    exit();
}

// Get pet details - check both tables (pets and user_pets)
$pet = null;
$is_user_pet = false;

// First check in pets table
$stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ? AND status = 'available'");
$stmt->execute([$pet_id]);
$pet = $stmt->fetch();

if (!$pet) {
    // Then check in user_pets table
    $stmt = $pdo->prepare("SELECT * FROM user_pets WHERE id = ? AND status = 'approved'");
    $stmt->execute([$pet_id]);
    $pet = $stmt->fetch();
    $is_user_pet = true;
}

if (!$pet) {
    echo "<div class='container' style='text-align: center; padding: 50px;'><h2>Pet not found</h2><a href='index.php'>Back to Home</a></div>";
    require_once('footer.php');
    exit();
}

// Get seller info
if ($is_user_pet) {
    $seller_name = $pet['seller_name'] ?? 'Mimie\'s Pet Hub';
    $seller_email = $pet['seller_email'] ?? 'info@mimiespethub.com';
    $seller_phone = $pet['seller_phone'] ?? '';
    $seller_location = $pet['location'] ?? 'Bindura, Zimbabwe';
    $profile_pic = 'https://ui-avatars.com/api/?background=ff6b8b&color=fff&name=' . urlencode($seller_name);
} else {
    $seller_name = 'Mimie\'s Pet Hub';
    $seller_email = 'info@mimiespethub.com';
    $seller_phone = '+263 77 123 4567';
    $seller_location = $pet['location'] ?? 'Bindura, Zimbabwe';
    $profile_pic = 'assets/images/logo.png';
}

// Get seller's other pets
$other_pets = [];
if ($is_user_pet && !empty($pet['seller_email'])) {
    $stmt = $pdo->prepare("SELECT * FROM user_pets WHERE seller_email = ? AND id != ? AND status = 'approved' LIMIT 3");
    $stmt->execute([$pet['seller_email'], $pet_id]);
    $other_pets = $stmt->fetchAll();
} elseif (!$is_user_pet) {
    $stmt = $pdo->prepare("SELECT * FROM pets WHERE id != ? AND status = 'available' LIMIT 3");
    $stmt->execute([$pet_id]);
    $other_pets = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($pet['name'] ?? $pet['pet_name']); ?> - Mimie's Pet Hub</title>
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
            background: linear-gradient(135deg, #fff5f7 0%, #ffe0e5 100%);
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
            box-shadow: 0 4px 15px rgba(255,107,139,0.2);
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
        }
        
        .nav-links a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 25px;
            background: rgba(255,255,255,0.15);
            margin-left: 0.5rem;
            transition: 0.3s;
        }
        
        .nav-links a:hover {
            background: white;
            color: #ff6b8b;
            transform: translateY(-2px);
        }
        
        /* Container */
        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 2rem;
        }
        
        .back-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            background: white;
            color: #ff6b8b;
            text-decoration: none;
            padding: 0.6rem 1.2rem;
            border-radius: 30px;
            margin-bottom: 1.5rem;
            transition: 0.3s;
            font-weight: 500;
            box-shadow: 0 2px 10px rgba(255,107,139,0.1);
        }
        
        .back-btn:hover {
            background: #ff6b8b;
            color: white;
            transform: translateX(-5px);
        }
        
        /* Pet Card */
        .pet-card {
            background: white;
            border-radius: 30px;
            overflow: hidden;
            box-shadow: 0 10px 40px rgba(255,107,139,0.1);
        }
        
        .pet-detail {
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }
        
        .pet-image {
            flex: 1;
            min-width: 350px;
        }
        
        .pet-image img {
            width: 100%;
            height: 400px;
            object-fit: cover;
        }
        
        .pet-info {
            flex: 1;
            padding: 1.5rem 1.5rem 1.5rem 0;
        }
        
        .status-badge {
            display: inline-block;
            padding: 5px 18px;
            border-radius: 30px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-bottom: 1rem;
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
        
        .pet-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: #5a2a3a;
            margin-bottom: 0.3rem;
        }
        
        .pet-breed {
            color: #ff6b8b;
            font-size: 1.1rem;
            margin-bottom: 1rem;
        }
        
        .pet-price {
            font-size: 2rem;
            font-weight: 700;
            color: #ff6b8b;
            margin: 1rem 0;
        }
        
        .info-row {
            display: flex;
            padding: 0.8rem 0;
            border-bottom: 1px solid #ffe0e5;
        }
        
        .info-label {
            width: 120px;
            color: #888;
            font-weight: 500;
        }
        
        .info-value {
            color: #5a2a3a;
            flex: 1;
        }
        
        .description {
            margin: 1.5rem 0;
            padding: 1.2rem;
            background: #fff5f7;
            border-radius: 20px;
        }
        
        .description h3 {
            color: #ff6b8b;
            margin-bottom: 0.8rem;
            font-size: 1.1rem;
        }
        
        .description p {
            color: #5a2a3a;
            line-height: 1.6;
        }
        
        /* Seller Info Bar */
        .seller-bar {
            background: #fff5f7;
            padding: 1rem 1.5rem;
            border-radius: 20px;
            margin: 1rem 0;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }
        
        .seller-info {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        
        .seller-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            object-fit: cover;
            border: 2px solid #ff6b8b;
        }
        
        .seller-details h4 {
            color: #5a2a3a;
            font-size: 1rem;
        }
        
        .seller-details p {
            color: #888;
            font-size: 0.8rem;
        }
        
        .contact-buttons {
            display: flex;
            gap: 0.8rem;
        }
        
        .contact-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.6rem 1.2rem;
            background: white;
            color: #ff6b8b;
            text-decoration: none;
            border-radius: 30px;
            font-size: 0.85rem;
            font-weight: 500;
            transition: 0.3s;
            border: 1px solid #ff6b8b;
        }
        
        .contact-btn:hover {
            background: #ff6b8b;
            color: white;
            transform: translateY(-2px);
        }
        
        .contact-btn.whatsapp {
            border-color: #25D366;
            color: #25D366;
        }
        
        .contact-btn.whatsapp:hover {
            background: #25D366;
            color: white;
        }
        
        /* Other Pets Section */
        .other-pets {
            margin-top: 2rem;
        }
        
        .other-pets h3 {
            color: #ff6b8b;
            margin-bottom: 1rem;
            font-size: 1.3rem;
        }
        
        .other-pets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
            gap: 1.5rem;
        }
        
        .other-pet-card {
            background: white;
            border-radius: 20px;
            padding: 0.8rem;
            text-align: center;
            text-decoration: none;
            transition: 0.3s;
            box-shadow: 0 5px 15px rgba(255,107,139,0.08);
        }
        
        .other-pet-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(255,107,139,0.15);
        }
        
        .other-pet-card img {
            width: 100%;
            height: 140px;
            object-fit: cover;
            border-radius: 15px;
        }
        
        .other-pet-name {
            font-weight: 600;
            color: #5a2a3a;
            margin-top: 0.5rem;
        }
        
        .other-pet-price {
            color: #ff6b8b;
            font-weight: 600;
            font-size: 0.9rem;
        }
        
        footer {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            margin-top: 3rem;
        }
        
        @media (max-width: 768px) {
            .main-header {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }
            .container {
                padding: 0 1rem;
            }
            .pet-detail {
                flex-direction: column;
            }
            .pet-info {
                padding: 1.5rem;
            }
            .pet-name {
                font-size: 1.8rem;
            }
            .seller-bar {
                flex-direction: column;
                text-align: center;
            }
            .seller-info {
                flex-direction: column;
                text-align: center;
            }
            .contact-buttons {
                justify-content: center;
            }
            .other-pets-grid {
                grid-template-columns: 1fr 1fr;
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
            <a href="sell-pet.php"><i class="fas fa-plus-circle"></i> Sell Pet</a>
            <a href="lost-found.php"><i class="fas fa-search"></i> Lost & Found</a>
            <a href="dashboard.php"><i class="fas fa-chart-line"></i> Dashboard</a>
            <?php if(isset($_SESSION['user_id']) || isset($_SESSION['logged_in'])): ?>
                <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
                <?php if(isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                    <a href="admin/index.php"><i class="fas fa-shield-alt"></i> Admin</a>
                <?php endif; ?>
                <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
            <?php else: ?>
                <a href="login.php"><i class="fas fa-sign-in-alt"></i> Login</a>
            <?php endif; ?>
        </div>
    </header>
    
    <div class="container">
        <a href="index.php" class="back-btn"><i class="fas fa-arrow-left"></i> Back to Home</a>
        
        <div class="pet-card">
            <div class="pet-detail">
                <div class="pet-image">
                    <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=500'; ?>" alt="<?php echo htmlspecialchars($pet['name'] ?? $pet['pet_name']); ?>">
                </div>
                <div class="pet-info">
                    <span class="status-badge status-<?php echo $pet['status'] == 'approved' ? 'available' : $pet['status']; ?>">
                        <?php echo ucfirst($pet['status'] == 'approved' ? 'Available' : $pet['status']); ?>
                    </span>
                    <h1 class="pet-name"><?php echo htmlspecialchars($pet['name'] ?? $pet['pet_name']); ?></h1>
                    <div class="pet-breed"><?php echo htmlspecialchars($pet['breed'] ?? $pet['pet_breed'] ?? 'Mixed'); ?></div>
                    <div class="pet-price">$<?php echo number_format($pet['price'], 2); ?> <span style="font-size: 0.9rem;">USD</span></div>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-paw"></i> Species</div>
                        <div class="info-value"><?php echo ucfirst($pet['type'] ?? $pet['pet_type'] ?? 'Pet'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-calendar"></i> Age</div>
                        <div class="info-value"><?php echo htmlspecialchars($pet['age'] ?? $pet['pet_age'] ?? 'Adult'); ?></div>
                    </div>
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-tag"></i> Listing ID</div>
                        <div class="info-value">#<?php echo $pet_id; ?></div>
                    </div>
                    
                    <div class="info-row">
                        <div class="info-label"><i class="fas fa-map-marker-alt"></i> Location</div>
                        <div class="info-value"><?php echo htmlspecialchars($seller_location); ?></div>
                    </div>
                    
                    <?php if(!empty($pet['description'])): ?>
                    <div class="description">
                        <h3><i class="fas fa-align-left"></i> About <?php echo htmlspecialchars($pet['name'] ?? $pet['pet_name']); ?></h3>
                        <p><?php echo nl2br(htmlspecialchars($pet['description'])); ?></p>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Seller Info Bar -->
                    <div class="seller-bar">
                        <div class="seller-info">
                            <img src="<?php echo $profile_pic; ?>" class="seller-avatar">
                            <div class="seller-details">
                                <h4><i class="fas fa-user"></i> <?php echo htmlspecialchars($seller_name); ?></h4>
                                <p><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($seller_location); ?></p>
                            </div>
                        </div>
                        <div class="contact-buttons">
                            <a href="mailto:<?php echo $seller_email; ?>?subject=I'm interested in <?php echo urlencode($pet['name'] ?? $pet['pet_name']); ?>" class="contact-btn">
                                <i class="fas fa-envelope"></i> Email
                            </a>
                            <?php if($seller_phone): ?>
                            <a href="https://wa.me/<?php echo preg_replace('/[^0-9]/', '', $seller_phone); ?>" target="_blank" class="contact-btn whatsapp">
                                <i class="fab fa-whatsapp"></i> WhatsApp
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Seller's Other Pets -->
        <?php if(count($other_pets) > 0): ?>
        <div class="other-pets">
            <h3><i class="fas fa-paw"></i> More from <?php echo htmlspecialchars($seller_name); ?></h3>
            <div class="other-pets-grid">
                <?php foreach($other_pets as $other): ?>
                <a href="view-pet.php?id=<?php echo $other['id']; ?>" class="other-pet-card">
                    <img src="<?php echo !empty($other['image_url']) ? htmlspecialchars($other['image_url']) : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=150'; ?>" alt="<?php echo htmlspecialchars($other['name'] ?? $other['pet_name']); ?>">
                    <div class="other-pet-name"><?php echo htmlspecialchars($other['name'] ?? $other['pet_name']); ?></div>
                    <div class="other-pet-price">$<?php echo number_format($other['price'], 2); ?></div>
                </a>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <footer>
        <p>&copy; 2024 Mimie's Pet Hub | Zimbabwe's Trusted Pet Marketplace 🇿🇼</p>
        <p style="font-size: 0.8rem; margin-top: 0.5rem;">Made with <i class="fas fa-heart"></i> for our furry friends</p>
    </footer>
</body>
</html>