<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$id = (int)($_GET['id'] ?? 0);
if(!$id) {
    header("Location: pets.php");
    exit();
}

$stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
$stmt->execute([$id]);
$pet = $stmt->fetch();

if(!$pet) {
    header("Location: pets.php");
    exit();
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $type = trim($_POST['type'] ?? '');
    $breed = trim($_POST['breed'] ?? '');
    $age = trim($_POST['age'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $location = trim($_POST['location'] ?? 'Bindura, Zimbabwe');
    $status = trim($_POST['status'] ?? 'available');
    $image_url = trim($_POST['image_url'] ?? '');
    
    if (empty($name) || empty($type) || empty($price)) {
        $error = "Please fill all required fields";
    } else {
        try {
            $stmt = $pdo->prepare("UPDATE pets SET name=?, type=?, breed=?, age=?, price=?, location=?, image_url=?, status=? WHERE id=?");
            $stmt->execute([$name, $type, $breed, $age, $price, $location, $image_url, $status, $id]);
            $success = "Pet updated successfully!";
            // Refresh data
            $stmt = $pdo->prepare("SELECT * FROM pets WHERE id = ?");
            $stmt->execute([$id]);
            $pet = $stmt->fetch();
        } catch(PDOException $e) {
            $error = "Database error: " . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Edit Pet - Admin</title>
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
        .container { max-width: 600px; margin: 0 auto; padding: 2rem; }
        .form-card {
            background: white;
            padding: 2rem;
            border-radius: 25px;
            box-shadow: 0 10px 30px rgba(255,107,139,0.1);
        }
        .form-card h2 { color: #ff6b8b; font-weight: 700; margin-bottom: 1.5rem; }
        .form-group { margin-bottom: 1.2rem; }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #5a2a3a;
        }
        .form-group input, .form-group select {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #ffe0e5;
            border-radius: 12px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: 0.3s;
        }
        .form-group input:focus, .form-group select:focus {
            outline: none;
            border-color: #ff6b8b;
        }
        .btn-submit {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #ffc107, #ff9800);
            color: #333;
            border: none;
            border-radius: 12px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: inherit;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 5px 20px rgba(255,193,7,0.4); }
        .success { background: #d4edda; color: #155724; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        .error { background: #f8d7da; color: #721c24; padding: 1rem; border-radius: 12px; margin-bottom: 1rem; text-align: center; }
        .back-link {
            display: inline-block;
            margin-top: 1rem;
            color: #ff6b8b;
            text-decoration: none;
            font-weight: 500;
        }
        .back-link:hover { text-decoration: underline; }
        .current-image { margin-top: 0.5rem; }
        .current-image img { max-height: 80px; border-radius: 8px; border: 2px solid #ffe0e5; }
        footer {
            text-align: center;
            padding: 2rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            color: white;
            margin-top: 2rem;
        }
        .required { color: #dc3545; }
        @media (max-width: 768px) {
            .main-header { flex-direction: column; gap: 1rem; text-align: center; }
            .container { padding: 1rem; }
            .form-card { padding: 1.5rem; }
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
            <a href="../logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a>
        </div>
    </header>

    <div class="container">
        <div class="form-card">
            <h2><i class="fas fa-edit"></i> Edit Pet: <?php echo htmlspecialchars($pet['name']); ?></h2>
            
            <?php if($success): ?>
                <div class="success"><i class="fas fa-check-circle"></i> <?php echo $success; ?></div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Pet Name <span class="required">*</span></label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($pet['name']); ?>" required>
                </div>
                <div class="form-group">
                    <label>Type <span class="required">*</span></label>
                    <select name="type" required>
                        <option value="cat" <?php echo $pet['type'] == 'cat' ? 'selected' : ''; ?>>🐱 Cat</option>
                        <option value="dog" <?php echo $pet['type'] == 'dog' ? 'selected' : ''; ?>>🐶 Dog</option>
                        <option value="bird" <?php echo $pet['type'] == 'bird' ? 'selected' : ''; ?>>🐦 Bird</option>
                        <option value="other" <?php echo $pet['type'] == 'other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Breed</label>
                    <input type="text" name="breed" value="<?php echo htmlspecialchars($pet['breed']); ?>">
                </div>
                <div class="form-group">
                    <label>Age</label>
                    <input type="text" name="age" value="<?php echo htmlspecialchars($pet['age']); ?>">
                </div>
                <div class="form-group">
                    <label>Price ($) <span class="required">*</span></label>
                    <input type="number" step="0.01" name="price" value="<?php echo $pet['price']; ?>" required>
                </div>
                <div class="form-group">
                    <label>Location</label>
                    <input type="text" name="location" value="<?php echo htmlspecialchars($pet['location']); ?>">
                </div>
                <div class="form-group">
                    <label>Image URL</label>
                    <input type="text" name="image_url" value="<?php echo htmlspecialchars($pet['image_url']); ?>">
                    <?php if($pet['image_url']): ?>
                        <div class="current-image">
                            <img src="<?php echo htmlspecialchars($pet['image_url']); ?>" alt="Current image">
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Status</label>
                    <select name="status">
                        <option value="available" <?php echo $pet['status'] == 'available' ? 'selected' : ''; ?>>Available</option>
                        <option value="sold" <?php echo $pet['status'] == 'sold' ? 'selected' : ''; ?>>Sold</option>
                    </select>
                </div>
                <button type="submit" class="btn-submit"><i class="fas fa-save"></i> Update Pet</button>
            </form>
            <a href="pets.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Pets</a>
        </div>
    </div>

    <footer>
        <p>&copy; <?php echo date('Y'); ?> Mimie's Pet Hub | Admin Panel</p>
    </footer>
</body>
</html>