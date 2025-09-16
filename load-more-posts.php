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
                    <time class="time-ago" data-datetime="' . $post_item['created_at'] . '" title="' . formatDate($post_item['created_at']) . '"></time>
                </div>
            </div>
        </div>
        
        <h2 class="post-title">
            <a href="post.php?id=' . $post_item['id'] . '">
                ' . htmlspecialchars($post_item['title']) . '
            </a>
        </h2>
        
        <div class="post-excerpt">
            ';
    
    // Parse BBCode for preview and limit to 40-50 words
    $content = $post_item['content'];
    // Remove BBCode tags for preview
    $content = preg_replace('/\[img\](.*?)\[\/img\]/i', '[Image]', $content);
    $content = preg_replace('/\[b\](.*?)\[\/b\]/i', '$1', $content);
    $content = preg_replace('/\[i\](.*?)\[\/i\]/i', '$1', $content);
    $content = preg_replace('/\[u\](.*?)\[\/u\]/i', '$1', $content);
    $content = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/i', '$2', $content);
    $content = preg_replace('/\[url\](.*?)\[\/url\]/i', '$1', $content);
    $content = preg_replace('/\[h[1-3]\](.*?)\[\/h[1-3]\]/i', '$1', $content);
    $content = preg_replace('/\[centre\](.*?)\[\/centre\]/i', '$1', $content);
    $content = preg_replace('/\[center\](.*?)\[\/center\]/i', '$1', $content);
    $content = preg_replace('/\[box\](.*?)\[\/box\]/i', '$1', $content);
    $content = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/i', '$2', $content);
    $content = preg_replace('/\[notice\](.*?)\[\/notice\]/i', '$1', $content);
    $content = preg_replace('/\[ul\](.*?)\[\/ul\]/is', '$1', $content);
    $content = preg_replace('/\[ol\](.*?)\[\/ol\]/is', '$1', $content);
    $content = preg_replace('/\[li\](.*?)\[\/li\]/i', '• $1', $content);
    $content = strip_tags($content);
    
    // Limit to 40-50 words
    $words = explode(' ', $content);
    if (count($words) > 45) {
        $words = array_slice($words, 0, 45);
        $html .= implode(' ', $words) . '...';
    } else {
        $html .= $content;
    }
    
    $html .= '
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