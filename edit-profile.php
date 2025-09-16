<?php
require_once 'config/init.php';
require_once 'classes/User.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isLoggedIn()) {
    redirect('auth/login.php');
}

$user = new User($db);
$profile_data = $user->getUserById(getUserId());

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitizeInput($_POST['username']);
    $email = sanitizeInput($_POST['email']);
    $gender = sanitizeInput($_POST['gender']);
    
    if(empty($username) || empty($email)) {
        $error = 'Username and email are required';
    } else {
        $update_data = [
            'username' => $username,
            'email' => $email,
            'gender' => $gender,
            'description' => sanitizeInput($_POST['description'])
        ];
        
        // Handle avatar upload
        if(isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['avatar']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['avatar']['name'], PATHINFO_EXTENSION);
                $new_filename = 'avatar_' . getUserId() . '_' . time() . '.' . $file_extension;
                $upload_path = 'assets/images/avatars/' . $new_filename;
                
                if(move_uploaded_file($_FILES['avatar']['tmp_name'], $upload_path)) {
                    $update_data['avatar'] = $new_filename;
                }
            }
        }
        
        // Handle banner upload
        if(isset($_FILES['banner']) && $_FILES['banner']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
            $file_type = $_FILES['banner']['type'];
            
            if(in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['banner']['name'], PATHINFO_EXTENSION);
                $new_filename = 'banner_' . getUserId() . '_' . time() . '.' . $file_extension;
                $upload_path = 'assets/images/banners/' . $new_filename;
                
                if(!is_dir('assets/images/banners')) {
                    mkdir('assets/images/banners', 0755, true);
                }
                
                if(move_uploaded_file($_FILES['banner']['tmp_name'], $upload_path)) {
                    $update_data['banner'] = $new_filename;
                }
            }
        }
        
        if($user->updateProfile(getUserId(), $update_data)) {
            $_SESSION['username'] = $username;
            $success = 'Profile updated successfully!';
            $profile_data = $user->getUserById(getUserId());
        } else {
            $error = 'Failed to update profile. Username or email may already exist.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile - Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hero.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="create-post-container">
                <h1>Edit Profile</h1>
                
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="post-form">
                    <div class="form-group">
                        <label>Current Banner</label>
                        <div style="margin-bottom: 1rem;">
                            <div style="width: 100%; height: 120px; background: <?php echo !empty($profile_data['banner']) && $profile_data['banner'] !== 'default-banner.jpg' ? 'url(assets/images/banners/' . htmlspecialchars($profile_data['banner']) . ')' : 'linear-gradient(135deg, #667eea 0%, #764ba2 100%)'; ?>; background-size: cover; background-position: center; border-radius: 8px; border: 2px solid rgba(255, 255, 255, 0.1);" id="banner-preview"></div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="banner">Change Banner</label>
                        <input type="file" id="banner" name="banner" accept="image/*" 
                               onchange="previewBanner(this, 'banner-preview')">
                    </div>
                    
                    <div class="form-group">
                        <label>Current Avatar</label>
                        <div style="margin-bottom: 1rem;">
                            <img src="assets/images/avatars/<?php echo htmlspecialchars($profile_data['avatar']); ?>" 
                                 alt="Current Avatar" 
                                 id="avatar-preview"
                                 style="width: 100px; height: 100px; border-radius: 50%; object-fit: cover;">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="avatar">Change Avatar</label>
                        <input type="file" id="avatar" name="avatar" accept="image/*" 
                               onchange="previewImage(this, 'avatar-preview')">
                    </div>
                    
                    <div class="form-group">
                        <label for="username">Username</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo htmlspecialchars($profile_data['username']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="email">Email Address</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo htmlspecialchars($profile_data['email'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="gender">Gender</label>
                        <select id="gender" name="gender">
                            <option value="prefer_not_to_say" <?php echo $profile_data['gender'] === 'prefer_not_to_say' ? 'selected' : ''; ?>>Prefer not to say</option>
                            <option value="male" <?php echo $profile_data['gender'] === 'male' ? 'selected' : ''; ?>>Male</option>
                            <option value="female" <?php echo $profile_data['gender'] === 'female' ? 'selected' : ''; ?>>Female</option>
                            <option value="other" <?php echo $profile_data['gender'] === 'other' ? 'selected' : ''; ?>>Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="description">Description</label>
                        <div class="bbcode-toolbar">
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('description', 'b')" title="Bold">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                    <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('description', 'i')" title="Italic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="19" y1="4" x2="10" y2="4"></line>
                                    <line x1="14" y1="20" x2="5" y2="20"></line>
                                    <line x1="15" y1="4" x2="9" y2="20"></line>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('description', 'u')" title="Underline">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path>
                                    <line x1="4" y1="21" x2="20" y2="21"></line>
                                </svg>
                            </button>
                            <div class="toolbar-separator"></div>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('description', 'url')" title="Link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('description', 'color')" title="Color">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2l3 6 6 3-6 3-3 6-3-6-6-3 6-3z"></path>
                                </svg>
                            </button>
                        </div>
                        <textarea id="description" name="description" rows="4" maxlength="500" placeholder="Tell us about yourself..."><?php echo htmlspecialchars($profile_data['description']); ?></textarea>
                        <div class="char-counter">
                            <span id="description-count">0</span>/500 characters
                        </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="profile.php?id=<?php echo getUserId(); ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/bbcode.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Character counter for description
        document.getElementById('description').addEventListener('input', function() {
            document.getElementById('description-count').textContent = this.value.length;
        });
        
        // Initialize counter
        document.getElementById('description-count').textContent = document.getElementById('description').value.length;
        
        // Banner preview function
        function previewBanner(input, previewId) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById(previewId);
                    preview.style.backgroundImage = `url(${e.target.result})`;
                };
                reader.readAsDataURL(input.files[0]);
            }
        }
    </script>
</body>
</html>