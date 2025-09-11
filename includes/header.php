<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="logo">
                <a href="<?php echo isAdmin() ? '../index.php' : 'index.php'; ?>">Personal Blog</a>
            </div>
            
            <nav class="main-nav">
                <ul>
                    <li><a href="<?php echo isAdmin() ? '../index.php' : 'index.php'; ?>">Home</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li><a href="<?php echo isAdmin() ? '../create-post.php' : 'create-post.php'; ?>">Write</a></li>
                        <li><a href="<?php echo isAdmin() ? '../profile.php?id=' . getUserId() : 'profile.php?id=' . getUserId(); ?>">Profile</a></li>
                        <?php if(isAdmin()): ?>
                            <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'index.php' : 'admin/index.php'; ?>">Admin</a></li>
                        <?php endif; ?>
                        <li><a href="<?php echo isAdmin() ? '../auth/logout.php' : 'auth/logout.php'; ?>">Logout</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Register</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <?php if(isLoggedIn()): ?>
                <div class="user-info">
                    <span>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                    <?php if($_SESSION['status'] === 'limited'): ?>
                        <span class="status-badge status-limited">Limited</span>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</header>