<?php
require_once '../config/init.php';
require_once '../classes/Post.php';
require_once '../classes/Comment.php';
require_once '../classes/Report.php';
require_once '../classes/User.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isAdmin()) {
    redirect('../index.php');
}

$post = new Post($db);
$comment = new Comment($db);
$report = new Report($db);
$user = new User($db);

$pending_posts = $post->getPendingPosts();
$pending_comments = $comment->getPendingComments();
$pending_reports = $report->getPendingReports();
$all_users = $user->getAllUsers();

// Handle actions
if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'];
    $type = $_POST['type'];
    $id = (int)$_POST['id'];
    
    switch($action) {
        case 'approve_post':
            $post->updateStatus($id, 'published');
            break;
        case 'reject_post':
            $post->updateStatus($id, 'hidden');
            break;
        case 'approve_comment':
            $comment->updateStatus($id, 'approved');
            break;
        case 'reject_comment':
            $comment->updateStatus($id, 'hidden');
            break;
        case 'handle_report':
            $report_action = $_POST['report_action'];
            if($report_action === 'hide') {
                $report->takeAction($id, 'hide');
            } else {
                $report->updateStatus($id, 'dismissed');
            }
            break;
        case 'limit_user':
            $user_status = $_POST['user_status'];
            $user->limitUser($id, $user_status);
            break;
    }
    
    redirect('index.php');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - Personal Blog</title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <link rel="stylesheet" href="../assets/css/hero.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <nav class="admin-sidebar">
            <div class="admin-logo">
                <h2>Admin Panel</h2>
            </div>
            <ul class="admin-nav">
                <li><a href="#dashboard" class="nav-link active">Dashboard</a></li>
                <li><a href="#posts" class="nav-link">Pending Posts</a></li>
                <li><a href="#comments" class="nav-link">Pending Comments</a></li>
                <li><a href="#reports" class="nav-link">Reports</a></li>
                <li><a href="#users" class="nav-link">Users</a></li>
                <li><a href="../index.php" class="nav-link">← Back to Site</a></li>
            </ul>
        </nav>
        
        <main class="admin-content">
            <section id="dashboard" class="admin-section active">
                <h1>Dashboard</h1>
                <div class="stats-grid">
                    <div class="stat-card">
                        <h3>Pending Posts</h3>
                        <div class="stat-number"><?php echo count($pending_posts); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Comments</h3>
                        <div class="stat-number"><?php echo count($pending_comments); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Pending Reports</h3>
                        <div class="stat-number"><?php echo count($pending_reports); ?></div>
                    </div>
                    <div class="stat-card">
                        <h3>Total Users</h3>
                        <div class="stat-number"><?php echo count($all_users); ?></div>
                    </div>
                </div>
            </section>
            
            <section id="posts" class="admin-section">
                <h1>Pending Posts</h1>
                <div class="admin-table">
                    <?php if(empty($pending_posts)): ?>
                        <p>No pending posts.</p>
                    <?php else: ?>
                        <?php foreach($pending_posts as $post_item): ?>
                            <div class="admin-item">
                                <div class="item-header">
                                    <div class="item-header-left">
                                        <div class="author-info-admin">
                                        <img src="../assets/images/avatars/<?php echo htmlspecialchars($post_item['avatar']); ?>" alt="<?php echo htmlspecialchars($post_item['username']); ?>">
                                            <div class="author-details">
                                                <h4><?php echo htmlspecialchars($post_item['username']); ?></h4>
                                                <span class="author-role">Author</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-header-right">
                                        <span>Submitted: <?php echo formatDate($post_item['created_at']); ?></span>
                                    </div>
                                </div>
                                <h3><?php echo htmlspecialchars($post_item['title']); ?></h3>
                                <div class="spacer"></div>
                                <div class="item-content">
                                    <?php echo substr(strip_tags($post_item['content']), 0, 200) . '...'; ?>
                                </div>
                                <div class="item-actions">
                                    <div class="item-actions">
                                    <button class="btn-view-details"
                                    data-id="<?php echo $post_item['id']; ?>"
                                    data-title="<?php echo htmlspecialchars($post_item['title'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-content="<?php echo htmlspecialchars($post_item['content'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-author="<?php echo htmlspecialchars($post_item['username'], ENT_QUOTES, 'UTF-8'); ?>"
                                    data-date="<?php echo htmlspecialchars(formatDate($post_item['created_at']), ENT_QUOTES, 'UTF-8'); ?>">View Details</button>
                                </div>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve_post">
                                        <input type="hidden" name="id" value="<?php echo $post_item['id']; ?>">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reject_post">
                                        <input type="hidden" name="id" value="<?php echo $post_item['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <section id="comments" class="admin-section">
                <h1>Pending Comments</h1>
                <div class="admin-table">
                    <?php if(empty($pending_comments)): ?>
                        <p>No pending comments.</p>
                    <?php else: ?>
                        <?php foreach($pending_comments as $comment_item): ?>
                            <div class="admin-item">
                                <div class="item-header">
                                    <div class="item-header-left">
                                        <div class="author-info-admin">
                                            <img src="../assets/images/avatars/<?php echo htmlspecialchars($comment_item['avatar']); ?>" alt="<?php echo htmlspecialchars($comment_item['username']); ?>">
                                            <div class="author-details">
                                                <h4><?php echo htmlspecialchars($comment_item['username']); ?></h4>
                                                <span class="author-role">Commenter</span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="item-header-right">
                                        <span>Submitted: <?php echo formatDate($comment_item['created_at']); ?></span>
                                    </div>
                                </div>
                                <h3>Comment on "<?php echo htmlspecialchars($comment_item['post_title']); ?>"</h3>
                                <div class="spacer"></div>
                                <div class="item-content">
                                    <?php echo htmlspecialchars($comment_item['content'], ENT_QUOTES, 'UTF-8'); ?>
                                </div>
                                <div class="item-actions">
                                 <button class="btn-view-details"
                                 data-content="<?php echo htmlspecialchars($comment_item['content'], ENT_QUOTES, 'UTF-8'); ?>"
                                 data-author="<?php echo htmlspecialchars($comment_item['username'], ENT_QUOTES, 'UTF-8'); ?>"
                                 data-posttitle="<?php echo htmlspecialchars($comment_item['post_title'], ENT_QUOTES, 'UTF-8'); ?>"
                                 data-date="<?php echo htmlspecialchars(formatDate($comment_item['created_at']), ENT_QUOTES, 'UTF-8'); ?>"
                                 onclick="showCommentDetails(this)">View Details</button>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="approve_comment">
                                        <input type="hidden" name="id" value="<?php echo $comment_item['id']; ?>">
                                        <button type="submit" class="btn btn-success">Approve</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="reject_comment">
                                        <input type="hidden" name="id" value="<?php echo $comment_item['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Reject</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <section id="reports" class="admin-section">
                <h1>Content Reports</h1>
                <div class="admin-table">
                    <?php if(empty($pending_reports)): ?>
                        <p>No pending reports.</p>
                    <?php else: ?>
                        <?php foreach($pending_reports as $report_item): ?>
                            <div class="admin-item">
                                <div class="item-header">
                                    <span class="report-type"><?php echo ucfirst($report_item['reported_type']); ?> Report</span>
                                    <span class="reporter">by <?php echo htmlspecialchars($report_item['reporter_username']); ?></span>
                                </div>
                                <div class="item-content">
                                    <p><strong>Reason:</strong> <?php echo htmlspecialchars($report_item['reason']); ?></p>
                                    <p><strong>Content:</strong> <?php echo htmlspecialchars(substr($report_item['content_preview'], 0, 200)); ?>...</p>
                                </div>
                                <div class="item-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="handle_report">
                                        <input type="hidden" name="report_action" value="hide">
                                        <input type="hidden" name="id" value="<?php echo $report_item['id']; ?>">
                                        <button type="submit" class="btn btn-danger">Hide Content</button>
                                    </form>
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="handle_report">
                                        <input type="hidden" name="report_action" value="dismiss">
                                        <input type="hidden" name="id" value="<?php echo $report_item['id']; ?>">
                                        <button type="submit" class="btn btn-outline">Dismiss</button>
                                    </form>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </section>
            
            <section id="users" class="admin-section">
                <h1>User Management</h1>
                <div class="admin-table">
                    <?php foreach($all_users as $user_item): ?>
                        <div class="admin-item">
                            <div class="item-header">
                                <h3><?php echo htmlspecialchars($user_item['username']); ?></h3>
                                <span class="status status-<?php echo $user_item['status']; ?>">
                                    <?php echo ucfirst($user_item['status']); ?>
                                </span>
                            </div>
                            <div class="item-content">
                                <p>Posts: <?php echo $user_item['total_posts']; ?> | Comments: <?php echo $user_item['total_comments']; ?></p>
                                <p>Joined: <?php echo formatDate($user_item['created_at']); ?></p>
                            </div>
                            <?php if($user_item['id'] != getUserId()): ?>
                                <div class="item-actions">
                                    <form method="POST" style="display: inline;">
                                        <input type="hidden" name="action" value="limit_user">
                                        <input type="hidden" name="id" value="<?php echo $user_item['id']; ?>">
                                        <select name="user_status">
                                            <option value="active" <?php echo $user_item['status'] === 'active' ? 'selected' : ''; ?>>Active</option>
                                            <option value="limited" <?php echo $user_item['status'] === 'limited' ? 'selected' : ''; ?>>Limited</option>
                                            <option value="banned" <?php echo $user_item['status'] === 'banned' ? 'selected' : ''; ?>>Banned</option>
                                        </select>
                                    </form>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </section>
        </main>
    </div>
    
    <!-- Modal for viewing details -->
    <div id="detailsModal" class="modal-overlay" style="display: none;">
        <div class="modal-content">
            <div class="modal-header">
                <h3 id="modalTitle">Details</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modalBody">
                <!-- Content will be populated by JavaScript -->
            </div>
        </div>
    </div>
    
    <script src="../assets/js/admin.js"></script>
    <script src="../assets/js/main.js"></script>
    <script>
        function showPostDetails(id, title, content, author, date) {
            document.getElementById('modalTitle').textContent = 'Post Details';
            document.getElementById('modalBody').innerHTML = `
                <h4>Title:</h4>
                <p>${title}</p>
                <h4>Author:</h4>
                <p>${author}</p>
                <h4>Submitted:</h4>
                <p>${date}</p>
                <h4>Content:</h4>
                <div class="content-preview">${content.replace(/\n/g, '<br>')}</div>
            `;
            document.getElementById('detailsModal').style.display = 'flex';
        }

    document.querySelectorAll('.btn-view-details').forEach(btn => {
    btn.addEventListener('click', () => {
        const id = btn.dataset.id;
        const title = btn.dataset.title;
        const content = btn.dataset.content || "";
        const author = btn.dataset.author;
        const date = btn.dataset.date;

        showPostDetails(id, title, content, author, date);
    });
});

        
    function showCommentDetails(button) {
    const content = button.dataset.content;
    const author = button.dataset.author;
    const postTitle = button.dataset.posttitle;
    const date = button.dataset.date;

    document.getElementById('modalTitle').textContent = 'Comment Details';
    document.getElementById('modalBody').innerHTML = `
        <h4>Author:</h4>
        <p>${author}</p>
        <h4>Post:</h4>
        <p>${postTitle}</p>
        <h4>Submitted:</h4>
        <p>${date}</p>
        <h4>Comment:</h4>
        <div class="content-preview">${content.replace(/\n/g, '<br>')}</div>
    `;
    document.getElementById('detailsModal').style.display = 'flex';
    }

   
        function closeModal() {
            document.getElementById('detailsModal').style.display = 'none';
        }
        
        // Close modal when clicking outside
        document.getElementById('detailsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeModal();
            }
        });
    </script>
</body>
</html>