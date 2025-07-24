<?php
require_once 'config.php';
requireLogin();

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Nur POST-Requests erlaubt']);
    exit;
}

if (!isset($_FILES['profile_image']) || $_FILES['profile_image']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(['error' => 'Kein Bild hochgeladen oder Upload-Fehler']);
    exit;
}

$file = $_FILES['profile_image'];
$uploadDir = 'uploads/profile_images/';

// Datei-Validierung
$allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
$maxFileSize = 5 * 1024 * 1024; // 5MB

if (!in_array($file['type'], $allowedTypes)) {
    echo json_encode(['error' => 'Nur JPEG, PNG, GIF und WebP Bilder sind erlaubt']);
    exit;
}

if ($file['size'] > $maxFileSize) {
    echo json_encode(['error' => 'Datei ist zu groß (max. 5MB)']);
    exit;
}

// Dateiname generieren
$extension = pathinfo($file['name'], PATHINFO_EXTENSION);
$filename = 'profile_' . uniqid() . '_' . time() . '.' . strtolower($extension);
$uploadPath = $uploadDir . $filename;

// Verzeichnis erstellen falls nicht vorhanden
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

// Datei verschieben
if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
    // Bild verkleinern/optimieren
    $optimizedPath = optimizeImage($uploadPath, $uploadDir . 'opt_' . $filename);
    
    if ($optimizedPath) {
        // Original löschen, optimierte Version behalten
        unlink($uploadPath);
        $finalPath = $optimizedPath;
    } else {
        $finalPath = $uploadPath;
    }
    
    // Absolute URL generieren
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $absoluteUrl = $protocol . '://' . $host . '/' . $finalPath;
    
    echo json_encode([
        'success' => true,
        'url' => $absoluteUrl,
        'relativePath' => $finalPath,
        'message' => 'Bild erfolgreich hochgeladen'
    ]);
} else {
    echo json_encode(['error' => 'Fehler beim Speichern der Datei']);
}

function optimizeImage($sourcePath, $destPath) {
    $imageInfo = getimagesize($sourcePath);
    if (!$imageInfo) return false;
    
    $width = $imageInfo[0];
    $height = $imageInfo[1];
    $type = $imageInfo[2];
    
    // Maximale Größe für Profilbilder
    $maxWidth = 400;
    $maxHeight = 400;
    
    // Neue Dimensionen berechnen
    if ($width > $maxWidth || $height > $maxHeight) {
        $ratio = min($maxWidth / $width, $maxHeight / $height);
        $newWidth = round($width * $ratio);
        $newHeight = round($height * $ratio);
    } else {
        $newWidth = $width;
        $newHeight = $height;
    }
    
    // Bild erstellen je nach Typ
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
    
    // Neues Bild erstellen
    $destination = imagecreatetruecolor($newWidth, $newHeight);
    
    // Transparenz für PNG und GIF beibehalten
    if ($type == IMAGETYPE_PNG || $type == IMAGETYPE_GIF) {
        imagealphablending($destination, false);
        imagesavealpha($destination, true);
        $transparent = imagecolorallocatealpha($destination, 255, 255, 255, 127);
        imagefilledrectangle($destination, 0, 0, $newWidth, $newHeight, $transparent);
    }
    
    // Bild skalieren
    imagecopyresampled($destination, $source, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
    
    // Speichern
    $success = false;
    switch ($type) {
        case IMAGETYPE_JPEG:
            $success = imagejpeg($destination, $destPath, 85);
            break;
        case IMAGETYPE_PNG:
            $success = imagepng($destination, $destPath, 6);
            break;
        case IMAGETYPE_GIF:
            $success = imagegif($destination, $destPath);
            break;
        case IMAGETYPE_WEBP:
            $success = imagewebp($destination, $destPath, 85);
            break;
    }
    
    // Speicher freigeben
    imagedestroy($source);
    imagedestroy($destination);
    
    return $success ? $destPath : false;
}
?>
