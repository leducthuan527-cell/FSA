<?php
require_once 'config/init.php';
require_once 'classes/Post.php';
require_once 'classes/Comment.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isset($_GET['id'])) {
    redirect('index.php');
}

$post_id = (int)$_GET['id'];
$post = new Post($db);
$comment = new Comment($db);

$post_data = $post->getPostById($post_id);
if(!$post_data || $post_data['status'] !== 'published') {
    redirect('index.php');
}

$comments = $comment->getPostComments($post_id);

// Handle comment submission
if($_SERVER['REQUEST_METHOD'] === 'POST' && isLoggedIn()) {
    if($_SESSION['status'] !== 'limited') {
        $content = sanitizeInput($_POST['content']);
        if(!empty($content)) {
            $comment->create($post_id, getUserId(), $content);
            redirect("post.php?id=$post_id#comments");
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($post_data['title']); ?> - Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <article class="post-full">
                <header class="post-header">
                    <h1><?php echo htmlspecialchars($post_data['title']); ?></h1>
                    <div class="post-meta">
                        <div class="author-info">
                            <img src="assets/images/avatars/<?php echo htmlspecialchars($post_data['avatar']); ?>" 
                                 alt="<?php echo htmlspecialchars($post_data['username']); ?>" 
                                 class="avatar">
                            <div>
                                <a href="profile.php?id=<?php echo $post_data['user_id']; ?>" class="author-name">
                                    <?php echo htmlspecialchars($post_data['username']); ?>
                                </a>
                                <time><?php echo formatDate($post_data['created_at']); ?></time>
                            </div>
                        </div>
                       <?php if(isLoggedIn() && $post_data['user_id'] != getUserId()): ?>
                            <button onclick="reportContent('post', <?php echo $post_data['id']; ?>)" class="btn btn-report">Report</button>
                        <?php endif; ?>
                    </div>
                </header>
                
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post_data['content'])); ?>
                </div>
            </article>
            
            <section id="comments" class="comments-section">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                
                <?php if(isLoggedIn()): ?>
                    <?php if($_SESSION['status'] !== 'limited'): ?>
                        <form method="POST" class="comment-form">
                            <div class="form-group">
                                <textarea name="content" placeholder="Share your thoughts..." required></textarea>
                            </div>
                            <button type="submit" class="btn btn-primary">Post Comment</button>
                        </form>
                    <?php else: ?>
                        <div class="alert alert-warning">Your account is limited. You cannot post comments.</div>
                    <?php endif; ?>
                <?php else: ?>
                    <div class="auth-prompt">
                        <p><a href="auth/login.php">Sign in</a> to join the conversation</p>
                    </div>
                <?php endif; ?>
                
                <div class="comments-list">
                    <?php foreach($comments as $comment_item): ?>
                        <?php 
                        // Check if user has hidden this comment
                        $is_hidden = isset($_SESSION['hidden_content']) && 
                                   in_array('comment_' . $comment_item['id'], $_SESSION['hidden_content']);
                        if($is_hidden) continue;
                        ?>
                        <div class="comment">
                            <div class="comment-header">
                                <div class="author-info">
                                    <img src="assets/images/avatars/<?php echo htmlspecialchars($comment_item['avatar']); ?>" 
                                         alt="<?php echo htmlspecialchars($comment_item['username']); ?>" 
                                         class="avatar avatar-sm">
                                    <div>
                                        <a href="profile.php?id=<?php echo $comment_item['user_id']; ?>" class="author-name">
                                            <?php echo htmlspecialchars($comment_item['username']); ?>
                                        </a>
                                        <time><?php echo formatDate($comment_item['created_at']); ?></time>
                                    </div>
                                </div>
                                <?php if(isLoggedIn() && $comment_item['user_id'] != getUserId()): ?>
                                    <button onclick="reportContent('comment', <?php echo $comment_item['id']; ?>)" class="btn btn-report btn-sm">Report</button>
                                <?php endif; ?>
                            </div>
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment_item['content'])); ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>