<?php
session_start();
require_once('includes/config.php');
require_once('includes/zim_cities.php');

// Check if user is logged in
if(!isset($_SESSION['user_id']) && !isset($_SESSION['logged_in'])) {
    header("Location: login.php");
    exit();
}

// Get user info from session
$user_email = $_SESSION['email'] ?? $_SESSION['username'] ?? '';
$username = $_SESSION['username'] ?? $_SESSION['full_name'] ?? 'User';
$user_role = $_SESSION['role'] ?? 'user';

$success = '';
$error = '';

// Get current user data from admin_users table using PDO
$user = [];
$stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ? OR username = ?");
$stmt->execute([$user_email, $username]);
$user = $stmt->fetch();

if (!$user) {
    // If not found, create a basic record
    $user = [
        'id' => $_SESSION['user_id'] ?? 0,
        'username' => $username,
        'email' => $user_email,
        'full_name' => $username,
        'phone' => '',
        'bio' => '',
        'province' => '',
        'city' => '',
        'suburb' => '',
        'gender' => '',
        'date_of_birth' => null,
        'profile_pic' => ''
    ];
}

// Handle profile update
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = trim($_POST['fullname'] ?? $username);
    $phone = trim($_POST['phone'] ?? '');
    $bio = trim($_POST['bio'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $suburb = trim($_POST['suburb'] ?? '');
    $gender = trim($_POST['gender'] ?? '');
    $dob = !empty($_POST['date_of_birth']) ? $_POST['date_of_birth'] : null;
    $profile_pic = trim($_POST['profile_pic'] ?? '');
    
    // Handle file upload
    if(isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $profile_pic = $upload_dir . $new_filename;
            move_uploaded_file($_FILES['profile_image']['tmp_name'], $profile_pic);
        } else {
            $error = "Invalid file type. Please upload JPG, PNG, or GIF.";
        }
    }
    
    if(empty($error)) {
        try {
            $stmt = $pdo->prepare("UPDATE admin_users SET 
                                   full_name = ?,
                                   phone = ?,
                                   bio = ?,
                                   province = ?,
                                   city = ?,
                                   suburb = ?,
                                   gender = ?,
                                   date_of_birth = ?,
                                   profile_pic = ?
                                   WHERE email = ?");
            $stmt->execute([$fullname, $phone, $bio, $province, $city, $suburb, $gender, $dob, $profile_pic, $user_email]);
            
            $_SESSION['full_name'] = $fullname;
            $success = "✅ Profile updated successfully!";
            
            // Refresh user data
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
            $stmt->execute([$user_email]);
            $user = $stmt->fetch();
        } catch(PDOException $e) {
            $error = "❌ Failed to update: " . $e->getMessage();
        }
    }
}

// Get user stats
$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM user_pets WHERE seller_email = ?");
$stmt->execute([$user_email]);
$total_pets = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT COUNT(*) as count FROM lost_found WHERE owner_email = ?");
$stmt->execute([$user_email]);
$total_reports = $stmt->fetch()['count'];

$stmt = $pdo->prepare("SELECT * FROM user_pets WHERE seller_email = ? ORDER BY created_at DESC LIMIT 4");
$stmt->execute([$user_email]);
$recent_pets = $stmt->fetchAll();

// Format date for display
$formatted_dob = '';
if(!empty($user['date_of_birth']) && $user['date_of_birth'] != '0000-00-00') {
    $formatted_dob = date('Y-m-d', strtotime($user['date_of_birth']));
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - Mimie's Pet Hub</title>
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
        .nav-links a:hover { background: white; color: #ff6b8b; }
        
        .profile-container {
            display: flex;
            max-width: 1400px;
            margin: 2rem auto;
            min-height: calc(100vh - 200px);
            gap: 1.5rem;
            padding: 0 1rem;
        }
        
        .side-panel {
            width: 300px;
            background: white;
            border-radius: 25px;
            padding: 1.5rem;
            box-shadow: 0 10px 30px rgba(255,107,139,0.1);
            position: sticky;
            top: 100px;
            height: fit-content;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            object-fit: cover;
            margin: 0 auto 1rem;
            border: 4px solid #ff6b8b;
            display: block;
        }
        
        .side-profile-name {
            text-align: center;
            font-size: 1.2rem;
            color: #5a2a3a;
            margin-bottom: 0.3rem;
        }
        
        .side-profile-email {
            text-align: center;
            font-size: 0.75rem;
            color: #888;
            margin-bottom: 1rem;
        }
        
        .side-stats {
            display: flex;
            justify-content: space-around;
            margin: 1rem 0;
            padding: 0.8rem 0;
            border-top: 1px solid #ffe0e5;
            border-bottom: 1px solid #ffe0e5;
        }
        
        .side-stat {
            text-align: center;
        }
        
        .side-stat-number {
            font-size: 1.2rem;
            font-weight: 700;
            color: #ff6b8b;
        }
        
        .side-stat-label {
            font-size: 0.65rem;
            color: #888;
        }
        
        .nav-menu {
            margin-top: 1.5rem;
        }
        
        .nav-item {
            display: flex;
            align-items: center;
            gap: 1rem;
            padding: 0.8rem 1rem;
            border-radius: 15px;
            text-decoration: none;
            color: #5a2a3a;
            transition: 0.3s;
            margin-bottom: 0.3rem;
        }
        
        .nav-item i {
            width: 25px;
            color: #ff6b8b;
        }
        
        .nav-item:hover {
            background: #fff0f3;
        }
        
        .nav-item.active {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
        }
        
        .nav-item.active i {
            color: white;
        }
        
        .main-content {
            flex: 1;
            background: white;
            border-radius: 25px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(255,107,139,0.1);
        }
        
        .section-title {
            color: #ff6b8b;
            font-size: 1.3rem;
            margin-bottom: 1.5rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid #ffe0e5;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }
        
        .form-group {
            margin-bottom: 1rem;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #5a2a3a;
            font-weight: 500;
            font-size: 0.85rem;
        }
        
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ffe0e5;
            border-radius: 12px;
            font-family: inherit;
        }
        
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #ff6b8b;
        }
        
        .upload-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 0.5rem;
        }
        
        .tab-btn {
            flex: 1;
            padding: 0.5rem;
            background: #f0f0f0;
            border: none;
            border-radius: 25px;
            cursor: pointer;
            transition: 0.3s;
        }
        
        .tab-btn.active {
            background: #ff6b8b;
            color: white;
        }
        
        .upload-panel {
            display: none;
        }
        
        .upload-panel.active {
            display: block;
        }
        
        .image-preview {
            margin-top: 0.5rem;
            text-align: center;
        }
        
        .image-preview img {
            max-width: 100px;
            border-radius: 50%;
            border: 3px solid #ff6b8b;
        }
        
        .btn-save {
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            padding: 0.8rem 1.5rem;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            width: 100%;
            margin-top: 1rem;
            transition: 0.3s;
        }
        
        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255,107,139,0.4);
        }
        
        .success { background: #d4edda; color: #155724; padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        
        .recent-pets-section {
            margin-top: 2rem;
        }
        
        .pet-list {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
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
            margin-top: 2rem;
        }
        
        @media (max-width: 992px) {
            .profile-container {
                flex-direction: column;
            }
            .side-panel {
                width: auto;
                position: static;
            }
        }
        
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .form-row { grid-template-columns: 1fr; }
            .main-content { padding: 1rem; }
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
            <?php if($user_role == 'admin'): ?>
                <a href="admin/index.php"><i class="fas fa-shield-alt"></i> Admin</a>
            <?php endif; ?>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    
    <div class="profile-container">
        <div class="side-panel">
            <img src="<?php echo !empty($user['profile_pic']) ? htmlspecialchars($user['profile_pic']) : 'https://ui-avatars.com/api/?background=ff6b8b&color=fff&name='.urlencode($user['username']); ?>" 
                 alt="Profile" class="profile-avatar">
            <h3 class="side-profile-name"><?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?></h3>
            <p class="side-profile-email"><?php echo htmlspecialchars($user['email']); ?></p>
            
            <div class="side-stats">
                <div class="side-stat">
                    <div class="side-stat-number"><?php echo $total_pets; ?></div>
                    <div class="side-stat-label">Pets</div>
                </div>
                <div class="side-stat">
                    <div class="side-stat-number"><?php echo $total_reports; ?></div>
                    <div class="side-stat-label">Reports</div>
                </div>
            </div>
            
            <div class="nav-menu">
                <a href="profile.php" class="nav-item active">
                    <i class="fas fa-user-edit"></i> Edit Profile
                </a>
                <a href="my-pets.php" class="nav-item">
                    <i class="fas fa-paw"></i> My Pets
                </a>
                <a href="sell-pet.php" class="nav-item">
                    <i class="fas fa-plus-circle"></i> Sell a Pet
                </a>
                <a href="lost-found.php" class="nav-item">
                    <i class="fas fa-search"></i> Lost & Found
                </a>
                <a href="dashboard.php" class="nav-item">
                    <i class="fas fa-chart-line"></i> Dashboard
                </a>
            </div>
        </div>
        
        <div class="main-content">
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <h2 class="section-title"><i class="fas fa-user-edit"></i> Edit Profile</h2>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-user"></i> Full Name</label>
                        <input type="text" name="fullname" value="<?php echo htmlspecialchars($user['full_name'] ?? $user['username']); ?>" required>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-phone"></i> Phone Number</label>
                        <input type="tel" name="phone" value="<?php echo htmlspecialchars($user['phone'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-image"></i> Profile Picture</label>
                    <div class="upload-tabs">
                        <button type="button" class="tab-btn active" onclick="switchTab('url')">🌐 Online URL</button>
                        <button type="button" class="tab-btn" onclick="switchTab('file')">📁 Upload Photo</button>
                    </div>
                    <div id="url-panel" class="upload-panel active">
                        <input type="url" name="profile_pic" id="profile_url" placeholder="https://..." value="<?php echo htmlspecialchars($user['profile_pic'] ?? ''); ?>" onchange="previewProfileImage(this.value)">
                    </div>
                    <div id="file-panel" class="upload-panel">
                        <input type="file" name="profile_image" id="profile_file" accept="image/*" onchange="previewProfileFile(this)">
                        <small style="color: #888;">Max 5MB. Allowed: JPG, PNG, GIF</small>
                    </div>
                    <div id="profile-preview" class="image-preview">
                        <?php if(!empty($user['profile_pic'])): ?>
                            <img src="<?php echo htmlspecialchars($user['profile_pic']); ?>">
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-align-left"></i> Bio</label>
                    <textarea name="bio" rows="3" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-venus-mars"></i> Gender</label>
                        <select name="gender">
                            <option value="">Prefer not to say</option>
                            <option value="male" <?php echo ($user['gender'] ?? '') == 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo ($user['gender'] ?? '') == 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo ($user['gender'] ?? '') == 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-calendar"></i> Date of Birth</label>
                        <input type="date" name="date_of_birth" value="<?php echo $formatted_dob; ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label><i class="fas fa-map-marker-alt"></i> Province</label>
                        <input type="text" name="province" value="<?php echo htmlspecialchars($user['province'] ?? ''); ?>" placeholder="e.g., Harare, Mashonaland Central">
                    </div>
                    <div class="form-group">
                        <label><i class="fas fa-city"></i> City/Town</label>
                        <input type="text" name="city" value="<?php echo htmlspecialchars($user['city'] ?? ''); ?>" placeholder="e.g., Harare, Bindura, Mutare">
                    </div>
                </div>
                
                <div class="form-group">
                    <label><i class="fas fa-location-dot"></i> Suburb/Area</label>
                    <input type="text" name="suburb" value="<?php echo htmlspecialchars($user['suburb'] ?? ''); ?>" placeholder="e.g., Chipadze, Borrowdale, CBD">
                </div>
                
                <button type="submit" class="btn-save"><i class="fas fa-save"></i> Save Changes</button>
            </form>
            
            <div class="recent-pets-section">
                <h2 class="section-title"><i class="fas fa-paw"></i> My Recent Pets</h2>
                <?php if(count($recent_pets) > 0): ?>
                <div class="pet-list">
                    <?php foreach($recent_pets as $pet): ?>
                    <a href="view-pet.php?id=<?php echo $pet['id']; ?>" class="pet-item">
                        <img src="<?php echo !empty($pet['image_url']) ? htmlspecialchars($pet['image_url']) : 'https://images.unsplash.com/photo-1543466835-00a7907e9de1?w=200'; ?>">
                        <div class="pet-name"><?php echo htmlspecialchars($pet['pet_name']); ?></div>
                        <div style="color: #ff6b8b;">$<?php echo number_format($pet['price'], 2); ?></div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php else: ?>
                    <p style="text-align: center; color: #888;">No pets listed yet. <a href="sell-pet.php" style="color: #ff6b8b;">Sell your first pet</a></p>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <footer>
        <p>&copy; 2024 Mimie's Pet Hub | Zimbabwe's Trusted Pet Marketplace 🇿🇼</p>
    </footer>
    
    <script>
        function switchTab(type) {
            const urlPanel = document.getElementById('url-panel');
            const filePanel = document.getElementById('file-panel');
            const tabs = document.querySelectorAll('.tab-btn');
            
            if(type === 'url') {
                urlPanel.classList.add('active');
                filePanel.classList.remove('active');
                tabs[0].classList.add('active');
                tabs[1].classList.remove('active');
            } else {
                urlPanel.classList.remove('active');
                filePanel.classList.add('active');
                tabs[0].classList.remove('active');
                tabs[1].classList.add('active');
            }
        }
        
        function previewProfileImage(url) {
            const preview = document.getElementById('profile-preview');
            if(url && (url.startsWith('http') || url.startsWith('https'))) {
                preview.innerHTML = `<img src="${url}">`;
            } else if(!url) {
                preview.innerHTML = '';
            }
        }
        
        function previewProfileFile(input) {
            const preview = document.getElementById('profile-preview');
            if(input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}">`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>