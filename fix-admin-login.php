<?php
require_once('includes/config.php');

echo "<!DOCTYPE html>
<html>
<head>
    <title>Fix Admin Login</title>
    <style>
        body { font-family: 'Poppins', Arial, sans-serif; background: #f5f5f5; padding: 50px; text-align: center; }
        .container { max-width: 600px; margin: 0 auto; background: white; padding: 40px; border-radius: 20px; box-shadow: 0 2px 15px rgba(0,0,0,0.1); }
        h1 { color: #ff5c8a; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 10px; margin: 15px 0; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 10px; margin: 15px 0; }
        .info { background: #e3f2fd; padding: 15px; border-radius: 10px; margin: 15px 0; text-align: left; }
        .btn { background: #ff7aa2; color: white; padding: 12px 30px; border-radius: 8px; text-decoration: none; display: inline-block; margin-top: 20px; }
        .btn:hover { background: #ff4f83; }
        table { width: 100%; border-collapse: collapse; margin: 15px 0; }
        th, td { padding: 10px; border: 1px solid #ddd; text-align: left; }
        th { background: #ff7aa2; color: white; }
    </style>
</head>
<body>
<div class='container'>
    <h1>🔧 Fix Admin Login</h1>
    <p>This script will reset the admin password to <strong>admin123</strong></p>";

try {
    // Check if table exists
    $check = $pdo->query("SHOW TABLES LIKE 'admin_users'");
    if ($check->rowCount() == 0) {
        echo "<div class='error'>❌ Table 'admin_users' does not exist! Creating...</div>";
        
        // Create table
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
        echo "<div class='success'>✅ Table 'admin_users' created!</div>";
    }
    
    // Show current users
    $users = $pdo->query("SELECT id, username, email, role FROM admin_users");
    echo "<h3>📋 Current Users in Database:</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    while($row = $users->fetch()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    // Delete all existing admin users
    $pdo->exec("DELETE FROM admin_users WHERE username = 'admin'");
    
    // Hash the password 'admin123'
    $hashed_password = password_hash('admin123', PASSWORD_DEFAULT);
    
    // Insert new admin
    $stmt = $pdo->prepare("INSERT INTO admin_users (username, password, email, full_name, role, created_at) 
                           VALUES (?, ?, ?, ?, 'admin', NOW())");
    $result = $stmt->execute(['admin', $hashed_password, 'admin@mimiespethub.com', 'Administrator']);
    
    if ($result) {
        echo "<div class='success'>✅ Admin user created successfully!</div>";
    } else {
        echo "<div class='error'>❌ Failed to create admin user</div>";
    }
    
    // Also update any user with empty role to 'user'
    $pdo->exec("UPDATE admin_users SET role = 'user' WHERE role IS NULL OR role = ''");
    
    // Verify
    $stmt = $pdo->prepare("SELECT * FROM admin_users WHERE username = 'admin'");
    $stmt->execute();
    $admin = $stmt->fetch();
    
    if ($admin && password_verify('admin123', $admin['password'])) {
        echo "<div class='success'>✅ Password verification successful! Login is ready.</div>";
    } else {
        echo "<div class='error'>❌ Password verification failed. Please try again.</div>";
    }
    
    // Show all users after fix
    $users = $pdo->query("SELECT id, username, email, role FROM admin_users");
    echo "<h3>📋 Users After Fix:</h3>";
    echo "<table>";
    echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
    while($row = $users->fetch()) {
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['username']) . "</td>";
        echo "<td>" . htmlspecialchars($row['email']) . "</td>";
        echo "<td>" . htmlspecialchars($row['role']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<div class='info'>
            <strong>🔑 Login Credentials:</strong><br>
            Username: <strong>admin</strong><br>
            Password: <strong>admin123</strong><br><br>
            <strong>Other Users:</strong><br>
            Username: chido<br>
            Password: admin123 (if you want to set it)
          </div>";
    
    echo "<a href='login.php' class='btn'>Go to Login →</a>";
    
} catch(PDOException $e) {
    echo "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
}

echo "</div></body></html>";
?>