<?php
session_start();
require_once('../includes/config.php');

if(!isset($_SESSION['user_id']) || $_SESSION['role'] != 'admin') {
    header("Location: login.php");
    exit();
}

$id = (int)($_GET['id'] ?? 0);

if($id > 0) {
    $stmt = $pdo->prepare("DELETE FROM pets WHERE id = ?");
    $stmt->execute([$id]);
}

header("Location: pets.php");
exit();
?>