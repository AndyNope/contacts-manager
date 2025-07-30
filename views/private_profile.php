<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?> - Private Profile</title>
    <meta name="description" content="Private professional profile for <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>">
    
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: {
                            50: '#f5f3ff',
                            100: '#ede9fe',
                            200: '#ddd6fe',
                            300: '#c4b5fd',
                            400: '#a78bfa',
                            500: '#8b5cf6',
                            600: '#7c3aed',
                            700: '#6d28d9',
                            800: '#5b21b6',
                            900: '#4c1d95',
                        }
                    }
                }
            }
        }
    </script>
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Open Graph / Social Media Meta Tags -->
    <meta property="og:type" content="profile">
    <meta property="og:title" content="<?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?> - Private Profile">
    <meta property="og:description" content="Connect with <?= htmlspecialchars($contact['first_name']) ?> on their private professional profile">
    <?php if (!empty($contact['photo'])): ?>
    <meta property="og:image" content="<?= htmlspecialchars($contact['photo']) ?>">
    <?php endif; ?>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    
    <style>
        .gradient-bg {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        }
        
        .card-shadow {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
        
        .profile-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        @media (prefers-color-scheme: dark) {
            .profile-card {
                background: rgba(31, 41, 55, 0.95);
            }
        }
    </style>
</head>
<body class="min-h-screen gradient-bg">
    <div class="container mx-auto px-4 py-8">
        <!-- Header -->
        <div class="text-center mb-8">
            <h1 class="text-white text-2xl font-bold">
                <i class="fas fa-user-shield mr-2"></i>
                Private Profile
            </h1>
            <p class="text-white/80 mt-2">Secure professional contact information</p>
        </div>
        
        <!-- Profile Card -->
        <div class="max-w-2xl mx-auto">
            <div class="profile-card rounded-2xl p-8 card-shadow">
                <!-- Profile Header -->
                <div class="text-center mb-8">
                    <?php if (!empty($contact['photo'])): ?>
                        <div class="w-32 h-32 mx-auto mb-4">
                            <img src="<?= htmlspecialchars($contact['photo']) ?>" 
                                 alt="<?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>"
                                 class="w-full h-full object-cover rounded-full border-4 border-primary-500 shadow-lg">
                        </div>
                    <?php else: ?>
                        <div class="w-32 h-32 mx-auto mb-4 bg-primary-500 rounded-full flex items-center justify-center border-4 border-primary-600 shadow-lg">
                            <span class="text-4xl text-white font-bold">
                                <?= strtoupper(substr($contact['first_name'], 0, 1) . substr($contact['last_name'], 0, 1)) ?>
                            </span>
                        </div>
                    <?php endif; ?>
                    
                    <h1 class="text-3xl font-bold text-gray-800 dark:text-white mb-2">
                        <?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?>
                    </h1>
                    
                    <?php if (!empty($contact['position'])): ?>
                        <p class="text-xl text-primary-600 dark:text-primary-400 mb-4">
                            <?= htmlspecialchars($contact['position']) ?>
                        </p>
                    <?php endif; ?>
                    
                    <?php if (!empty($contact['company'])): ?>
                        <p class="text-lg text-gray-600 dark:text-gray-300">
                            <?= htmlspecialchars($contact['company']) ?>
                        </p>
                    <?php endif; ?>
                </div>
                
                <!-- Contact Information -->
                <div class="space-y-4">
                    <?php if (!empty($contact['email'])): ?>
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <i class="fas fa-envelope text-primary-500 text-xl w-8"></i>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Email</p>
                                <a href="mailto:<?= htmlspecialchars($contact['email']) ?>" 
                                   class="text-lg text-gray-800 dark:text-white hover:text-primary-600 transition-colors">
                                    <?= htmlspecialchars($contact['email']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($contact['phone'])): ?>
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <i class="fas fa-phone text-primary-500 text-xl w-8"></i>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Phone</p>
                                <a href="tel:<?= htmlspecialchars($contact['phone']) ?>" 
                                   class="text-lg text-gray-800 dark:text-white hover:text-primary-600 transition-colors">
                                    <?= htmlspecialchars($contact['phone']) ?>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($contact['website'])): ?>
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <i class="fas fa-globe text-primary-500 text-xl w-8"></i>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Website</p>
                                <a href="<?= htmlspecialchars($contact['website']) ?>" 
                                   target="_blank" rel="noopener noreferrer"
                                   class="text-lg text-gray-800 dark:text-white hover:text-primary-600 transition-colors">
                                    <?= htmlspecialchars($contact['website']) ?>
                                    <i class="fas fa-external-link-alt ml-1 text-sm"></i>
                                </a>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (!empty($contact['address'])): ?>
                        <div class="flex items-center p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <i class="fas fa-map-marker-alt text-primary-500 text-xl w-8"></i>
                            <div class="ml-4">
                                <p class="text-sm text-gray-500 dark:text-gray-400">Address</p>
                                <p class="text-lg text-gray-800 dark:text-white">
                                    <?= nl2br(htmlspecialchars($contact['address'])) ?>
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
                
                <!-- Social Links -->
                <?php 
                $socialLinks = [
                    'linkedin' => ['icon' => 'fab fa-linkedin', 'color' => 'text-blue-600', 'name' => 'LinkedIn'],
                    'twitter' => ['icon' => 'fab fa-twitter', 'color' => 'text-blue-400', 'name' => 'Twitter'],
                    'facebook' => ['icon' => 'fab fa-facebook', 'color' => 'text-blue-700', 'name' => 'Facebook'],
                    'instagram' => ['icon' => 'fab fa-instagram', 'color' => 'text-pink-500', 'name' => 'Instagram']
                ];
                
                $hasSocialLinks = false;
                foreach ($socialLinks as $key => $social) {
                    if (!empty($contact[$key])) {
                        $hasSocialLinks = true;
                        break;
                    }
                }
                ?>
                
                <?php if ($hasSocialLinks): ?>
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">Connect on Social</h3>
                        <div class="flex flex-wrap gap-4">
                            <?php foreach ($socialLinks as $key => $social): ?>
                                <?php if (!empty($contact[$key])): ?>
                                    <a href="<?= htmlspecialchars($contact[$key]) ?>" 
                                       target="_blank" rel="noopener noreferrer"
                                       class="flex items-center px-4 py-2 bg-gray-50 dark:bg-gray-700 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
                                        <i class="<?= $social['icon'] ?> <?= $social['color'] ?> text-xl mr-2"></i>
                                        <span class="text-gray-800 dark:text-white"><?= $social['name'] ?></span>
                                    </a>
                                <?php endif; ?>
                            <?php endforeach; ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Bio/Notes -->
                <?php if (!empty($contact['notes'])): ?>
                    <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                        <h3 class="text-lg font-semibold text-gray-800 dark:text-white mb-4">About</h3>
                        <div class="text-gray-700 dark:text-gray-300 leading-relaxed">
                            <?= nl2br(htmlspecialchars($contact['notes'])) ?>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- Action Buttons -->
                <div class="mt-8 pt-6 border-t border-gray-200 dark:border-gray-600">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <a href="/api/vcard?contact=<?= $contact['id'] ?>" 
                           class="flex items-center justify-center px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition-colors">
                            <i class="fas fa-download mr-2"></i>
                            Save Contact
                        </a>
                        
                        <button onclick="shareProfile()" 
                                class="flex items-center justify-center px-6 py-3 bg-gray-600 text-white rounded-lg hover:bg-gray-700 transition-colors">
                            <i class="fas fa-share mr-2"></i>
                            Share Profile
                        </button>
                    </div>
                </div>
                
                <!-- Privacy Notice -->
                <div class="mt-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-shield-alt text-blue-500 mt-1 mr-3"></i>
                        <div>
                            <h4 class="font-semibold text-blue-800 dark:text-blue-200">Private Profile</h4>
                            <p class="text-sm text-blue-700 dark:text-blue-300 mt-1">
                                This is a private profile. Information is shared securely and only accessible via this link.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Footer -->
        <div class="text-center mt-8">
            <p class="text-white/60 text-sm">
                Powered by <a href="/" class="text-white hover:text-white/80 transition-colors">EasyContact</a>
            </p>
        </div>
    </div>
    
    <script>
        function shareProfile() {
            if (navigator.share) {
                navigator.share({
                    title: '<?= htmlspecialchars($contact['first_name'] . ' ' . $contact['last_name']) ?> - Profile',
                    text: 'Check out <?= htmlspecialchars($contact['first_name']) ?>\'s professional profile',
                    url: window.location.href
                });
            } else {
                // Fallback: Copy to clipboard
                navigator.clipboard.writeText(window.location.href).then(() => {
                    alert('Profile link copied to clipboard!');
                });
            }
        }
        
        // Dark mode detection
        if (window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches) {
            document.documentElement.classList.add('dark');
        }
    </script>
</body>
</html>
