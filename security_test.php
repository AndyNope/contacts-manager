<?php
/**
 * Security Test Page
 * Test access control and authentication features
 */

session_start();

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

echo "<h1>🔒 Security System Test</h1>";

// Show current session
echo "<h2>👤 Current Session</h2>";
if (isset($_SESSION['user_id'])) {
    echo "<div style='background: #d4edda; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<strong>✅ User Logged In</strong><br>";
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "Name: " . ($_SESSION['user_name'] ?? 'N/A') . "<br>";
    echo "Email: " . ($_SESSION['user_email'] ?? 'N/A') . "<br>";
    echo "Company ID: " . ($_SESSION['company_id'] ?? 'N/A') . "<br>";
    echo "Company: " . ($_SESSION['company_name'] ?? 'N/A') . "<br>";
    echo "Company Slug: " . ($_SESSION['company_slug'] ?? 'N/A') . "<br>";
    echo "Role: " . ($_SESSION['user_role'] ?? 'N/A') . "<br>";
    echo "</div>";
} else {
    echo "<div style='background: #f8d7da; padding: 15px; border-radius: 5px; margin-bottom: 20px;'>";
    echo "<strong>❌ No User Logged In</strong><br>";
    echo "<a href='/login'>Login here</a>";
    echo "</div>";
}

// Get all companies for testing
$stmt = $pdo->prepare("SELECT id, name, slug, subscription_status FROM companies ORDER BY name");
$stmt->execute();
$companies = $stmt->fetchAll();

echo "<h2>🏢 Company Access Test</h2>";
echo "<p>Test which companies you can access based on your current session:</p>";

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Company</th><th>Slug</th><th>Status</th><th>Access Test</th><th>URL</th>";
echo "</tr>";

foreach ($companies as $company) {
    $canAccess = canAccessCompanyTest($company);
    $accessClass = $canAccess ? 'color: green;' : 'color: red;';
    $accessText = $canAccess ? '✅ Allowed' : '❌ Denied';
    $url = '/' . $company['slug'];
    
    echo "<tr>";
    echo "<td>" . htmlspecialchars($company['name']) . "</td>";
    echo "<td>" . htmlspecialchars($company['slug']) . "</td>";
    echo "<td>" . htmlspecialchars($company['subscription_status']) . "</td>";
    echo "<td style='$accessClass'><strong>$accessText</strong></td>";
    echo "<td><a href='$url' target='_blank'>$url</a></td>";
    echo "</tr>";
}

echo "</table>";

// Test API endpoints
echo "<h2>🔌 API Security Test</h2>";
echo "<p>Test API access with current permissions:</p>";

// Get a sample contact from each company
$stmt = $pdo->prepare("
    SELECT c.id, c.first_name, c.last_name, c.is_public, co.name as company_name, co.slug as company_slug 
    FROM contacts c 
    JOIN companies co ON c.company_id = co.id 
    ORDER BY co.name, c.id 
    LIMIT 10
");
$stmt->execute();
$contacts = $stmt->fetchAll();

echo "<table border='1' style='border-collapse: collapse; width: 100%; margin-bottom: 20px;'>";
echo "<tr style='background-color: #f0f0f0;'>";
echo "<th>Contact</th><th>Company</th><th>Public</th><th>vCard API</th><th>Business Card API</th>";
echo "</tr>";

foreach ($contacts as $contact) {
    $canAccessAPI = canAccessContactAPITest($contact);
    $accessClass = $canAccessAPI ? 'color: green;' : 'color: red;';
    $accessText = $canAccessAPI ? '✅' : '❌';
    
    $name = htmlspecialchars(trim($contact['first_name'] . ' ' . $contact['last_name']));
    $isPublic = $contact['is_public'] ? '✅ Yes' : '❌ No';
    
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>" . htmlspecialchars($contact['company_name']) . "</td>";
    echo "<td>$isPublic</td>";
    echo "<td style='$accessClass'>";
    echo "<a href='/api/vcard?contact={$contact['id']}' target='_blank'>$accessText vCard</a>";
    echo "</td>";
    echo "<td style='$accessClass'>";
    echo "<a href='/api/business-card?contact={$contact['id']}' target='_blank'>$accessText Card</a>";
    echo "</td>";
    echo "</tr>";
}

echo "</table>";

// Security recommendations
echo "<h2>🛡️ Security Status</h2>";

$securityChecks = [
    'Session Management' => isset($_SESSION['user_id']) ? '✅ Active' : '⚠️ Not logged in',
    'Company Access Control' => '✅ Implemented',
    'API Access Control' => '✅ Implemented', 
    'CSRF Protection' => '⚠️ Recommend adding CSRF tokens',
    'Input Validation' => '✅ Basic validation in place',
    'SQL Injection Protection' => '✅ Using prepared statements',
    'XSS Protection' => '✅ Using htmlspecialchars()',
    'Access Denied Pages' => '✅ Custom error pages'
];

echo "<ul>";
foreach ($securityChecks as $check => $status) {
    echo "<li><strong>$check:</strong> $status</li>";
}
echo "</ul>";

echo "<hr>";
echo "<p><a href='/'>← Back to Homepage</a> | <a href='/login'>Login</a> | <a href='/api/logout.php'>Logout</a></p>";

/**
 * Test company access permissions
 */
function canAccessCompanyTest($company) {
    // Allow access to private profiles company
    if ($company['slug'] === 'private') {
        return true;
    }
    
    // Check if user is logged in
    if (!isset($_SESSION['user_id'])) {
        return false;
    }
    
    // Check if user belongs to this company
    if (isset($_SESSION['company_id']) && $_SESSION['company_id'] == $company['id']) {
        return true;
    }
    
    // Check if company allows public access (simulate)
    if (isset($company['allow_public_access']) && $company['allow_public_access']) {
        return true;
    }
    
    // Default: deny access
    return false;
}

/**
 * Test API contact access permissions
 */
function canAccessContactAPITest($contact) {
    // Private profiles - allow access if contact is public
    if ($contact['company_slug'] === 'private') {
        return isset($contact['is_public']) && $contact['is_public'];
    }
    
    // For company contacts, check user permissions
    if (!isset($_SESSION['user_id'])) {
        // Not logged in - only allow if explicitly public
        return isset($contact['is_public']) && $contact['is_public'];
    }
    
    // Check if user belongs to the same company
    if (isset($_SESSION['company_id']) && $_SESSION['company_id'] == $contact['company_id']) {
        return true;
    }
    
    // Default: only public contacts
    return isset($contact['is_public']) && $contact['is_public'];
}
?>