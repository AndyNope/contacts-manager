<?php
/**
 * EasyContact Database Configuration
 * Multi-tenant database connection
 */

try {
    // Database configuration for EasyContact
    $dsn = 'mysql:host=localhost;dbname=easycontact;charset=utf8mb4';
    $username = 'easycontact';
    $password = 'EzC0nt@ct2025!';
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];
    
    $db = new PDO($dsn, $username, $password, $options);
    
} catch (PDOException $e) {
    // For development, show detailed error
    if (defined('APP_ENV') && APP_ENV === 'development') {
        die("Database connection failed: " . $e->getMessage());
    } else {
        die("Database connection failed. Please try again later.");
    }
}
