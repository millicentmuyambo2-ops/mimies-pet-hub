<?php
session_start();
require_once('includes/config.php');

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $fullname = trim($_POST['fullname'] ?? '');
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $confirm_password = trim($_POST['confirm_password'] ?? '');
    
    if (empty($fullname) || empty($username) || empty($email) || empty($password)) {
        $error = "Please fill all required fields";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Please enter a valid email address";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match";
    } elseif (strlen($password) < 6) {
        $error = "Password must be at least 6 characters";
    } else {
        // Check if username exists using PDO
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        
        if ($stmt->fetch()) {
            $error = "Username already exists";
        } else {
            // Check if email exists
            $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE email = ?");
            $stmt->execute([$email]);
            
            if ($stmt->fetch()) {
                $error = "Email already registered";
            } else {
                // Create new user
                $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email, full_name, role, created_at) VALUES (?, ?, ?, ?, 'user', NOW())");
                $result = $stmt->execute([$username, $hashed_password, $email, $fullname]);
                
                if ($result) {
                    $success = "Registration successful! You can now login.";
                } else {
                    $error = "Registration failed. Please try again.";
                }
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
    <title>Register - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #ff7aa2, #ff4f83); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .register-container { background: white; border-radius: 20px; padding: 40px; width: 100%; max-width: 450px; margin: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
        .logo { text-align: center; margin-bottom: 30px; }
        .logo h1 { color: #ff5c8a; font-size: 28px; }
        .logo p { color: #666; font-size: 14px; }
        .form-group { margin-bottom: 20px; }
        label { display: block; margin-bottom: 8px; font-weight: 500; color: #333; }
        input { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 8px; font-size: 14px; transition: border-color 0.3s; }
        input:focus { outline: none; border-color: #ff7aa2; }
        button { width: 100%; background: #ff7aa2; color: white; border: none; padding: 12px; border-radius: 8px; font-size: 16px; font-weight: bold; cursor: pointer; transition: background 0.3s; }
        button:hover { background: #ff4f83; }
        .error { background: #f8d7da; color: #721c24; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .success { background: #d4edda; color: #155724; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; }
        .login-link { text-align: center; margin-top: 20px; }
        .login-link a { color: #ff7aa2; text-decoration: none; }
        .demo-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
<div class="register-container">
    <div class="logo">
        <h1>🐾 Mimie's Pet Hub</h1>
        <p>Create your account</p>
    </div>
    
    <?php if($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <?php if($success): ?>
        <div class="success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Full Name *</label>
            <input type="text" name="fullname" required>
        </div>
        
        <div class="form-group">
            <label>Username *</label>
            <input type="text" name="username" required>
        </div>
        
        <div class="form-group">
            <label>Email *</label>
            <input type="email" name="email" required>
        </div>
        
        <div class="form-group">
            <label>Password *</label>
            <input type="password" name="password" required>
        </div>
        
        <div class="form-group">
            <label>Confirm Password *</label>
            <input type="password" name="confirm_password" required>
        </div>
        
        <button type="submit">Register</button>
    </form>
    
    <div class="login-link">
        Already have an account? <a href="login.php">Login here</a>
    </div>
    
    <div class="demo-info">
        <strong>Demo Admin Account:</strong><br>
        Username: admin<br>
        Password: admin123
    </div>
</div>
</body>
</html>