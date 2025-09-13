<?php
require_once '../config/init.php';
require_once '../classes/User.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(isLoggedIn()) {
    redirect('../index.php');
}

$error = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = sanitizeInput($_POST['email']);
    $password = $_POST['password'];
    
    if(empty($email) || empty($password)) {
        $error = 'Please fill in all fields';
    } else {
        $user = new User($db);
        $login_result = $user->login($email, $password);
        
        if($login_result) {
            if($login_result['status'] === 'banned') {
                $error = 'Your account has been banned';
            } else {
                $_SESSION['user_id'] = $login_result['id'];
                $_SESSION['username'] = $login_result['username'];
                $_SESSION['role'] = $login_result['role'];
                $_SESSION['status'] = $login_result['status'];
                
                redirect('../index.php');
            }
        } else {
            $error = 'Invalid email or password';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Personal Blog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/hero.css">
</head>
<body>
    <div class="auth-container">
        <div class="auth-card">
            <h1>Welcome Back</h1>
            <p>Sign in to your account</p>
            
            <?php if($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" class="auth-form">
                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                </div>
                
                <div class="form-group">
                    <label for="password">Password</label>
                    <input type="password" id="password" name="password" required>
                </div>
                
                <button type="submit" class="btn btn-primary btn-full">Sign In</button>
            </form>
            
            <div class="auth-links">
                <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                <p><a href="../index.php">← Back to Home</a></p>
            </div>
        </div>
    </div>
</body>
</html>