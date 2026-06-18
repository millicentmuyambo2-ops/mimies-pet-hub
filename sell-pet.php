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
$user_id = $_SESSION['user_id'] ?? 0;
$user_name = $_SESSION['full_name'] ?? $_SESSION['username'] ?? 'User';
$user_email = $_SESSION['email'] ?? '';

$success = '';
$error = '';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $pet_name = trim($_POST['name'] ?? '');
    $pet_type = trim($_POST['species'] ?? '');
    $pet_breed = trim($_POST['breed'] ?? '');
    $pet_age = trim($_POST['age'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $description = trim($_POST['description'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $city = trim($_POST['city'] ?? '');
    $location = trim($_POST['location'] ?? '');
    $seller_name = $user_name;
    $seller_email = $user_email;
    
    // Handle image upload
    $image_url = '';
    $upload_type = $_POST['upload_type'] ?? 'url';
    
    if($upload_type == 'file' && isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] == 0) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
        $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (in_array(strtolower($file_extension), $allowed)) {
            $new_filename = time() . '_' . rand(1000, 9999) . '.' . $file_extension;
            $image_url = $upload_dir . $new_filename;
            move_uploaded_file($_FILES['pet_image']['tmp_name'], $image_url);
        } else {
            $error = "Invalid file type. Please upload JPG, PNG, or GIF.";
        }
    } elseif($upload_type == 'url' && !empty($_POST['image_url'])) {
        $image_url = trim($_POST['image_url']);
    }
    
    if(empty($error)) {
        if(empty($pet_name) || empty($pet_type) || empty($price)) {
            $error = "Please fill all required fields";
        } else {
            try {
                $tracking_code = 'SELL-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
                $full_location = $location ? "$city, $province - $location" : "$city, $province";
                
                $sql = "INSERT INTO user_pets (pet_name, pet_type, pet_breed, pet_age, price, description, image_url, location, seller_name, seller_email, status, tracking_code, created_at) 
                        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, NOW())";
                $stmt = $pdo->prepare($sql);
                $result = $stmt->execute([$pet_name, $pet_type, $pet_breed, $pet_age, $price, $description, $image_url, $full_location, $seller_name, $seller_email, $tracking_code]);
                
                if($result) {
                    $success = "✅ Pet listed successfully! Tracking code: $tracking_code. Admin will approve it soon.";
                } else {
                    $error = "❌ Failed to list pet. Please try again.";
                }
            } catch(PDOException $e) {
                $error = "❌ Database error: " . $e->getMessage();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sell a Pet - Mimie's Pet Hub</title>
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
        .container { max-width: 700px; margin: 2rem auto; padding: 0 1rem; }
        .form-card {
            background: white;
            border-radius: 30px;
            padding: 2rem;
            box-shadow: 0 10px 30px rgba(255,107,139,0.1);
        }
        .form-card h2 { color: #ff6b8b; text-align: center; margin-bottom: 0.5rem; }
        .form-card p { text-align: center; color: #666; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #5a2a3a;
            font-weight: 500;
        }
        .form-group input, .form-group select, .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #ffe0e5;
            border-radius: 12px;
            font-family: inherit;
            font-size: 14px;
        }
        .form-group input:focus, .form-group select:focus, .form-group textarea:focus {
            outline: none;
            border-color: #ff6b8b;
        }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
        
        /* Image Upload Tabs */
        .upload-tabs {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1rem;
        }
        .tab-btn {
            flex: 1;
            padding: 0.6rem;
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
            max-width: 150px;
            border-radius: 12px;
            border: 2px solid #ffe0e5;
        }
        
        .btn-submit {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            border: none;
            border-radius: 30px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            margin-top: 1rem;
            transition: 0.3s;
        }
        .btn-submit:hover { transform: translateY(-2px); }
        .success { background: #d4edda; color: #155724; padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 0.8rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        .note { background: #fff0f3; padding: 1rem; border-radius: 12px; margin-top: 1rem; font-size: 0.85rem; text-align: center; }
        
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .form-row { grid-template-columns: 1fr; }
            .nav-links a { display: inline-block; margin: 5px; }
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
            <a href="profile.php"><i class="fas fa-user-circle"></i> Profile</a>
            <a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>
    
    <div class="container">
        <div class="form-card">
            <h2><i class="fas fa-paw"></i> List Your Pet for Sale</h2>
            <p>Find a loving home for your furry friend in Zimbabwe</p>
            
            <?php if($success): ?>
                <div class="success"><?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" enctype="multipart/form-data">
                <div class="form-row">
                    <div class="form-group">
                        <label>Pet Name *</label>
                        <input type="text" name="name" placeholder="e.g., Max, Luna" required>
                    </div>
                    <div class="form-group">
                        <label>Species *</label>
                        <select name="species" required>
                            <option value="Dog">🐕 Dog</option>
                            <option value="Cat">🐱 Cat</option>
                            <option value="Bird">🐦 Bird</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Breed</label>
                        <input type="text" name="breed" placeholder="e.g., Golden Retriever">
                    </div>
                    <div class="form-group">
                        <label>Age (months)</label>
                        <input type="text" name="age" placeholder="e.g., 24 months or 2 Years">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price ($) *</label>
                        <input type="number" step="0.01" name="price" placeholder="USD" required>
                    </div>
                    <div class="form-group">
                        <label>Province</label>
                        <select name="province" id="province">
                            <option value="">Select Province</option>
                            <?php foreach($zim_provinces as $p=>$c): ?>
                                <option value="<?php echo $p; ?>"><?php echo $p; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>City/Town</label>
                        <select name="city" id="city">
                            <option value="">Select City</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Specific Location</label>
                        <input type="text" name="location" placeholder="e.g., CBD, Suburb">
                    </div>
                </div>
                
                <!-- Image Upload Section -->
                <div class="form-group">
                    <label>Pet Photo</label>
                    <div class="upload-tabs">
                        <button type="button" class="tab-btn active" onclick="switchTab('url')">🌐 Online URL</button>
                        <button type="button" class="tab-btn" onclick="switchTab('file')">📁 Upload from Computer</button>
                    </div>
                    <div id="url-panel" class="upload-panel active">
                        <input type="hidden" name="upload_type" value="url" id="upload_type">
                        <input type="url" name="image_url" placeholder="https://images.unsplash.com/..." class="image-input" onchange="previewImage(this.value, 'url-preview')">
                        <div id="url-preview" class="image-preview"></div>
                    </div>
                    <div id="file-panel" class="upload-panel">
                        <input type="file" name="pet_image" accept="image/*" class="image-input" onchange="previewFile(this, 'file-preview')">
                        <div id="file-preview" class="image-preview"></div>
                    </div>
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="4" placeholder="Describe your pet's personality, health, habits..." required></textarea>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-check-circle"></i> List Pet</button>
                <div class="note">
                    <i class="fas fa-info-circle"></i> Your listing will be reviewed by admin before going live
                </div>
            </form>
        </div>
    </div>
    
    <script>
        const cities = <?php echo json_encode($zim_provinces); ?>;
        
        document.getElementById('province')?.addEventListener('change', function(){
            let citySelect = document.getElementById('city');
            citySelect.innerHTML = '<option value="">Select City</option>';
            if(cities[this.value]) {
                cities[this.value].forEach(c => {
                    let opt = document.createElement('option');
                    opt.value = c;
                    opt.textContent = c;
                    citySelect.appendChild(opt);
                });
            }
        });
        
        function switchTab(type) {
            document.getElementById('upload_type').value = type;
            document.getElementById('url-panel').classList.remove('active');
            document.getElementById('file-panel').classList.remove('active');
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            
            if(type === 'url') {
                document.getElementById('url-panel').classList.add('active');
                document.querySelectorAll('.tab-btn')[0].classList.add('active');
            } else {
                document.getElementById('file-panel').classList.add('active');
                document.querySelectorAll('.tab-btn')[1].classList.add('active');
            }
        }
        
        function previewImage(url, previewId) {
            const preview = document.getElementById(previewId);
            if(url && (url.startsWith('http') || url.startsWith('https'))) {
                preview.innerHTML = `<img src="${url}" onerror="this.src='https://via.placeholder.com/150?text=Invalid+URL'" style="max-width:150px; border-radius:12px;">`;
            } else {
                preview.innerHTML = '';
            }
        }
        
        function previewFile(input, previewId) {
            const preview = document.getElementById(previewId);
            if(input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.innerHTML = `<img src="${e.target.result}" style="max-width:150px; border-radius:12px;">`;
                };
                reader.readAsDataURL(input.files[0]);
            } else {
                preview.innerHTML = '';
            }
        }
    </script>
</body>
</html>