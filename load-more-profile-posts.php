<?php
require_once 'config/init.php';
require_once 'classes/Post.php';

header('Content-Type: application/json');

$user_id = isset($_GET['user_id']) ? (int)$_GET['user_id'] : 0;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts_per_page = 6;
$offset = ($page - 1) * $posts_per_page;

if (!$user_id) {
    echo json_encode(['success' => false, 'message' => 'Invalid user ID']);
    exit;
}

$post = new Post($db);
$user_posts = $post->getUserPosts($user_id, getUserId());

// Filter posts based on status and pagination
$filtered_posts = array_filter($user_posts, function($post_item) {
    $is_own_profile = isLoggedIn() && getUserId() == $post_item['user_id'];
    return $post_item['status'] === 'published' || $is_own_profile;
});

$total_posts = count($filtered_posts);
$paginated_posts = array_slice($filtered_posts, $offset, $posts_per_page);
$has_more = ($offset + $posts_per_page) < $total_posts;

$html = '';
$is_own_profile = isLoggedIn() && getUserId() == $user_id;

foreach($paginated_posts as $post_item) {
    $html .= '<div class="profile-post-card" onclick="' . ($post_item['status'] === 'published' ? "window.location.href='post.php?id=" . $post_item['id'] . "'" : '') . '">
        <div class="profile-post-title">
            ' . htmlspecialchars($post_item['title']) . '
            ' . ($post_item['status'] !== 'published' ? '<span class="status-badge status-' . $post_item['status'] . '">' . ucfirst($post_item['status']) . '</span>' : '') . '
        </div>
        <div class="profile-post-excerpt">
            ' . substr(strip_tags($post_item['content']), 0, 150) . '...
        </div>
        <div class="profile-post-meta">
            <time class="time-ago" data-datetime="' . $post_item['created_at'] . '"></time>';
    
    if($is_own_profile) {
        $html .= '<div class="profile-post-actions" onclick="event.stopPropagation();">
                <a href="edit-post.php?id=' . $post_item['id'] . '" class="btn btn-edit">Edit</a>
                <form method="POST" style="display: inline;" onsubmit="return confirm(\'Are you sure you want to delete this post?\'); setTimeout(function(){ location.reload(); }, 100);">
                    <input type="hidden" name="action" value="delete_post">
                    <input type="hidden" name="post_id" value="' . $post_item['id'] . '">
                    <button type="submit" class="btn btn-delete">Delete</button>
                </form>
            </div>';
    }
    
    $html .= '</div>
    </div>';
}

echo json_encode([
    'success' => true,
    'html' => $html,
    'has_more' => $has_more
]);
?>