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
            'gender' => $gender
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
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Profile</button>
                        <a href="profile.php?id=<?php echo getUserId(); ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>