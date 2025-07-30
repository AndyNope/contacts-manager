<?php
/**
 * Database Setup Script for EasyContact
 * Run this script to create all required database tables
 */

echo "Setting up EasyContact database...\n";

try {
    // Database connection
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "Database connection successful!\n";
    
    // Read and execute SQL schema
    $sql = file_get_contents('database_schema.sql');
    if ($sql === false) {
        throw new Exception('Could not read database_schema.sql file');
    }
    
    // Split SQL into individual statements
    $statements = array_filter(array_map('trim', explode(';', $sql)));
    
    foreach ($statements as $statement) {
        if (!empty($statement) && !preg_match('/^--/', $statement)) {
            try {
                $pdo->exec($statement);
                echo "✓ Executed: " . substr($statement, 0, 50) . "...\n";
            } catch (PDOException $e) {
                echo "⚠ Warning: " . $e->getMessage() . "\n";
            }
        }
    }
    
    echo "\n🎉 Database setup completed successfully!\n";
    echo "You can now test the registration system.\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
