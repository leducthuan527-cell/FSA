<footer class="site-footer">
    <div class="container">
        <div class="footer-content">
            <div class="footer-section">
                <h3>Personal Blog</h3>
                <p>A community-driven platform for sharing stories and experiences.</p>
            </div>
            
            <div class="footer-section">
                <h4>Quick Links</h4>
                <ul>
                    <li><a href="index.php">Home</a></li>
                    <?php if(isLoggedIn()): ?>
                        <li><a href="create-post.php">Write a Post</a></li>
                        <li><a href="profile.php?id=<?php echo getUserId(); ?>">My Profile</a></li>
                    <?php else: ?>
                        <li><a href="auth/login.php">Login</a></li>
                        <li><a href="auth/register.php">Join Us</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            
            <div class="footer-section">
                <h4>Community</h4>
                <ul>
                    <li><a href="#">Guidelines</a></li>
                    <li><a href="#">Privacy Policy</a></li>
                    <li><a href="#">Terms of Service</a></li>
                    <li><a href="#">Contact</a></li>
                </ul>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> Personal Blog. All rights reserved.</p>
        </div>
    </div>
</footer>