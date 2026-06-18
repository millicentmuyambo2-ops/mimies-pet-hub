<?php
require_once('includes/config.php');
require_once('header.php');

// Handle form submission
$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $pet_type = $_POST['pet_type'] ?? '';
    $pet_color = $_POST['pet_color'] ?? '';
    $location = $_POST['location'] ?? 'Bindura, Zimbabwe';
    $description = $_POST['description'] ?? '';
    $owner_name = $_POST['owner_name'] ?? '';
    $owner_email = $_POST['owner_email'] ?? '';
    $owner_phone = $_POST['owner_phone'] ?? '';
    
    // Handle image upload
    $image_path = null;
    if (isset($_FILES['pet_image']) && $_FILES['pet_image']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/';
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }
        $file_extension = pathinfo($_FILES['pet_image']['name'], PATHINFO_EXTENSION);
        $new_filename = time() . '_' . rand(1000, 9999) . '.' . $file_extension;
        $image_path = $upload_dir . $new_filename;
        move_uploaded_file($_FILES['pet_image']['tmp_name'], $image_path);
    }
    
    if (empty($pet_type) || empty($owner_name) || empty($owner_email)) {
        $message = "Please fill all required fields";
        $messageType = "error";
    } else {
        try {
            $tracking_code = 'LOST-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
            
            $sql = "INSERT INTO lost_found (pet_type, pet_color, location, description, image_path, owner_name, owner_email, owner_phone, status, tracking_code, reported_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'lost', ?, NOW())";
            $stmt = $pdo->prepare($sql);
            $result = $stmt->execute([$pet_type, $pet_color, $location, $description, $image_path, $owner_name, $owner_email, $owner_phone, $tracking_code]);
            
            if ($result) {
                $message = "Lost pet reported! Tracking code: $tracking_code";
                $messageType = "success";
            } else {
                $message = "Database error. Please try again.";
                $messageType = "error";
            }
        } catch(PDOException $e) {
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

// Get lost pets from database
$stmt = $pdo->prepare("SELECT * FROM lost_found WHERE status = 'lost' ORDER BY reported_at DESC");
$stmt->execute();
$lost_pets = $stmt->fetchAll();
?>

<style>
    .container { max-width: 1200px; margin: 40px auto; padding: 20px; }
    .two-columns { display: grid; grid-template-columns: 1fr 1fr; gap: 30px; }
    .form-box, .list-box { background: white; border-radius: 15px; padding: 25px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    .form-group { margin-bottom: 15px; }
    label { display: block; margin-bottom: 5px; font-weight: bold; }
    input, select, textarea { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 8px; }
    button { background: #ff7aa2; color: white; border: none; padding: 12px; border-radius: 8px; cursor: pointer; width: 100%; font-weight: bold; }
    .lost-card { background: #fff3e0; border-radius: 10px; padding: 15px; margin-bottom: 15px; display: flex; gap: 15px; align-items: center; }
    .lost-card img { width: 80px; height: 80px; object-fit: cover; border-radius: 10px; background: #ddd; }
    .lost-info { flex: 1; }
    .lost-badge { background: #ff9800; color: white; padding: 2px 8px; border-radius: 10px; font-size: 10px; display: inline-block; margin-bottom: 8px; }
    .contact-btn { background: #2196f3; color: white; border: none; padding: 5px 15px; border-radius: 5px; cursor: pointer; margin-top: 5px; }
    .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; }
    h1 { text-align: center; color: #ff5c8a; margin-bottom: 30px; }
    @media (max-width: 768px) { .two-columns { grid-template-columns: 1fr; } }
</style>

<div class="container">
    <h1>🔍 Lost & Found Pets in Bindura</h1>
    
    <div class="two-columns">
        <!-- Report Lost Pet Form -->
        <div class="form-box">
            <h3>📝 Report a Lost Pet</h3>
            <?php if($message): ?>
                <div class="<?php echo $messageType; ?>"><?php echo $message; ?></div>
            <?php endif; ?>
            <form method="POST" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Pet Type *</label>
                    <select name="pet_type" required>
                        <option value="">Select Pet Type</option>
                        <option value="cat">🐱 Cat</option>
                        <option value="dog">🐶 Dog</option>
                        <option value="bird">🐦 Bird</option>
                        <option value="other">Other</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Color/Markings *</label>
                    <input type="text" name="pet_color" placeholder="e.g., White with brown spots" required>
                </div>
                
                <div class="form-group">
                    <label>Last Seen Location</label>
                    <input type="text" name="location" placeholder="Bindura, Zimbabwe" value="Bindura, Zimbabwe">
                </div>
                
                <div class="form-group">
                    <label>Description</label>
                    <textarea name="description" rows="3" placeholder="Describe the pet and when it went missing"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Pet Photo (Optional)</label>
                    <input type="file" name="pet_image" accept="image/*">
                </div>
                
                <div class="form-group">
                    <label>Your Name *</label>
                    <input type="text" name="owner_name" required>
                </div>
                
                <div class="form-group">
                    <label>Your Email *</label>
                    <input type="email" name="owner_email" required>
                </div>
                
                <div class="form-group">
                    <label>Your Phone</label>
                    <input type="tel" name="owner_phone" placeholder="077XXXXXXX">
                </div>
                
                <button type="submit">📢 Report Lost Pet</button>
            </form>
        </div>
        
        <!-- List of Lost Pets -->
        <div class="list-box">
            <h3>🐾 Currently Lost Pets</h3>
            <?php if(count($lost_pets) > 0): ?>
                <?php foreach($lost_pets as $pet): ?>
                <div class="lost-card">
                    <img src="<?php echo htmlspecialchars($pet['image_path'] ?? 'https://via.placeholder.com/80x80?text=No+Image'); ?>">
                    <div class="lost-info">
                        <span class="lost-badge">⚠️ LOST</span>
                        <h3><?php echo ucfirst($pet['pet_type']); ?></h3>
                        <p><strong>Color:</strong> <?php echo htmlspecialchars($pet['pet_color']); ?></p>
                        <p><strong>Location:</strong> <?php echo htmlspecialchars($pet['location']); ?></p>
                        <p><small>Reported: <?php echo date('M d, Y', strtotime($pet['reported_at'])); ?></small></p>
                        <button class="contact-btn" onclick="contactOwner('<?php echo $pet['owner_email']; ?>', '<?php echo $pet['pet_type']; ?>')">📞 I Found This Pet</button>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="text-align: center; padding: 20px;">No lost pets reported yet.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
function contactOwner(email, petType) {
    window.location.href = 'mailto:' + email + '?subject=I found a lost ' + petType + ' in Bindura';
}
</script>

<?php require_once('footer.php'); ?>