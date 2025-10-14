<?php
/**
 * Private Profile URL Testing Page
 * Test the new /private/{id} URL system
 */

// Database connection
try {
    $host = 'localhost';
    $user = 'easycontact';
    $pass = 'EzC0nt@ct2025!';
    $dbname = 'easycontact';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die('Database connection failed: ' . $e->getMessage());
}

echo "<h1>🔒 Private Profile URL System Test</h1>";

// Get private company
$stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = 'private'");
$stmt->execute();
$privateCompany = $stmt->fetch();

if (!$privateCompany) {
    echo "<p>❌ No private company found. Creating one...</p>";
    
    // Create private company
    $stmt = $pdo->prepare("
        INSERT INTO companies (name, slug, subscription_status, subscription_tier, created_at) 
        VALUES ('Private Profiles', 'private', 'active', 'free', NOW())
    ");
    $stmt->execute();
    $privateCompanyId = $pdo->lastInsertId();
    echo "<p>✅ Created private company with ID: $privateCompanyId</p>";
} else {
    $privateCompanyId = $privateCompany['id'];
    echo "<p>✅ Private company found with ID: $privateCompanyId</p>";
}

// Get all private profiles
$stmt = $pdo->prepare("
    SELECT c.*, u.first_name as creator_first_name, u.last_name as creator_last_name 
    FROM contacts c 
    LEFT JOIN users u ON c.created_by = u.id 
    WHERE c.company_id = ?
    ORDER BY c.id
");
$stmt->execute([$privateCompanyId]);
$privateProfiles = $stmt->fetchAll();

echo "<h2>📋 Current Private Profiles</h2>";

if (empty($privateProfiles)) {
    echo "<p>No private profiles found.</p>";
} else {
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr style='background-color: #f0f0f0;'>";
    echo "<th>ID</th><th>Name</th><th>Email</th><th>Slug</th><th>New URL</th><th>Legacy URL</th><th>Actions</th>";
    echo "</tr>";
    
    foreach ($privateProfiles as $profile) {
        $name = trim($profile['first_name'] . ' ' . $profile['last_name']) ?: 'Unknown';
        $newUrl = '/private/' . $profile['id'];
        $legacyUrl = '/private/profile/' . ($profile['slug'] ?: $profile['profile_slug'] ?: 'unknown');
        
        echo "<tr>";
        echo "<td>{$profile['id']}</td>";
        echo "<td>" . htmlspecialchars($name) . "</td>";
        echo "<td>" . htmlspecialchars($profile['email'] ?: 'N/A') . "</td>";
        echo "<td>" . htmlspecialchars($profile['slug'] ?: $profile['profile_slug'] ?: 'N/A') . "</td>";
        echo "<td><a href='$newUrl' target='_blank'>$newUrl</a></td>";
        echo "<td><a href='$legacyUrl' target='_blank'>$legacyUrl</a></td>";
        echo "<td>";
        echo "<a href='/api/vcard?contact={$profile['id']}' target='_blank'>vCard</a> | ";
        echo "<a href='/api/business-card?contact={$profile['id']}' target='_blank'>Card</a>";
        echo "</td>";
        echo "</tr>";
    }
    
    echo "</table>";
}

// Test URL patterns
echo "<h2>🧪 URL Pattern Tests</h2>";

$testUrls = [
    '/private/3' => 'New format - By ID',
    '/private/andy-bui' => 'New format - By slug',
    '/private/profile/andy-bui' => 'Legacy format (should redirect)'
];

foreach ($testUrls as $url => $description) {
    echo "<p><strong>$description:</strong> <a href='$url' target='_blank'>$url</a></p>";
}

// API endpoints
echo "<h2>🔌 API Endpoints</h2>";
echo "<ul>";
echo "<li><a href='/api/vcard?contact=3' target='_blank'>/api/vcard?contact=3</a> - Download vCard</li>";
echo "<li><a href='/api/business-card?contact=3' target='_blank'>/api/business-card?contact=3</a> - Generate business card</li>";
echo "</ul>";

// Show current user from login
session_start();
if (isset($_SESSION['user_id'])) {
    echo "<h2>👤 Current Session</h2>";
    echo "<p>User ID: " . $_SESSION['user_id'] . "</p>";
    echo "<p>Company: " . ($_SESSION['company_name'] ?? 'N/A') . "</p>";
    echo "<p>Company Slug: " . ($_SESSION['company_slug'] ?? 'N/A') . "</p>";
} else {
    echo "<h2>👤 No User Logged In</h2>";
    echo "<p><a href='/login'>Login here</a></p>";
}

echo "<hr>";
echo "<p><a href='/'>← Back to Homepage</a></p>";
?>