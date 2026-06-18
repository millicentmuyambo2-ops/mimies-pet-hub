<?php
require_once('includes/config.php');

// Delete existing test users (optional)
mysqli_query($conn, "DELETE FROM users WHERE email IN ('john@example.com', 'jane@example.com')");

// Create users with working passwords
$password_hash = password_hash('admin123', PASSWORD_DEFAULT);

// Create Admin
$admin_query = "INSERT INTO users (username, email, password, phone, role) VALUES 
                ('admin', 'admin@mimiespethub.com', '$password_hash', '0771234567', 'admin')
                ON DUPLICATE KEY UPDATE password = '$password_hash', role = 'admin'";
mysqli_query($conn, $admin_query);

// Create John
$john_query = "INSERT INTO users (username, email, password, phone, role) VALUES 
               ('john_doe', 'john@example.com', '$password_hash', '0771234568', 'user')
               ON DUPLICATE KEY UPDATE password = '$password_hash'";
mysqli_query($conn, $john_query);

// Create Jane
$jane_query = "INSERT INTO users (username, email, password, phone, role) VALUES 
               ('jane_smith', 'jane@example.com', '$password_hash', '0771234569', 'user')
               ON DUPLICATE KEY UPDATE password = '$password_hash'";
mysqli_query($conn, $jane_query);

// Verify users
$result = mysqli_query($conn, "SELECT id, username, email, role FROM users");
echo "<h2>Users in Database:</h2>";
echo "<table border='1' cellpadding='10'>";
echo "<tr><th>ID</th><th>Username</th><th>Email</th><th>Role</th></tr>";
while($row = mysqli_fetch_assoc($result)) {
    echo "<tr>";
    echo "<td>" . $row['id'] . "</td>";
    echo "<td>" . $row['username'] . "</td>";
    echo "<td>" . $row['email'] . "</td>";
    echo "<td>" . $row['role'] . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<br><h3>✅ Login Credentials:</h3>";
echo "<p><strong>Admin:</strong> admin@mimiespethub.com / admin123</p>";
echo "<p><strong>User 1:</strong> john@example.com / admin123</p>";
echo "<p><strong>User 2:</strong> jane@example.com / admin123</p>";

echo "<br><a href='login.php' style='background:#ff6b8b; color:white; padding:10px 20px; text-decoration:none; border-radius:25px;'>Go to Login →</a>";
?>