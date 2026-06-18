<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: ../login.php");
    exit();
}

$report_id = $_GET['id'] ?? 0;
$report = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, u.username, u.email, u.phone FROM lost_found_reports r JOIN users u ON r.user_id = u.id WHERE r.id = $report_id"));

if(!$report) {
    header("Location: reports.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $status = $_POST['status'];
    $admin_response = mysqli_real_escape_string($conn, $_POST['admin_response']);
    mysqli_query($conn, "UPDATE lost_found_reports SET status='$status', admin_response='$admin_response' WHERE id=$report_id");
    $success = "Report updated!";
    $report = mysqli_fetch_assoc(mysqli_query($conn, "SELECT r.*, u.username, u.email, u.phone FROM lost_found_reports r JOIN users u ON r.user_id = u.id WHERE r.id = $report_id"));
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>View Report - Admin</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="form-container" style="max-width: 800px;">
        <h2>Report Details</h2>
        <?php if(isset($success)) echo "<p class='success'>$success</p>"; ?>
        
        <div style="background: #f9f9f9; padding: 1rem; border-radius: 10px; margin-bottom: 1rem;">
            <p><strong>Type:</strong> <?php echo ucfirst($report['type']); ?></p>
            <p><strong>Pet Name:</strong> <?php echo $report['pet_name'] ?: 'Unknown'; ?></p>
            <p><strong>Species:</strong> <?php echo $report['species']; ?></p>
            <p><strong>Breed:</strong> <?php echo $report['breed'] ?: 'N/A'; ?></p>
            <p><strong>Color:</strong> <?php echo $report['color'] ?: 'N/A'; ?></p>
            <p><strong>Location:</strong> <?php echo $report['location']; ?></p>
            <p><strong>Date:</strong> <?php echo $report['date']; ?></p>
            <p><strong>Description:</strong> <?php echo nl2br($report['description']); ?></p>
            <p><strong>Contact:</strong> <?php echo $report['contact_phone'] ?: $report['phone']; ?></p>
            <p><strong>Reported by:</strong> <?php echo $report['username']; ?> (<?php echo $report['email']; ?>)</p>
            
            <?php if($report['image']): ?>
                <p><strong>Image:</strong></p>
                <img src="../assets/uploads/<?php echo $report['image']; ?>" style="max-width: 300px; border-radius: 10px;">
            <?php endif; ?>
        </div>
        
        <form method="POST">
            <label>Admin Response:</label>
            <textarea name="admin_response" rows="4"><?php echo $report['admin_response']; ?></textarea>
            
            <label>Status:</label>
            <select name="status">
                <option value="open" <?php echo $report['status'] == 'open' ? 'selected' : ''; ?>>Open</option>
                <option value="resolved" <?php echo $report['status'] == 'resolved' ? 'selected' : ''; ?>>Resolved</option>
                <option value="closed" <?php echo $report['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
            </select>
            
            <button type="submit">Update Report</button>
        </form>
        
        <a href="reports.php" style="display: block; text-align: center; margin-top: 1rem;">← Back to Reports</a>
    </div>
</body>
</html>