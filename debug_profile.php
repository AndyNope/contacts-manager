<?php
// Debug script to check private profile creation
// URL: /debug_profile.php?test_slug=your-slug-here

// Use your hosting database credentials here
$host = 'localhost'; // Your hosting DB host
$user = 'easycontact'; // Your hosting DB username 
$pass = 'EzC0nt@ct2025!'; // Your hosting DB password
$dbname = 'easycontact'; // Your hosting DB name

// Database connection
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
    
    echo "<!DOCTYPE html><html><head><title>Debug Private Profiles</title></head><body>";
    echo "<h2>Debug: Private Profile Check</h2>\n";
    echo "<p><strong>Test URL format:</strong> /debug_profile.php?test_slug=your-slug-here</p>\n";
    
    // Get private company
    $stmt = $pdo->prepare("SELECT * FROM companies WHERE slug = 'private'");
    $stmt->execute();
    $privateCompany = $stmt->fetch();
    
    echo "<h3>1. Private Company Status:</h3>\n";
    if ($privateCompany) {
        echo "<pre>" . print_r($privateCompany, true) . "</pre>\n";
    } else {
        echo "<p style='color: red;'>❌ No private company found! This needs to be created.</p>\n";
    }
    
    // Get recent users and contacts
    if ($privateCompany) {
        // Recent users with private profiles
        $stmt = $pdo->prepare("
            SELECT id, first_name, last_name, email, profile_slug, is_private_profile, created_at 
            FROM users 
            WHERE company_id = ? 
            ORDER BY created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$privateCompany['id']]);
        $users = $stmt->fetchAll();
        
        echo "<h3>2. Recent Private Users:</h3>\n";
        if ($users) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Profile Slug</th><th>Created</th></tr>";
            foreach ($users as $user) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($user['id']) . "</td>";
                echo "<td>" . htmlspecialchars($user['first_name'] . ' ' . $user['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                echo "<td>" . htmlspecialchars($user['profile_slug']) . "</td>";
                echo "<td>" . htmlspecialchars($user['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No users found in private company.</p>\n";
        }
        
        // Recent contacts in private company
        $stmt = $pdo->prepare("
            SELECT c.*, u.profile_slug as user_profile_slug, u.first_name as user_first_name, u.last_name as user_last_name
            FROM contacts c 
            LEFT JOIN users u ON c.created_by = u.id 
            WHERE c.company_id = ? 
            ORDER BY c.created_at DESC 
            LIMIT 10
        ");
        $stmt->execute([$privateCompany['id']]);
        $contacts = $stmt->fetchAll();
        
        echo "<h3>3. Recent Private Contacts:</h3>\n";
        if ($contacts) {
            echo "<table border='1' style='border-collapse: collapse;'>";
            echo "<tr><th>ID</th><th>Name</th><th>Contact Slug</th><th>User Profile Slug</th><th>Is Public</th><th>UUID</th><th>Created</th></tr>";
            foreach ($contacts as $contact) {
                echo "<tr>";
                echo "<td>" . htmlspecialchars($contact['id']) . "</td>";
                echo "<td>" . htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) . "</td>";
                echo "<td>" . htmlspecialchars($contact['slug']) . "</td>";
                echo "<td>" . htmlspecialchars($contact['user_profile_slug']) . "</td>";
                echo "<td>" . ($contact['is_public'] ? '✅ Yes' : '❌ No') . "</td>";
                echo "<td>" . htmlspecialchars(substr($contact['uuid'], 0, 8) . '...') . "</td>";
                echo "<td>" . htmlspecialchars($contact['created_at']) . "</td>";
                echo "</tr>";
            }
            echo "</table>";
        } else {
            echo "<p>No contacts found in private company.</p>\n";
        }
        
        // Test specific slug if provided
        $testSlug = $_GET['test_slug'] ?? null;
        if ($testSlug) {
            echo "<h3>4. Testing Slug: '" . htmlspecialchars($testSlug) . "'</h3>\n";
            
            // Method 1: Direct contact slug lookup
            $stmt = $pdo->prepare("
                SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
                FROM contacts c 
                LEFT JOIN users u ON c.created_by = u.id 
                WHERE c.company_id = ? AND c.slug = ? AND c.is_public = 1
            ");
            $stmt->execute([$privateCompany['id'], $testSlug]);
            $contact1 = $stmt->fetch();
            
            echo "<p><strong>Method 1 - Contact Slug Lookup:</strong></p>\n";
            if ($contact1) {
                echo "<p style='color: green;'>✅ Found by contact slug!</p>";
                echo "<pre>" . print_r($contact1, true) . "</pre>\n";
            } else {
                echo "<p style='color: red;'>❌ Not found by contact slug</p>\n";
            }
            
            // Method 2: User profile_slug lookup
            $stmt = $pdo->prepare("
                SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
                FROM contacts c 
                INNER JOIN users u ON c.created_by = u.id 
                WHERE c.company_id = ? AND u.profile_slug = ? AND c.is_public = 1
            ");
            $stmt->execute([$privateCompany['id'], $testSlug]);
            $contact2 = $stmt->fetch();
            
            echo "<p><strong>Method 2 - User Profile Slug Lookup:</strong></p>\n";
            if ($contact2) {
                echo "<p style='color: green;'>✅ Found by user profile slug!</p>";
                echo "<pre>" . print_r($contact2, true) . "</pre>\n";
            } else {
                echo "<p style='color: red;'>❌ Not found by user profile slug</p>\n";
            }
            
            // Method 3: Broader search
            $stmt = $pdo->prepare("
                SELECT c.* FROM contacts c 
                WHERE c.company_id = ? AND c.slug = ? AND c.is_public = 1
            ");
            $stmt->execute([$privateCompany['id'], $testSlug]);
            $contact3 = $stmt->fetch();
            
            echo "<p><strong>Method 3 - Simple Contact Lookup:</strong></p>\n";
            if ($contact3) {
                echo "<p style='color: green;'>✅ Found by simple lookup!</p>";
                echo "<pre>" . print_r($contact3, true) . "</pre>\n";
            } else {
                echo "<p style='color: red;'>❌ Not found by simple lookup</p>\n";
            }
            
            // Check if there are any contacts with similar slugs
            $stmt = $pdo->prepare("
                SELECT slug FROM contacts 
                WHERE company_id = ? AND slug LIKE ?
            ");
            $stmt->execute([$privateCompany['id'], '%' . $testSlug . '%']);
            $similar = $stmt->fetchAll();
            
            if ($similar) {
                echo "<p><strong>Similar slugs found:</strong></p>";
                echo "<ul>";
                foreach ($similar as $s) {
                    echo "<li>" . htmlspecialchars($s['slug']) . "</li>";
                }
                echo "</ul>";
            }
        } else {
            echo "<h3>4. Test a Specific Slug</h3>\n";
            echo "<p>Add ?test_slug=your-slug-here to the URL to test a specific profile slug.</p>\n";
        }
    }
    
    echo "</body></html>";
    
} catch (Exception $e) {
    echo "<h2 style='color: red;'>Database Error:</h2>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
?>