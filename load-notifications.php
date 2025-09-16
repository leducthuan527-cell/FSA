<?php
require_once 'config/init.php';
require_once 'classes/Notification.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$notification = new Notification($db);
$notifications = $notification->getUserNotifications(getUserId(), $limit, $offset);
$total = $notification->getTotalCount(getUserId());
$has_more = ($offset + $limit) < $total;

echo json_encode([
    'success' => true,
    'notifications' => $notifications,
    'has_more' => $has_more
]);
?>