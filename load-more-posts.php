<?php
require_once 'config/init.php';
require_once 'classes/Post.php';

header('Content-Type: application/json');

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$posts_per_page = 6;
$offset = ($page - 1) * $posts_per_page;

$post = new Post($db);
$posts = $post->getPublishedPosts(getUserId(), $posts_per_page, $offset);
$total_posts = $post->getTotalPublishedPosts(getUserId());
$has_more = ($offset + $posts_per_page) < $total_posts;

$html = '';
foreach($posts as $post_item) {
    $html .= '<article class="post-card">
        <div class="post-header">
            <div class="author-info">
                <img src="assets/images/avatars/' . htmlspecialchars($post_item['avatar']) . '" 
                     alt="' . htmlspecialchars($post_item['username']) . '" 
                     class="avatar">
                <div>
                    <h4>' . htmlspecialchars($post_item['username']) . '</h4>
                    <time class="post-date-small">' . formatDate($post_item['created_at']) . '</time>
                </div>
            </div>
        </div>
        
        <h2 class="post-title">
            <a href="post.php?id=' . $post_item['id'] . '">
                ' . htmlspecialchars($post_item['title']) . '
            </a>
        </h2>
        
        <div class="post-excerpt">
            ' . substr(strip_tags($post_item['content']), 0, 200) . '...
        </div>
        
        <div class="post-actions">
            <a href="post.php?id=' . $post_item['id'] . '" class="btn btn-outline">Read More</a>';
    
    if(isLoggedIn()) {
        $html .= '<button onclick="reportContent(\'post\', ' . $post_item['id'] . ')" class="btn btn-report">Report</button>';
    }
    
    $html .= '</div>
    </article>';
}

echo json_encode([
    'success' => true,
    'html' => $html,
    'has_more' => $has_more
]);
?>