<?php
require_once (strpos($_SERVER['PHP_SELF'], 'admin') !== false ? '../classes/Notification.php' : 'classes/Notification.php');
require_once (strpos($_SERVER['PHP_SELF'], 'admin') !== false ? '../classes/User.php' : 'classes/User.php');
require_once (strpos($_SERVER['PHP_SELF'], 'admin') !== false ? '../classes/User.php' : 'classes/User.php');

if(isLoggedIn()) {
    $notification = new Notification($db);
    $unread_count = $notification->getUnreadCount(getUserId());
    
    // Get user avatar
    $user = new User($db);
    $user_data = $user->getUserById(getUserId());
    $user_avatar = $user_data['avatar'] ?? 'default-avatar.png';
    
    // Get user avatar
    $user = new User($db);
    $user_data = $user->getUserById(getUserId());
    $user_avatar = $user_data['avatar'] ?? 'default-avatar.png';
}
?>

<header class="site-header">
    <div class="container">
        <div class="header-content">
            <div class="header-left">
                <div class="logo">
                    <a href="<?php echo isAdmin() ? '../index.php' : 'index.php'; ?>">Personal Blog</a>
                </div>
            </div>
            
            <div class="header-center">
                <nav class="main-nav">
                    <ul>
                        <li><a href="<?php echo isAdmin() ? '../index.php' : 'index.php'; ?>">Home</a></li>
                        <?php if(isLoggedIn()): ?>
                            <li><a href="<?php echo isAdmin() ? '../create-post.php' : 'create-post.php'; ?>">Write</a></li>
                            <?php if(isAdmin()): ?>
                                <li><a href="<?php echo strpos($_SERVER['PHP_SELF'], 'admin') !== false ? 'index.php' : 'admin/index.php'; ?>">Admin</a></li>
                            <?php endif; ?>
                        <?php endif; ?>
                    </ul>
                </nav>
            </div>
            
            <div class="header-right">
                <?php if(isLoggedIn()): ?>
                    <div class="header-actions">
                        <div class="notification-container">
                            <button class="notification-btn" onclick="toggleNotifications()">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"></path>
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0"></path>
                                </svg>
                                <?php if($unread_count > 0): ?>
                                    <span class="notification-badge"><?php echo $unread_count; ?></span>
                                <?php endif; ?>
                            </button>
                            <div class="notification-dropdown" id="notificationDropdown">
                                <div class="notification-header">
                                    <h4>Notifications</h4>
                                    <button onclick="markAllAsRead()" class="mark-read-btn">Mark all as read</button>
                                </div>
                                <div class="notification-list" id="notificationList">
                                    <!-- Notifications will be loaded here -->
                                </div>
                                <div class="notification-footer">
                                    <button onclick="loadMoreNotifications()" id="loadMoreNotifications" class="load-more-btn">Show More</button>
                                </div>
                            </div>
                        </div>
                        
                        <div class="user-menu">
                            <div class="user-avatar" onclick="window.location.href='<?php echo isAdmin() ? '../profile.php?id=' . getUserId() : 'profile.php?id=' . getUserId(); ?>'">
                                <img src="<?php echo isAdmin() ? '../assets/images/avatars/' : 'assets/images/avatars/'; ?><?php echo htmlspecialchars($user_avatar); ?>" alt="Avatar">
                            </div>
                            <?php if($_SESSION['status'] === 'limited'): ?>
                                <span class="status-badge status-limited">Limited</span>
                            <?php endif; ?>
                            <button onclick="window.location.href='<?php echo isAdmin() ? '../auth/logout.php' : 'auth/logout.php'; ?>'" class="logout-btn">Logout</button>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="auth-buttons">
                        <a href="auth/login.php" class="btn btn-outline">Login</a>
                        <a href="auth/register.php" class="btn btn-primary">Register</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</header>

<?php if(isLoggedIn()): ?>
<script>
let notificationPage = 1;
let notificationDropdownOpen = false;

function toggleNotifications() {
    const dropdown = document.getElementById('notificationDropdown');
    notificationDropdownOpen = !notificationDropdownOpen;
    
    if (notificationDropdownOpen) {
        dropdown.style.display = 'block';
        loadNotifications();
    } else {
        dropdown.style.display = 'none';
    }
}

function loadNotifications(page = 1) {
    fetch(`<?php echo isAdmin() ? '../' : ''; ?>load-notifications.php?page=${page}`)
        .then(response => response.json())
        .then(data => {
            const list = document.getElementById('notificationList');
            if (page === 1) {
                list.innerHTML = '';
            }
            
            if (data.notifications.length === 0 && page === 1) {
                list.innerHTML = '<div class="no-notifications">No notifications yet</div>';
                document.getElementById('loadMoreNotifications').style.display = 'none';
            } else {
                data.notifications.forEach(notification => {
                    const item = document.createElement('div');
                    item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
                    item.innerHTML = `
                        <div class="notification-content">
                            <h5>${notification.title}</h5>
                            <p>${notification.message}</p>
                            <span class="notification-time time-ago" data-datetime="${notification.created_at}"></span>
                        </div>
                    `;
                    list.appendChild(item);
                });
                
                document.getElementById('loadMoreNotifications').style.display = data.has_more ? 'block' : 'none';
            }
        });
}

function loadMoreNotifications() {
    notificationPage++;
    loadNotifications(notificationPage);
}

function markAllAsRead() {
    fetch(`<?php echo isAdmin() ? '../' : ''; ?>mark-notifications-read.php`, {
        method: 'POST'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelector('.notification-badge').style.display = 'none';
            document.querySelectorAll('.notification-item').forEach(item => {
                item.classList.remove('unread');
            });
        }
    });
}

// Close dropdown when clicking outside
document.addEventListener('click', function(event) {
    const container = document.querySelector('.notification-container');
    if (!container.contains(event.target) && notificationDropdownOpen) {
        toggleNotifications();
    }
});
</script>
<?php endif; ?>

<script src="<?php echo isAdmin() ? '../' : ''; ?>assets/js/time-ago.js"></script>
</header>
