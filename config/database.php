<?php
/**
 * EasyContact Database Configuration
 * UPDATE THESE VALUES FOR YOUR HOSTING ENVIRONMENT
 */

// === HOSTING DATABASE CONFIGURATION ===
// Update these values with your actual hosting database credentials
$host = 'localhost'; // Your hosting database host 
$dbname = 'your_database_name'; // Your hosting database name (replace this!)
$user = 'your_database_username'; // Your hosting database username (replace this!)
$pass = 'your_database_password'; // Your hosting database password (replace this!)

// === FOR DEVELOPMENT (can be removed after hosting setup) ===
// Comment out these lines after updating the values above for hosting
if (file_exists(__DIR__ . '/../.env')) {
    // Development fallback
    $host = 'localhost';
    $dbname = 'easycontact';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
}

// Database connection function
function getDatabaseConnection() {
    global $host, $dbname, $user, $pass;
    
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ]);
        return $pdo;
    } catch (PDOException $e) {
        // Enhanced error reporting for debugging
        $errorMsg = 'Database connection failed: ' . $e->getMessage();
        error_log($errorMsg);
        
        // Return JSON error for API endpoints
        if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
            http_response_code(500);
            header('Content-Type: application/json');
            die(json_encode([
                'success' => false,
                'error' => 'Database connection failed',
                'debug' => [
                    'message' => $e->getMessage(),
                    'host' => $host,
                    'database' => $dbname,
                    'file' => __FILE__,
                    'line' => __LINE__
                ]
            ]));
        }
        
        throw new Exception('Database connection failed. Please check your database configuration.');
    }
}

// Create global connection for backward compatibility
try {
    $db = getDatabaseConnection();
    $pdo = $db; // For files expecting $pdo variable
} catch (Exception $e) {
    // Handle initialization error
    if (strpos($_SERVER['REQUEST_URI'], '/api/') !== false) {
        // Already handled in getDatabaseConnection for API calls
        exit;
    }
}
