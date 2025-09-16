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
        if(isset($_POST['action'])) {
            $action = $_POST['action'];
            
            if($action === 'delete_post') {
                $post_id = (int)$_POST['post_id'];
                if($post->canUserEdit($post_id, getUserId()) || isAdmin()) {
                    $post->delete($post_id);
                    redirect('index.php');
                }
            } elseif($action === 'delete_comment') {
                $comment_id = (int)$_POST['comment_id'];
                if($comment->canUserEdit($comment_id, getUserId()) || isAdmin()) {
                    $comment->delete($comment_id);
                    redirect("post.php?id=$post_id#comments");
                }
            } elseif($action === 'edit_comment') {
                $comment_id = (int)$_POST['comment_id'];
                $content = sanitizeInput($_POST['content']);
                
                if($comment->canUserEdit($comment_id, getUserId()) && !empty($content)) {
                    $media_file = null;
                    
                    // Handle media upload for edit
                    if(isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'audio/mp3', 'audio/wav'];
                        $file_type = $_FILES['media']['type'];
                        $file_size = $_FILES['media']['size'];
                        
                        if($file_size <= 10 * 1024 * 1024 && in_array($file_type, $allowed_types)) {
                            $file_extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                            $new_filename = 'comment_media_' . getUserId() . '_' . time() . '.' . $file_extension;
                            $upload_path = 'assets/media/' . $new_filename;
                            
                            if(!is_dir('assets/media')) {
                                mkdir('assets/media', 0755, true);
                            }
                            
                            if(move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                                $media_file = $new_filename;
                            }
                        }
                    }
                    
                    $comment->update($comment_id, $content, $media_file);
                }
            }
        } else {
            // New comment
            $content = sanitizeInput($_POST['content']);
            if(!empty($content)) {
                $media_file = null;
                
                // Handle media upload
                if(isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
                    $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'audio/mp3', 'audio/wav'];
                    $file_type = $_FILES['media']['type'];
                    $file_size = $_FILES['media']['size'];
                    
                    if($file_size <= 10 * 1024 * 1024 && in_array($file_type, $allowed_types)) {
                        $file_extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                        $new_filename = 'comment_media_' . getUserId() . '_' . time() . '.' . $file_extension;
                        $upload_path = 'assets/media/' . $new_filename;
                        
                        if(!is_dir('assets/media')) {
                            mkdir('assets/media', 0755, true);
                        }
                        
                        if(move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                            $media_file = $new_filename;
                        }
                    }
                }
                
                $comment->createWithMedia($post_id, getUserId(), $content, $media_file);
                // Clear form
                $_POST = array();
            redirect("post.php?id=$post_id#comments");
        }
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
    <link rel="stylesheet" href="assets/css/hero.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
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
                                <time class="post-date-small"><?php echo formatDate($post_data['created_at']); ?></time>
                            </div>
                        </div>
                        <div class="post-actions">
                            <?php if(isLoggedIn() && $post_data['user_id'] == getUserId()): ?>
                                <div class="edit-delete-actions">
                                    <a href="edit-post.php?id=<?php echo $post_data['id']; ?>" class="btn btn-edit">Edit</a>
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?')">
                                        <input type="hidden" name="action" value="delete_post">
                                        <input type="hidden" name="post_id" value="<?php echo $post_data['id']; ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            <?php elseif(isAdmin()): ?>
                                <div class="edit-delete-actions">
                                    <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this post?'); setTimeout(function(){ window.location.href='index.php'; }, 100);">
                                        <input type="hidden" name="action" value="delete_post">
                                        <input type="hidden" name="post_id" value="<?php echo $post_data['id']; ?>">
                                        <button type="submit" class="btn btn-delete">Delete</button>
                                    </form>
                                </div>
                            <?php elseif(isLoggedIn()): ?>
                            <button onclick="reportContent('post', <?php echo $post_data['id']; ?>)" class="btn btn-report">Report</button>
                        <?php endif; ?>
                        </div>
                    </div>
                </header>
                
                <div class="post-content">
                    <?php 
                    // Parse BBCode for display
                    $content = htmlspecialchars($post_data['content']);
                    // Basic BBCode parsing
                    $content = preg_replace('/\[b\](.*?)\[\/b\]/i', '<strong>$1</strong>', $content);
                    $content = preg_replace('/\[i\](.*?)\[\/i\]/i', '<em>$1</em>', $content);
                    $content = preg_replace('/\[u\](.*?)\[\/u\]/i', '<u>$1</u>', $content);
                    $content = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/i', '<a href="$1" target="_blank" rel="noopener">$2</a>', $content);
                    $content = preg_replace('/\[url\](.*?)\[\/url\]/i', '<a href="$1" target="_blank" rel="noopener">$1</a>', $content);
                    $content = preg_replace('/\[img\](.*?)\[\/img\]/i', '<img src="$1" alt="Image" style="max-width: 100%; max-height: 400px; height: auto; border-radius: 8px; object-fit: contain;">', $content);
                    $content = preg_replace('/\[h1\](.*?)\[\/h1\]/i', '<h1>$1</h1>', $content);
                    $content = preg_replace('/\[h2\](.*?)\[\/h2\]/i', '<h2>$1</h2>', $content);
                    $content = preg_replace('/\[h3\](.*?)\[\/h3\]/i', '<h3>$1</h3>', $content);
                    $content = preg_replace('/\[centre\](.*?)\[\/centre\]/i', '<div class="bbcode-center">$1</div>', $content);
                    $content = preg_replace('/\[center\](.*?)\[\/center\]/i', '<div class="bbcode-center">$1</div>', $content);
                    $content = preg_replace('/\[box\](.*?)\[\/box\]/i', '<div class="bbcode-box">$1</div>', $content);
                    $content = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/i', '<span style="color: $1;">$2</span>', $content);
                    $content = preg_replace('/\[notice\](.*?)\[\/notice\]/i', '<div class="bbcode-notice">$1</div>', $content);
                    $content = nl2br($content);
                    echo $content;
                    ?>
                </div>
            </article>
            
            <section id="comments" class="comments-section">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                
                <?php if(isLoggedIn()): ?>
                    <?php if($_SESSION['status'] !== 'limited'): ?>
                        <form method="POST" class="comment-form">
                            <div class="form-group">
                                <div class="bbcode-toolbar">
                                    <button type="button" class="bbcode-btn" onclick="insertBBCode('comment-content', 'b')" title="Bold">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                            <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                        </svg>
                                    </button>
                                    <button type="button" class="bbcode-btn" onclick="insertBBCode('comment-content', 'i')" title="Italic">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <line x1="19" y1="4" x2="10" y2="4"></line>
                                            <line x1="14" y1="20" x2="5" y2="20"></line>
                                            <line x1="15" y1="4" x2="9" y2="20"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="bbcode-btn" onclick="insertBBCode('comment-content', 'u')" title="Underline">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path>
                                            <line x1="4" y1="21" x2="20" y2="21"></line>
                                        </svg>
                                    </button>
                                    <button type="button" class="bbcode-btn" onclick="insertBBCode('comment-content', 'url')" title="Link">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                            <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                        </svg>
                                    </button>
                                    </div>
                                    <textarea id="comment-content" name="content" placeholder="Share your thoughts..." required maxlength="1000"><?php echo !isset($_POST['action']) && isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                    <div class="char-counter">
                                        <span id="comment-count">0</span>/1000 characters
                                    </div>
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
                                        <time class="time-ago" data-datetime="<?php echo $post_data['created_at']; ?>"></time>
                                    </div>
                                </div>
                                <div class="comment-actions">
                                    <?php if(isLoggedIn() && ($comment_item['user_id'] == getUserId() || isAdmin())): ?>
                                        <div class="edit-delete-actions">
                                            <?php if($comment_item['user_id'] == getUserId()): ?>
                                                <button onclick="editComment(<?php echo $comment_item['id']; ?>)" class="btn btn-edit">Edit</button>
                                            <?php endif; ?>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this comment?'); setTimeout(function(){ location.reload(); }, 100);">
                                                <input type="hidden" name="action" value="delete_comment">
                                                <input type="hidden" name="comment_id" value="<?php echo $comment_item['id']; ?>">
                                                <button type="submit" class="btn btn-delete">Delete</button>
                                            </form>
                                        </div>
                                    <?php elseif(isLoggedIn()): ?>
                                    <button onclick="reportContent('comment', <?php echo $comment_item['id']; ?>)" class="btn btn-report btn-sm">Report</button>
                                <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="comment-content">
                                <?php 
                                // Parse BBCode for display
                                $content = htmlspecialchars($comment_item['content']);
                                // Basic BBCode parsing
                                $content = preg_replace('/\[b\](.*?)\[\/b\]/i', '<strong>$1</strong>', $content);
                                $content = preg_replace('/\[i\](.*?)\[\/i\]/i', '<em>$1</em>', $content);
                                $content = preg_replace('/\[u\](.*?)\[\/u\]/i', '<u>$1</u>', $content);
                                $content = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/i', '<a href="$1" target="_blank" rel="noopener">$2</a>', $content);
                                $content = preg_replace('/\[url\](.*?)\[\/url\]/i', '<a href="$1" target="_blank" rel="noopener">$1</a>', $content);
                                $content = preg_replace('/\[img\](.*?)\[\/img\]/i', '<img src="$1" alt="Image" style="max-width: 100%; max-height: 400px; height: auto; border-radius: 8px; object-fit: contain;">', $content);
                                $content = preg_replace('/\[h1\](.*?)\[\/h1\]/i', '<h1>$1</h1>', $content);
                                $content = preg_replace('/\[h2\](.*?)\[\/h2\]/i', '<h2>$1</h2>', $content);
                                $content = preg_replace('/\[h3\](.*?)\[\/h3\]/i', '<h3>$1</h3>', $content);
                                $content = preg_replace('/\[centre\](.*?)\[\/centre\]/i', '<div class="bbcode-center">$1</div>', $content);
                                $content = preg_replace('/\[center\](.*?)\[\/center\]/i', '<div class="bbcode-center">$1</div>', $content);
                                $content = preg_replace('/\[box\](.*?)\[\/box\]/i', '<div class="bbcode-box">$1</div>', $content);
                                $content = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/i', '<span style="color: $1;">$2</span>', $content);
                                $content = preg_replace('/\[notice\](.*?)\[\/notice\]/i', '<div class="bbcode-notice">$1</div>', $content);
                                $content = nl2br($content);
                                echo $content;
                                ?>
                            </div>
                            
                            <div id="edit-form-<?php echo $comment_item['id']; ?>" class="edit-form" style="display: none;">
                                <form method="POST">
                                    <input type="hidden" name="action" value="edit_comment">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment_item['id']; ?>">
                                    <div class="form-group">
                                        <textarea name="content" required maxlength="1000"><?php echo htmlspecialchars($comment_item['content']); ?></textarea>
                                    </div>
                                    <div class="form-actions">
                                        <button type="submit" class="btn btn-primary">Update Comment</button>
                                        <button type="button" onclick="cancelEdit(<?php echo $comment_item['id']; ?>)" class="btn btn-outline">Cancel</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/bbcode.js"></script>
    <script src="assets/js/time-ago.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Character counter for comment
        document.getElementById('comment-content').addEventListener('input', function() {
            document.getElementById('comment-count').textContent = this.value.length;
        });
        
        // Initialize counter
        document.getElementById('comment-count').textContent = document.getElementById('comment-content').value.length;
        
        function editComment(commentId) {
            document.getElementById('edit-form-' + commentId).style.display = 'block';
        }
        
        function cancelEdit(commentId) {
            document.getElementById('edit-form-' + commentId).style.display = 'none';
        }
    </script>
</body>
</html>