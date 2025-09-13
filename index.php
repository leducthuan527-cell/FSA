<?php
require_once 'config/init.php';
require_once 'classes/Post.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$post = new Post($db);
$posts = $post->getPublishedPosts(getUserId(), 10, 0);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hero.css">
    <link rel="icon" href="assets/images/icons/IMG_1554.jpg">
</head>
<body class="hero-page">
    <?php include 'includes/header.php'; ?>
    
    <main>
        <!-- Hero Geometric Section -->
        <section class="hero-geometric">
            <div class="hero-background-gradient"></div>
            
            <div class="hero-shapes-container">
                <div class="elegant-shape shape-1" style="--rotate: 12deg;">
                    <div class="elegant-shape-inner">
                        <div class="elegant-shape-element"></div>
                    </div>
                </div>
                
                <div class="elegant-shape shape-2" style="--rotate: -15deg;">
                    <div class="elegant-shape-inner">
                        <div class="elegant-shape-element"></div>
                    </div>
                </div>
                
                <div class="elegant-shape shape-3" style="--rotate: -8deg;">
                    <div class="elegant-shape-inner">
                        <div class="elegant-shape-element"></div>
                    </div>
                </div>
                
                <div class="elegant-shape shape-4" style="--rotate: 20deg;">
                    <div class="elegant-shape-inner">
                        <div class="elegant-shape-element"></div>
                    </div>
                </div>
                
                <div class="elegant-shape shape-5" style="--rotate: -25deg;">
                    <div class="elegant-shape-inner">
                        <div class="elegant-shape-element"></div>
                    </div>
                </div>
            </div>
            
            <div class="hero-content">
                <div class="hero-inner">
                    <div class="hero-badge">
                        <div class="hero-badge-icon"></div>
                        <span class="hero-badge-text">Personal Blog System</span>
                    </div>
                    
                    <h1 class="hero-title">
                        <span class="hero-title-line1">Welcome to Our</span>
                        <br>
                        <span class="hero-title-line2">Creative Community</span>
                    </h1>
                    
                    <p class="hero-description">
                        Discover amazing stories, insights, and experiences from our vibrant community of writers and creators.
                    </p>
                </div>
            </div>
            
            <div class="hero-overlay"></div>
        </section>

        <!-- Posts Section -->
        <section class="posts-section">
            <div class="container">
                <div style="text-align: center; margin-bottom: 3rem;">
                    <h2 style="font-size: 2.5rem; color: #f1f5f9; margin-bottom: 1rem; font-weight: 700;">Latest Stories</h2>
                    <p style="color: #94a3b8; font-size: 1.1rem; max-width: 600px; margin: 0 auto;">Explore the latest posts from our community of passionate writers and storytellers.</p>
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
        </section>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>