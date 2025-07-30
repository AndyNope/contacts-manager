<?php
session_start();

// Database connection
try {
    $host = 'localhost';
    $user = 'kontaktverwaltung';
    $pass = 'Kontakt&Verwaltung';
    $dbname = 'kontaktverwaltung';
    
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    die(json_encode(['error' => 'Database connection failed']));
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    die(json_encode(['error' => 'Method not allowed']));
}

// Get form data
$companyName = trim($_POST['company_name'] ?? '');
$firstName = trim($_POST['first_name'] ?? '');
$lastName = trim($_POST['last_name'] ?? '');
$email = trim($_POST['email'] ?? '');
$password = $_POST['password'] ?? '';
$confirmPassword = $_POST['confirm_password'] ?? '';
$plan = $_POST['plan'] ?? 'free';
$agreeTerms = isset($_POST['agree_terms']);

// Validation
$errors = [];

if (empty($companyName)) {
    $errors[] = 'Company name is required';
}

if (empty($firstName)) {
    $errors[] = 'First name is required';
}

if (empty($lastName)) {
    $errors[] = 'Last name is required';
}

if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Valid email address is required';
}

if (empty($password) || strlen($password) < 8) {
    $errors[] = 'Password must be at least 8 characters long';
}

if ($password !== $confirmPassword) {
    $errors[] = 'Passwords do not match';
}

if (!$agreeTerms) {
    $errors[] = 'You must agree to the Terms of Service and Privacy Policy';
}

if (!in_array($plan, ['free', 'basic', 'premium'])) {
    $errors[] = 'Invalid plan selected';
}

// For free plan, allow private profiles
$isPrivateProfile = false;
if ($plan === 'free' && (empty($companyName) || $companyName === 'Private Profile')) {
    $companyName = $firstName . ' ' . $lastName . ' (Private)';
    $isPrivateProfile = true;
}

if (!empty($errors)) {
    header('Location: /register?error=validation&message=' . urlencode(implode(', ', $errors)));
    exit;
}

