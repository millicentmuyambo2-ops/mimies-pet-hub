<?php
session_start();
require_once('includes/config.php');

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        // Check in admin_users table
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            // Redirect based on role
            if ($user['role'] == 'admin') {
                header('Location: admin/index.php');
            } else {
                header('Location: dashboard.php');
            }
            exit();
        } else {
            $error = "Invalid username or password";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Poppins', sans-serif; background: linear-gradient(135deg, #ff7aa2, #ff4f83); min-height: 100vh; display: flex; align-items: center; justify-content: center; }
        .login-container { background: white; border-radius: 20px; padding: 40px; width: 100%; max-width: 400px; margin: 20px; box-shadow: 0 10px 30px rgba(0,0,0,0.2); }
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
        .register-link { text-align: center; margin-top: 20px; }
        .register-link a { color: #ff7aa2; text-decoration: none; }
        .demo-info { background: #f9f9f9; padding: 15px; border-radius: 8px; margin-top: 20px; font-size: 12px; color: #666; text-align: center; }
    </style>
</head>
<body>
<div class="login-container">
    <div class="logo">
        <h1>🐾 Mimie's Pet Hub</h1>
        <p>Login to your account</p>
    </div>
    
    <?php if($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>
    
    <form method="POST">
        <div class="form-group">
            <label>Username</label>
            <input type="text" name="username" required autofocus>
        </div>
        
        <div class="form-group">
            <label>Password</label>
            <input type="password" name="password" required>
        </div>
        
        <button type="submit">Login</button>
    </form>
    
    <div class="register-link">
        Don't have an account? <a href="register.php">Register here</a>
    </div>
    
    <div class="demo-info">
        <strong>Demo Credentials:</strong><br>
        Username: admin<br>
        Password: admin123
    </div>
</div>
</body>
</html>