<?php
require_once 'config/init.php';
require_once 'classes/User.php';
require_once 'classes/Post.php';

//debug
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


if(!isset($_GET['id'])) {
    redirect('index.php');
}

$profile_id = (int)$_GET['id'];
$user = new User($db);
$post = new Post($db);

$profile_data = $user->getUserById($profile_id);
if(!$profile_data) {
    redirect('index.php');
}

// Check if profile is accessible
$is_limited = $profile_data['status'] === 'limited' || $profile_data['status'] === 'banned';
$is_own_profile = isLoggedIn() && getUserId() == $profile_id;

// Limited/banned profiles should be inaccessible to others
if($is_limited && !$is_own_profile && !isAdmin()) {
    $access_denied = true;
} else {
    $access_denied = false;
    $user_posts = $post->getUserPosts($profile_id, getUserId());
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($profile_data['username']); ?>'s Profile - Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hero.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <?php if($access_denied): ?>
                <div class="access-denied">
                    <h1>Profile Not Available</h1>
                    <p>This user's profile is currently restricted and cannot be viewed.</p>
                    <a href="index.php" class="btn btn-primary">← Back to Home</a>
                </div>
            <?php else: ?>
                <div class="profile-banner">
                    <img src="assets/images/banners/<?php echo htmlspecialchars($profile_data['banner'] ?? 'default-banner.jpg'); ?>" alt="Profile Banner">
                </div>
                
                <?php if($is_limited && $is_own_profile): ?>
                    <div class="profile-restricted">
                        <h3>Account Status: <?php echo ucfirst($profile_data['status']); ?></h3>
                        <p>Your account has been <?php echo $profile_data['status']; ?>. Some features may be unavailable.</p>
                    </div>
                <?php endif; ?>
                
                <div class="profile-container">
                    <div class="profile-header">
                        <div class="profile-avatar">
                            <img src="assets/images/avatars/<?php echo htmlspecialchars($profile_data['avatar']); ?>" 
                                 alt="<?php echo htmlspecialchars($profile_data['username']); ?>">
                        </div>
                        <div class="profile-info">
                            <h1><?php echo htmlspecialchars($profile_data['username']); ?></h1>
                            <div class="profile-stats">
                                <div class="stat">
                                    <span class="stat-number"><?php echo $profile_data['total_posts']; ?></span>
                                    <span class="stat-label">Posts</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number"><?php echo $profile_data['total_comments']; ?></span>
                                    <span class="stat-label">Comments</span>
                                </div>
                                <div class="stat">
                                    <span class="stat-number"><?php echo $profile_data['gender_display']; ?></span>
                                    <span class="stat-label">Gender</span>
                                </div>
                            </div>
                            <p class="join-date">Member since <?php echo formatDate($profile_data['created_at']); ?></p>
                            
                            <?php if($is_own_profile): ?>
                                <a href="edit-profile.php" class="btn btn-outline">Edit Profile</a>
                            <?php endif; ?>
                        </div>
                    </div>
                    
                    <div class="profile-content">
                        <h2>Posts by <?php echo htmlspecialchars($profile_data['username']); ?></h2>
                        
                        <?php if(empty($user_posts)): ?>
                            <div class="no-posts">
                                <p>No posts yet.</p>
                            </div>
                        <?php else: ?>
                            <div class="profile-posts-list" id="profilePostsList">
                                <?php 
                                $displayed_posts = 0;
                                foreach($user_posts as $post_item): 
                                    if($post_item['status'] === 'published' || $is_own_profile):
                                        $displayed_posts++;
                                        if($displayed_posts > 6) break;
                                ?>
                                        <div class="profile-post-card" onclick="<?php echo $post_item['status'] === 'published' ? "window.location.href='post.php?id=" . $post_item['id'] . "'" : ''; ?>">
                                            <div class="profile-post-title">
                                                <?php echo htmlspecialchars($post_item['title']); ?>
                                                <?php if($post_item['status'] !== 'published'): ?>
                                                    <span class="status-badge status-<?php echo $post_item['status']; ?>">
                                                        <?php echo ucfirst($post_item['status']); ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            <div class="profile-post-excerpt">
                                                <?php echo substr(strip_tags($post_item['content']), 0, 150) . '...'; ?>
                                            </div>
                                            <div class="profile-post-meta">
                                                <time class="time-ago" data-datetime="<?php echo $post_item['created_at']; ?>"></time>
                                                <?php if($is_own_profile): ?>
                                                    <div class="profile-post-actions" onclick="event.stopPropagation();">
                                                        <a href="edit-post.php?id=<?php echo $post_item['id']; ?>" class="btn btn-edit">Edit</a>
                                                        <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?'); setTimeout(function(){ location.reload(); }, 100);">
                                                            <input type="hidden" name="action" value="delete_post">
                                                            <input type="hidden" name="post_id" value="<?php echo $post_item['id']; ?>">
                                                            <button type="submit" class="btn btn-delete">Delete</button>
                                                        </form>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>
                            
                            <?php if(count(array_filter($user_posts, function($post) use ($is_own_profile) {
                                return $post['status'] === 'published' || $is_own_profile;
                            })) > 6): ?>
                                <div class="show-more-container">
                                    <button class="show-more-btn" onclick="loadMoreProfilePosts()">Show More Posts</button>
                                </div>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/time-ago.js"></script>
    <script>
        let profilePostsPage = 1;
        
        function loadMoreProfilePosts() {
            profilePostsPage++;
            fetch(`load-more-profile-posts.php?user_id=<?php echo $profile_id; ?>&page=${profilePostsPage}`)
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        const container = document.getElementById('profilePostsList');
                        container.insertAdjacentHTML('beforeend', data.html);
                        
                        if (!data.has_more) {
                            document.querySelector('.show-more-btn').style.display = 'none';
                        }
                        
                        // Reinitialize time ago for new elements
                        const newTimeElements = container.querySelectorAll('.time-ago:not([data-initialized])');
                        newTimeElements.forEach(element => {
                            const dateTime = element.getAttribute('data-datetime');
                            if (dateTime) {
                                element.textContent = timeAgo(dateTime);
                                element.title = formatDateTime(dateTime);
                                element.setAttribute('data-initialized', 'true');
                            }
                        });
                    }
                });
        }
        
        // Handle post deletion
        <?php if($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'delete_post' && $is_own_profile): ?>
            <?php
            $post_id = (int)$_POST['post_id'];
            if($post->canUserEdit($post_id, getUserId())) {
                $post->delete($post_id);
                echo "window.location.reload();";
            }
            ?>
        <?php endif; ?>
    </script>
</body>
</html>