<?php
require_once 'config/init.php';
require_once 'classes/Report.php';
require_once 'classes/Post.php';
require_once 'classes/Comment.php';


ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if(!isLoggedIn()) {
    redirect('auth/login.php');
}

if($_SERVER['REQUEST_METHOD'] === 'POST') {
    $type = sanitizeInput($_POST['type']);
    $id = (int)$_POST['id'];
    $reason = sanitizeInput($_POST['reason']);
    
    if(empty($reason)) {
        redirect('index.php');
    }

    if ($type === 'post') {
    $post = new Post($db);
    $post_data = $post->getPostById($id);
    if ($post_data && $post_data['user_id'] == getUserId()) {
        redirect('index.php?message=' . urlencode("You cannot report your own post."));
    }
}

if ($type === 'comment') {
    $comment = new Comment($db);
    $comment_data = $comment->getCommentById($id);
    if ($comment_data && $comment_data['user_id'] == getUserId()) {
        redirect('index.php?message=' . urlencode("You cannot report your own comment."));
    }
}
    
    $report = new Report($db);
    if($report->create(getUserId(), $type, $id, $reason)) {
        // Content is automatically hidden for the reporter
        $message = "Content reported successfully. It has been hidden from your view.";
    } else {
        $message = "You have already reported this content.";
    }
    
    // Redirect back to the referring page with message
    $referer = $_SERVER['HTTP_REFERER'] ?? 'index.php';
    redirect($referer . (strpos($referer, '?') ? '&' : '?') . 'message=' . urlencode($message));
}

redirect('index.php');
?>