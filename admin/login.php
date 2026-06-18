<?php
session_start();
require_once('../includes/config.php');

$error = '';

// Check if admin_users table exists, create if not
try {
    $check = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($check->rowCount() == 0) {
        $pdo->exec("CREATE TABLE admin_users (
            id INT PRIMARY KEY AUTO_INCREMENT,
            username VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            email VARCHAR(200),
            full_name VARCHAR(200),
            phone VARCHAR(50),
            role VARCHAR(50) DEFAULT 'user',
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )");
        $hashed = password_hash('admin123', PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email, full_name, role) VALUES (?, ?, ?, ?, 'admin')");
        $stmt->execute(['admin', $hashed, 'admin@mimiespethub.com', 'Administrator']);
    }
} catch(PDOException $e) {}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    
    if (empty($username) || empty($password)) {
        $error = "Please enter username and password";
    } else {
        $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['full_name'] = $user['full_name'];
            $_SESSION['email'] = $user['email'];
            $_SESSION['role'] = $user['role'];
            $_SESSION['logged_in'] = true;
            
            if ($user['role'] == 'admin') {
                header('Location: index.php');
            } else {
                header('Location: ../dashboard.php');
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
    <title>Admin Login - Mimie's Pet Hub</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            border-radius: 30px;
            padding: 3rem;
            width: 100%;
            max-width: 420px;
            box-shadow: 0 20px 60px rgba(255,107,139,0.3);
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #ff6b8b;
            font-size: 2rem;
            font-weight: 700;
        }
        .login-header p {
            color: #888;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }
        .form-group {
            margin-bottom: 1.2rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
            color: #5a2a3a;
            font-size: 0.9rem;
        }
        .form-group input {
            width: 100%;
            padding: 0.8rem 1rem;
            border: 2px solid #ffe0e5;
            border-radius: 12px;
            font-size: 0.95rem;
            transition: 0.3s;
            font-family: inherit;
        }
        .form-group input:focus {
            outline: none;
            border-color: #ff6b8b;
        }
        .btn-login {
            width: 100%;
            padding: 0.9rem;
            background: linear-gradient(135deg, #ff6b8b, #ff8da1);
            border: none;
            border-radius: 12px;
            color: white;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: 0.3s;
            font-family: inherit;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(255,107,139,0.4);
        }
        .error {
            background: #ffe0e0;
            color: #f44336;
            padding: 0.8rem;
            border-radius: 12px;
            margin-bottom: 1rem;
            text-align: center;
            font-size: 0.9rem;
        }
        .demo-info {
            background: #fff5f7;
            padding: 1rem;
            border-radius: 12px;
            margin-top: 1.5rem;
            text-align: center;
            font-size: 0.8rem;
            color: #888;
        }
        .demo-info strong {
            color: #ff6b8b;
        }
        .back-link {
            text-align: center;
            margin-top: 1rem;
        }
        .back-link a {
            color: #ff6b8b;
            text-decoration: none;
            font-size: 0.85rem;
        }
        .back-link a:hover {
            text-decoration: underline;
        }
        @media (max-width: 768px) {
            .login-container { padding: 2rem; margin: 1rem; }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-header">
            <h1>🐾 Mimie's Pet Hub</h1>
            <p>Admin Login</p>
        </div>
        
        <?php if($error): ?>
            <div class="error"><i class="fas fa-exclamation-circle"></i> <?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label><i class="fas fa-user"></i> Username</label>
                <input type="text" name="username" value="admin" required autofocus>
            </div>
            <div class="form-group">
                <label><i class="fas fa-lock"></i> Password</label>
                <input type="password" name="password" value="admin123" required>
            </div>
            <button type="submit" class="btn-login"><i class="fas fa-sign-in-alt"></i> Login</button>
        </form>
        
        <div class="demo-info">
            <strong>Demo Credentials:</strong><br>
            Username: admin &nbsp;|&nbsp; Password: admin123
        </div>
        
        <div class="back-link">
            <a href="../index.php"><i class="fas fa-arrow-left"></i> Back to Website</a>
        </div>
    </div>
</body>
</html>