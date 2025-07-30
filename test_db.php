<?php
try {
    $pdo = new PDO('mysql:host=localhost;dbname=easycontact;charset=utf8mb4', 'easycontact', 'EzC0nt@ct2025!');
    echo "Database connection successful\n";
    
    // Check if tables exist
    $tables = ['companies', 'users', 'contacts'];
    foreach ($tables as $table) {
        $stmt = $pdo->query("SHOW TABLES LIKE '$table'");
        if ($stmt->rowCount() > 0) {
            echo "Table $table exists\n";
            // Show columns
            $stmt = $pdo->query("DESCRIBE $table");
            $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo "Columns for $table:\n";
            foreach ($columns as $column) {
                echo "  - {$column['Field']} ({$column['Type']})\n";
            }
            echo "\n";
        } else {
            echo "Table $table does NOT exist\n";
        }
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>