try {
    // Start transaction
    $pdo->beginTransaction();
    
    // Check if email already exists
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $pdo->rollBack();
        header('Location: /register?error=email_exists');
        exit;
    }
    
    // Create company slug from name
    if ($isPrivateProfile) {
        // For private profiles, use 'private' as company slug
        $companySlug = 'private';
        
        // Check if 'private' company exists, if not create it
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = 'private'");
        $stmt->execute();
        $privateCompany = $stmt->fetch();
        
        if (!$privateCompany) {
            // Create the private company
            $stmt = $pdo->prepare("
                INSERT INTO companies 
                (slug, name, subscription_plan, subscription_status, contact_limit, created_at, updated_at) 
                VALUES ('private', 'Private Profiles', 'free', 'active', -1, NOW(), NOW())
            ");
            $stmt->execute();
            $companyId = $pdo->lastInsertId();
        } else {
            $companyId = $privateCompany['id'];
        }
        
        // Create unique slug for the private profile
        $userSlug = strtolower(trim($firstName . '-' . $lastName));
        $userSlug = preg_replace('/[^a-z0-9\s-]/', '', $userSlug);
        $userSlug = preg_replace('/[\s-]+/', '-', $userSlug);
        $userSlug = trim($userSlug, '-');
        
        // Check if private profile with this slug already exists
        $stmt = $pdo->prepare("
            SELECT id FROM contacts 
            WHERE company_id = ? AND slug = ?
        ");
        $stmt->execute([$companyId, $userSlug]);
        if ($stmt->fetch()) {
            // Add number suffix if slug exists
            $counter = 1;
            $originalSlug = $userSlug;
            do {
                $userSlug = $originalSlug . '-' . $counter;
                $stmt->execute([$companyId, $userSlug]);
                $counter++;
            } while ($stmt->fetch());
        }
    } else {
        // Regular company profile
        $companySlug = strtolower(trim($companyName));
        $companySlug = preg_replace('/[^a-z0-9\s-]/', '', $companySlug);
        $companySlug = preg_replace('/[\s-]+/', '-', $companySlug);
        $companySlug = trim($companySlug, '-');
        
        // Check if company slug already exists
        $stmt = $pdo->prepare("SELECT id FROM companies WHERE slug = ?");
        $stmt->execute([$companySlug]);
        if ($stmt->fetch()) {
            // Add number suffix if slug exists
            $counter = 1;
            $originalSlug = $companySlug;
            do {
                $companySlug = $originalSlug . '-' . $counter;
                $stmt->execute([$companySlug]);
                $counter++;
            } while ($stmt->fetch());
        }
        
        // Set plan limits
        $planLimits = [
            'free' => ['contact_limit' => 1, 'subscription_status' => 'active'],
            'basic' => ['contact_limit' => 50, 'subscription_status' => 'pending'],
            'premium' => ['contact_limit' => -1, 'subscription_status' => 'pending'] // -1 = unlimited
        ];
        
        $planConfig = $planLimits[$plan];
        
        // Create company
        $stmt = $pdo->prepare("
            INSERT INTO companies 
            (slug, name, subscription_plan, subscription_status, contact_limit, created_at, updated_at) 
            VALUES (?, ?, ?, ?, ?, NOW(), NOW())
        ");
        $stmt->execute([
            $companySlug,
            $companyName,
            $plan,
            $planConfig['subscription_status'],
            $planConfig['contact_limit']
        ]);
        $companyId = $pdo->lastInsertId();
        
        $userSlug = null; // For company profiles, users don't have individual slugs
    }
    
    // Create user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("
        INSERT INTO users 
        (company_id, first_name, last_name, email, password, role, is_private_profile, profile_slug, created_at, updated_at) 
        VALUES (?, ?, ?, ?, ?, 'admin', ?, ?, NOW(), NOW())
    ");
    $stmt->execute([
        $companyId, 
        $firstName, 
        $lastName, 
        $email, 
        $hashedPassword, 
        $isPrivateProfile ? 1 : 0,
        $userSlug
    ]);
    $userId = $pdo->lastInsertId();
    
    // Create initial contact profile for the user
    if ($isPrivateProfile) {
        // For private profiles, create contact with user slug
        $stmt = $pdo->prepare("
            INSERT INTO contacts 
            (company_id, first_name, last_name, email, position, slug, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'Private Profile', ?, ?, NOW(), NOW())
        ");
        $stmt->execute([$companyId, $firstName, $lastName, $email, $userSlug, $userId]);
    } else {
        // For company profiles, create regular contact
        $stmt = $pdo->prepare("
            INSERT INTO contacts 
            (company_id, first_name, last_name, email, position, created_by, created_at, updated_at) 
            VALUES (?, ?, ?, ?, 'Founder', ?, NOW(), NOW())
        ");
        $stmt->execute([$companyId, $firstName, $lastName, $email, $userId]);
    }
    
    // Commit transaction
    $pdo->commit();
    
    // Set session variables
    $_SESSION['user_id'] = $userId;
    $_SESSION['company_id'] = $companyId;
    $_SESSION['company_slug'] = $companySlug;
    $_SESSION['user_name'] = $firstName . ' ' . $lastName;
    $_SESSION['user_email'] = $email;
    $_SESSION['user_role'] = 'admin';
    $_SESSION['subscription_plan'] = $plan;
    $_SESSION['is_private_profile'] = $isPrivateProfile;
    $_SESSION['user_slug'] = $userSlug;
    
    // Redirect based on plan and profile type
    if ($plan === 'free') {
        // Free plan - redirect to appropriate dashboard
        if ($isPrivateProfile) {
            header('Location: /private/profile/' . $userSlug);
        } else {
            header('Location: /' . $companySlug);
        }
        exit;
    } else {
        // Paid plan - redirect to subscription payment
        header('Location: /subscribe.php?plan=' . $plan . '&company=' . $companySlug);
        exit;
    }
    
} catch (Exception $e) {
    $pdo->rollBack();
    error_log('Registration error: ' . $e->getMessage());
    header('Location: /register?error=registration_failed&message=' . urlencode('Registration failed. Please try again.'));
    exit;
}
?>
