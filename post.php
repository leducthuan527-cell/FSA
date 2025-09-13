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
            
            if($action === 'edit_comment') {
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
            } elseif($action === 'delete_comment') {
                $comment_id = (int)$_POST['comment_id'];
                if($comment->canUserEdit($comment_id, getUserId())) {
                    $comment->delete($comment_id);
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
                            <?php elseif(isLoggedIn()): ?>
                            <button onclick="reportContent('post', <?php echo $post_data['id']; ?>)" class="btn btn-report">Report</button>
                        <?php endif; ?>
                        </div>
                    </div>
                </header>
                
                <?php if($post_data['media_file']): ?>
                    <div class="post-media">
                        <?php
                        $media_path = 'assets/media/' . $post_data['media_file'];
                        $file_extension = strtolower(pathinfo($post_data['media_file'], PATHINFO_EXTENSION));
                        
                        if(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])):
                        ?>
                            <img src="<?php echo $media_path; ?>" alt="Post media">
                        <?php elseif(in_array($file_extension, ['mp4', 'webm'])): ?>
                            <video controls>
                                <source src="<?php echo $media_path; ?>" type="video/<?php echo $file_extension; ?>">
                                Your browser does not support the video tag.
                            </video>
                        <?php elseif(in_array($file_extension, ['mp3', 'wav'])): ?>
                            <audio controls>
                                <source src="<?php echo $media_path; ?>" type="audio/<?php echo $file_extension; ?>">
                                Your browser does not support the audio tag.
                            </audio>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>
                
                <div class="post-content">
                    <?php echo nl2br(htmlspecialchars($post_data['content'])); ?>
                </div>
            </article>
            
            <section id="comments" class="comments-section">
                <h3>Comments (<?php echo count($comments); ?>)</h3>
                
                <?php if(isLoggedIn()): ?>
                    <?php if($_SESSION['status'] !== 'limited'): ?>
                        <form method="POST" enctype="multipart/form-data" class="comment-form">
                            <div class="form-group">
                                <div class="input-with-toolbar">
                                    <div class="text-toolbar">
                                        <button type="button" onclick="formatText('comment-content', 'bold')" title="Bold"><i class="fas fa-bold"></i></button>
                                        <button type="button" onclick="formatText('comment-content', 'italic')" title="Italic"><i class="fas fa-italic"></i></button>
                                        <button type="button" onclick="formatText('comment-content', 'underline')" title="Underline"><i class="fas fa-underline"></i></button>
                                        <button type="button" onclick="insertLink('comment-content')" title="Insert Link"><i class="fas fa-link"></i></button>
                                    </div>
                                    <textarea id="comment-content" name="content" placeholder="Share your thoughts..." required maxlength="1000"><?php echo !isset($_POST['action']) && isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                                    <div class="char-counter">
                                        <span id="comment-count">0</span>/1000 characters
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="comment-media">Attach Media (Optional)</label>
                                <input type="file" id="comment-media" name="media" accept="image/*,video/*,audio/*">
                                <small>Maximum file size: 10MB</small>
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
                                        <time class="comment-date-small"><?php echo formatDate($comment_item['created_at']); ?></time>
                                    </div>
                                </div>
                                <div class="comment-actions">
                                    <?php if(isLoggedIn() && $comment_item['user_id'] == getUserId()): ?>
                                        <div class="edit-delete-actions">
                                            <button onclick="editComment(<?php echo $comment_item['id']; ?>)" class="btn btn-edit">Edit</button>
                                            <form method="POST" style="display: inline;" onsubmit="return confirm('Are you sure you want to delete this comment?')">
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
                            
                            <?php if($comment_item['media_file']): ?>
                                <div class="comment-media">
                                    <?php
                                    $media_path = 'assets/media/' . $comment_item['media_file'];
                                    $file_extension = strtolower(pathinfo($comment_item['media_file'], PATHINFO_EXTENSION));
                                    
                                    if(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])):
                                    ?>
                                        <img src="<?php echo $media_path; ?>" alt="Comment media">
                                    <?php elseif(in_array($file_extension, ['mp4', 'webm'])): ?>
                                        <video controls>
                                            <source src="<?php echo $media_path; ?>" type="video/<?php echo $file_extension; ?>">
                                        </video>
                                    <?php elseif(in_array($file_extension, ['mp3', 'wav'])): ?>
                                        <audio controls>
                                            <source src="<?php echo $media_path; ?>" type="audio/<?php echo $file_extension; ?>">
                                        </audio>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="comment-content">
                                <?php echo nl2br(htmlspecialchars($comment_item['content'])); ?>
                            </div>
                            
                            <div id="edit-form-<?php echo $comment_item['id']; ?>" class="edit-form" style="display: none;">
                                <form method="POST" enctype="multipart/form-data">
                                    <input type="hidden" name="action" value="edit_comment">
                                    <input type="hidden" name="comment_id" value="<?php echo $comment_item['id']; ?>">
                                    <div class="form-group">
                                        <textarea name="content" required maxlength="1000"><?php echo htmlspecialchars($comment_item['content']); ?></textarea>
                                    </div>
                                    <div class="form-group">
                                        <input type="file" name="media" accept="image/*,video/*,audio/*">
                                        <small>Leave empty to keep current media</small>
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
    
    <script src="assets/js/main.js"></script>
    <script src="assets/js/text-editor.js"></script>
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