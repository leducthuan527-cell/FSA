<?php
require_once 'config/init.php';
require_once 'classes/Post.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isLoggedIn()) {
    redirect('auth/login.php');
}

if(!isset($_GET['id'])) {
    redirect('index.php');
}

$post_id = (int)$_GET['id'];
$post = new Post($db);
$post_data = $post->getPostById($post_id);

if(!$post_data || !$post->canUserEdit($post_id, getUserId())) {
    redirect('index.php');
}

$error = '';
$success = '';

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = sanitizeInput($_POST['title']);
    $content = sanitizeInput($_POST['content']);
    
    if(empty($title) || empty($content)) {
        $error = 'Please fill in all fields';
    } elseif(strlen($title) > 100) {
        $error = 'Title must be 100 characters or less';
    } elseif(strlen($content) > 3000) {
        $error = 'Content must be 3000 characters or less';
    } else {
        $media_file = $post_data['media_file']; // Keep existing media by default
        
        // Handle media upload
        if(isset($_FILES['media']) && $_FILES['media']['error'] === UPLOAD_ERR_OK) {
            $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'video/mp4', 'video/webm', 'audio/mp3', 'audio/wav'];
            $file_type = $_FILES['media']['type'];
            $file_size = $_FILES['media']['size'];
            
            if($file_size > 10 * 1024 * 1024) {
                $error = 'File size must be less than 10MB';
            } elseif(in_array($file_type, $allowed_types)) {
                $file_extension = pathinfo($_FILES['media']['name'], PATHINFO_EXTENSION);
                $new_filename = 'post_media_' . getUserId() . '_' . time() . '.' . $file_extension;
                $upload_path = 'assets/media/' . $new_filename;
                
                if(!is_dir('assets/media')) {
                    mkdir('assets/media', 0755, true);
                }
                
                if(move_uploaded_file($_FILES['media']['tmp_name'], $upload_path)) {
                    // Delete old media file if exists
                    if($post_data['media_file'] && file_exists('assets/media/' . $post_data['media_file'])) {
                        unlink('assets/media/' . $post_data['media_file']);
                    }
                    $media_file = $new_filename;
                } else {
                    $error = 'Failed to upload media file';
                }
            } else {
                $error = 'Invalid file type';
            }
        }
        
        if(!$error) {
            if($post->update($post_id, $title, $content, $media_file)) {
                $success = 'Post updated successfully! It will be reviewed by admin before being published.';
                $post_data = $post->getPostById($post_id); // Refresh data
            } else {
                $error = 'Failed to update post';
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
    <title>Edit Post - Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hero.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="create-post-container">
                <h1>Edit Post</h1>
                
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" enctype="multipart/form-data" class="post-form">
                    <div class="form-group">
                        <label for="title">Post Title</label>
                        <div class="input-with-toolbar">
                            <div class="text-toolbar">
                                <button type="button" onclick="formatText('title', 'bold')" title="Bold"><i class="fas fa-bold"></i></button>
                                <button type="button" onclick="formatText('title', 'italic')" title="Italic"><i class="fas fa-italic"></i></button>
                                <button type="button" onclick="formatText('title', 'underline')" title="Underline"><i class="fas fa-underline"></i></button>
                                <button type="button" onclick="insertLink('title')" title="Insert Link"><i class="fas fa-link"></i></button>
                            </div>
                            <input type="text" id="title" name="title" required 
                                   value="<?php echo htmlspecialchars($post_data['title']); ?>"
                                   maxlength="100">
                            <div class="char-counter">
                                <span id="title-count">0</span>/100 characters
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Content</label>
                        <div class="input-with-toolbar">
                            <div class="text-toolbar">
                                <button type="button" onclick="formatText('content', 'bold')" title="Bold"><i class="fas fa-bold"></i></button>
                                <button type="button" onclick="formatText('content', 'italic')" title="Italic"><i class="fas fa-italic"></i></button>
                                <button type="button" onclick="formatText('content', 'underline')" title="Underline"><i class="fas fa-underline"></i></button>
                                <button type="button" onclick="insertLink('content')" title="Insert Link"><i class="fas fa-link"></i></button>
                                <button type="button" onclick="formatText('content', 'h1')" title="Heading 1">H1</button>
                                <button type="button" onclick="formatText('content', 'h2')" title="Heading 2">H2</button>
                                <button type="button" onclick="formatText('content', 'ul')" title="Bullet List"><i class="fas fa-list-ul"></i></button>
                                <button type="button" onclick="formatText('content', 'ol')" title="Numbered List"><i class="fas fa-list-ol"></i></button>
                            </div>
                            <textarea id="content" name="content" rows="15" required maxlength="3000"><?php echo htmlspecialchars($post_data['content']); ?></textarea>
                            <div class="char-counter">
                                <span id="content-count">0</span>/3000 characters
                            </div>
                        </div>
                    </div>
                    
                    <?php if($post_data['media_file']): ?>
                        <div class="form-group">
                            <label>Current Media</label>
                            <div class="media-preview">
                                <?php
                                $media_path = 'assets/media/' . $post_data['media_file'];
                                $file_extension = strtolower(pathinfo($post_data['media_file'], PATHINFO_EXTENSION));
                                
                                if(in_array($file_extension, ['jpg', 'jpeg', 'png', 'gif'])):
                                ?>
                                    <img src="<?php echo $media_path; ?>" alt="Current media" style="max-width: 200px;">
                                <?php elseif(in_array($file_extension, ['mp4', 'webm'])): ?>
                                    <video controls style="max-width: 200px;">
                                        <source src="<?php echo $media_path; ?>" type="video/<?php echo $file_extension; ?>">
                                    </video>
                                <?php elseif(in_array($file_extension, ['mp3', 'wav'])): ?>
                                    <audio controls>
                                        <source src="<?php echo $media_path; ?>" type="audio/<?php echo $file_extension; ?>">
                                    </audio>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endif; ?>
                    
                    <div class="form-group">
                        <label for="media">Update Media (Optional)</label>
                        <input type="file" id="media" name="media" accept="image/*,video/*,audio/*">
                        <small>Leave empty to keep current media. Maximum file size: 10MB.</small>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Update Post</button>
                        <a href="post.php?id=<?php echo $post_id; ?>" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/text-editor.js"></script>
    <script>
        // Character counters
        document.getElementById('title').addEventListener('input', function() {
            document.getElementById('title-count').textContent = this.value.length;
        });
        
        document.getElementById('content').addEventListener('input', function() {
            document.getElementById('content-count').textContent = this.value.length;
        });
        
        // Initialize counters
        document.getElementById('title-count').textContent = document.getElementById('title').value.length;
        document.getElementById('content-count').textContent = document.getElementById('content').value.length;
    </script>
</body>
</html>