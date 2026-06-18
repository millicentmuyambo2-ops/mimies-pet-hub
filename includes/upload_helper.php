<?php
function uploadImage($file, $target_dir = null) {
    // Set correct path
    if($target_dir === null) {
        $target_dir = __DIR__ . '/../assets/uploads/';
    }
    
    // Create directory if it doesn't exist
    if(!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    $max_size = 5 * 1024 * 1024; // 5MB
    
    if($file['error'] != 0) {
        return ['success' => false, 'message' => 'No file uploaded or upload error'];
    }
    
    $file_ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    if(!in_array($file_ext, $allowed)) {
        return ['success' => false, 'message' => 'Invalid file type. Allowed: JPG, PNG, GIF, WEBP'];
    }
    
    if($file['size'] > $max_size) {
        return ['success' => false, 'message' => 'File too large. Max 5MB'];
    }
    
    $new_filename = time() . '_' . uniqid() . '.' . $file_ext;
    $target_file = $target_dir . $new_filename;
    
    if(move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $new_filename, 'path' => 'assets/uploads/' . $new_filename];
    } else {
        return ['success' => false, 'message' => 'Failed to upload file. Check folder permissions.'];
    }
}
?>