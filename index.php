<?php
require_once 'config/init.php';
require_once 'classes/Post.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$post = new Post($db);
$posts = $post->getPublishedPosts(10, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="icon" href="assets/images/icons/IMG_1554.jpg">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="hero-section">
                <h1>Welcome to Our Personal Blog</h1>
                <p>Discover amazing stories, insights, and experiences from our community</p>
            </div>

            <div class="posts-grid">
                <?php if(empty($posts)): ?>
                    <div class="no-posts">
                        <h3>No posts available yet</h3>
                        <p>Be the first to share your story!</p>
                        <?php if(isLoggedIn()): ?>
                            <a href="create-post.php" class="btn btn-primary">Create Post</a>
                        <?php endif; ?>
                    </div>
                <?php else: ?>
                    <?php foreach($posts as $post_item): ?>
                        <article class="post-card">
                            <div class="post-header">
                                <div class="author-info">
                                    <img src="assets/images/avatars/<?php echo htmlspecialchars($post_item['avatar']); ?>" 
                                         alt="<?php echo htmlspecialchars($post_item['username']); ?>" 
                                         class="avatar">
                                    <div>
                                        <h4><?php echo htmlspecialchars($post_item['username']); ?></h4>
                                        <time><?php echo formatDate($post_item['created_at']); ?></time>
                                    </div>
                                </div>
                            </div>
                            
                            <h2 class="post-title">
                                <a href="post.php?id=<?php echo $post_item['id']; ?>">
                                    <?php echo htmlspecialchars($post_item['title']); ?>
                                </a>
                            </h2>
                            
                            <div class="post-excerpt">
                                <?php echo substr(strip_tags($post_item['content']), 0, 200) . '...'; ?>
                            </div>
                            
                            <div class="post-actions">
                                <a href="post.php?id=<?php echo $post_item['id']; ?>" class="btn btn-outline">Read More</a>
                                <?php if(isLoggedIn()): ?>
                                    <button onclick="reportContent('post', <?php echo $post_item['id']; ?>)" class="btn btn-report">Report</button>
                                <?php endif; ?>
                            </div>
                        </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>