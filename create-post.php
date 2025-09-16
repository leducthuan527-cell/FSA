<?php
require_once 'config/init.php';
require_once 'classes/Post.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isLoggedIn()) {
    redirect('auth/login.php');
}

if($_SESSION['status'] === 'limited') {
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
        if(!$error) {
        $post = new Post($db);
            if($post->createWithMedia(getUserId(), $title, $content)) {
            $success = 'Post submitted for review. It will be published after admin approval.';
                // Clear form data
                $_POST = array();
        } else {
            $error = 'Failed to create post. Please try again.';
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
    <title>Create Post - Personal Blog</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="assets/css/hero.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="main-content">
        <div class="container">
            <div class="create-post-container">
                <h1>Create New Post</h1>
                
                <?php if($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <?php if($success): ?>
                    <div class="alert alert-success"><?php echo $success; ?></div>
                <?php endif; ?>
                
                <form method="POST" class="post-form">
                    <div class="form-group">
                        <label for="title">Post Title</label>
                        <input type="text" id="title" name="title" required 
                                   value="<?php echo !$success && isset($_POST['title']) ? htmlspecialchars($_POST['title']) : ''; ?>"
                                   maxlength="100">
                            <div class="char-counter">
                                <span id="title-count">0</span>/100 characters
                            </div>
                    </div>
                    
                    <div class="form-group">
                        <label for="content">Content</label>
                        <div class="bbcode-toolbar">
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'b')" title="Bold">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 4h8a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                    <path d="M6 12h9a4 4 0 0 1 4 4 4 4 0 0 1-4 4H6z"></path>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'i')" title="Italic">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="19" y1="4" x2="10" y2="4"></line>
                                    <line x1="14" y1="20" x2="5" y2="20"></line>
                                    <line x1="15" y1="4" x2="9" y2="20"></line>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'u')" title="Underline">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M6 3v7a6 6 0 0 0 6 6 6 6 0 0 0 6-6V3"></path>
                                    <line x1="4" y1="21" x2="20" y2="21"></line>
                                </svg>
                            </button>
                            <div class="toolbar-separator"></div>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'url')" title="Link">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                                    <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'img')" title="Image">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                    <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                    <polyline points="21,15 16,10 5,21"></polyline>
                                </svg>
                            </button>
                            <div class="toolbar-separator"></div>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'h1')" title="Heading 1">H1</button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'h2')" title="Heading 2">H2</button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'h3')" title="Heading 3">H3</button>
                            <div class="toolbar-separator"></div>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'ul')" title="Bullet List">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="8" y1="6" x2="21" y2="6"></line>
                                    <line x1="8" y1="12" x2="21" y2="12"></line>
                                    <line x1="8" y1="18" x2="21" y2="18"></line>
                                    <line x1="3" y1="6" x2="3.01" y2="6"></line>
                                    <line x1="3" y1="12" x2="3.01" y2="12"></line>
                                    <line x1="3" y1="18" x2="3.01" y2="18"></line>
                                </svg>
                            </button>
                            <button type="button" class="bbcode-btn" onclick="insertBBCode('content', 'ol')" title="Numbered List">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="10" y1="6" x2="21" y2="6"></line>
                                    <line x1="10" y1="12" x2="21" y2="12"></line>
                                    <line x1="10" y1="18" x2="21" y2="18"></line>
                                    <path d="M4 6h1v4"></path>
                                    <path d="M4 10h2"></path>
                                    <path d="M6 18H4c0-1 2-2 2-3s-1-1.5-2-1"></path>
                                </svg>
                            </button>
                            </div>
                            <textarea id="content" name="content" rows="15" required maxlength="3000"><?php echo !$success && isset($_POST['content']) ? htmlspecialchars($_POST['content']) : ''; ?></textarea>
                            <div class="char-counter">
                                <span id="content-count">0</span>/3000 characters
                            </div>
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Submit for Review</button>
                        <a href="index.php" class="btn btn-outline">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </main>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/bbcode.js"></script>
    <script src="assets/js/time-ago.js"></script>
    <script src="assets/js/main.js"></script>
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