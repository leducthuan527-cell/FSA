<?php
require_once 'config/init.php';
require_once 'classes/Notification.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

$notification = new Notification($db);
$success = $notification->markAsRead(getUserId());

echo json_encode(['success' => $success]);
?>