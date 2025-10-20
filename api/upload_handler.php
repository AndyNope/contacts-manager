<?php
/**
 * Profile Image Upload Handler
 * Handles profile picture uploads for private profiles
 */

session_start();

// Debug session info
error_log('Upload handler - Session user_id: ' . ($_SESSION['user_id'] ?? 'not set'));
error_log('Upload handler - Session is_private_profile: ' . (isset($_SESSION['is_private_profile']) ? ($_SESSION['is_private_profile'] ? 'true' : 'false') : 'not set'));
error_log('Upload handler - Session user_role: ' . ($_SESSION['user_role'] ?? 'not set'));

// Check if user is logged in (either private profile user or company admin)
if (!isset($_SESSION['user_id'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Access denied - not logged in']);
    exit;
}

// Allow both private profile users and company admins
$isPrivateUser = isset($_SESSION['is_private_profile']) && $_SESSION['is_private_profile'];
$isCompanyAdmin = isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && !$isPrivateUser;

if (!$isPrivateUser && !$isCompanyAdmin) {
    error_log('Upload handler - Access denied. Private: ' . ($isPrivateUser ? 'yes' : 'no') . ', Admin: ' . ($isCompanyAdmin ? 'yes' : 'no'));
    http_response_code(403);
    echo json_encode(['error' => 'Access denied - insufficient permissions']);
    exit;
}

header('Content-Type: application/json');

error_log('Upload handler called - Request method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Upload handler called - FILES: ' . print_r($_FILES, true));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Only POST requests allowed']);
    exit;
}

if (!isset($_FILES['profile_image'])) {
    echo json_encode(['error' => 'No profile_image file in request']);
    exit;
}

if ($_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    $errorMessages = [
        UPLOAD_ERR_INI_SIZE => 'File exceeds upload_max_filesize',
        UPLOAD_ERR_FORM_SIZE => 'File exceeds MAX_FILE_SIZE',
        UPLOAD_ERR_PARTIAL => 'File was only partially uploaded',
        UPLOAD_ERR_NO_FILE => 'No file was uploaded',
        UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder',
        UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk',
        UPLOAD_ERR_EXTENSION => 'Upload stopped by extension'
    ];
    
    $error = $_FILES['profile_image']['error'];
    $message = $errorMessages[$error] ?? 'Unknown upload error';
    echo json_encode(['error' => "Upload error: $message (Code: $error)"]);
    exit;
}

$file = $_FILES['profile_image'];
$uploadDir = dirname(__DIR__) . '/uploads/profile_images/';

error_log('Upload handler - Upload directory: ' . $uploadDir);
error_log('Upload handler - Directory exists: ' . (is_dir($uploadDir) ? 'yes' : 'no'));
error_log('Upload handler - Directory writable: ' . (is_writable($uploadDir) ? 'yes' : 'no'));

// File validation
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Only JPEG, PNG, GIF and WebP images are allowed']);
    exit;
}

if ($file['size'] > $maxFileSize) {
    echo json_encode(['error' => 'File is too large (max. 5MB)']);
    exit;
}

// Generate filename
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . uniqid() . '_' . time() . '.' . strtolower($extension);
$uploadPath = $uploadDir . $filename;

// Create directory if it doesn't exist
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Move uploaded file
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Optimize image
    $optimizedFileName = 'opt_' . $filename;
    $optimizedPath = $uploadDir . $optimizedFileName;
    
    $finalFile = optimizeImage($uploadPath, $optimizedPath);
    
    if ($finalFile) {
        // Delete original, keep optimized version
        unlink($uploadPath);
        $finalPath = $optimizedPath;
        $finalFileName = $optimizedFileName;
    } else {
        $finalPath = $uploadPath;
        $finalFileName = $filename;
    }
    
    // Generate absolute URL
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $relativePath = 'uploads/profile_images/' . $finalFileName;
    $absoluteUrl = $protocol . '://' . $host . '/' . $relativePath;
    
    error_log('Upload handler - Final path: ' . $finalPath);
    error_log('Upload handler - Final filename: ' . $finalFileName);
    error_log('Upload handler - Relative path: ' . $relativePath);
    error_log('Upload handler - Absolute URL: ' . $absoluteUrl);
    
    echo json_encode([
        'success' => true,
        'url' => $absoluteUrl,
        'relativePath' => $relativePath,
        'message' => 'Image uploaded successfully'
    ]);
} else {
    echo json_encode(['error' => 'Error saving file']);
}

function optimizeImage($sourcePath, $destPath) {
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) return false;
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Maximum size for profile pictures
    $maxWidth = 400;
    $maxHeight = 400;
    
    // Calculate new dimensions
    if ($width > $height) {
        $newWidth = min($width, $maxWidth);
        $newHeight = intval($height * ($newWidth / $width));
    } else {
        $newHeight = min($height, $maxHeight);
        $newWidth = intval($width * ($newHeight / $height));
    }
    
    // Create image resource from source
    switch ($type) {
        case IMAGETYPE_JPEG:
            $source = imagecreatefromjpeg($sourcePath);
            break;
        case IMAGETYPE_PNG:
            $source = imagecreatefrompng($sourcePath);
            break;
        case IMAGETYPE_GIF:
            $source = imagecreatefromgif($sourcePath);
            break;
        case IMAGETYPE_WEBP:
            $source = imagecreatefromwebp($sourcePath);
            break;
        default:
            return false;
    }
    
    if (!$source) return false;
    
    // Create new image with calculated dimensions
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Preserve transparency for PNG and GIF
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Resize image
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Save optimized image
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($destination, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($destination, $destPath, 8);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($destination, $destPath);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($destination, $destPath, 85);
            break;
    }
    
    // Free memory
    imagedestroy($source);
    imagedestroy($destination);
    
    return $success ? $destPath : false;
}
?>