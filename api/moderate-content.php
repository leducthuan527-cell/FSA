<?php
require_once '../config/init.php';
require_once '../classes/ContentModerator.php';

header('Content-Type: application/json');

error_log('=== MODERATION API ENDPOINT CALLED ===');
error_log('Method: ' . $_SERVER['REQUEST_METHOD']);
error_log('Is logged in: ' . (isLoggedIn() ? 'yes' : 'no'));

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed']);
    exit;
}

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
error_log('Input received: ' . json_encode($input));

if (!isset($input['text']) || empty(trim($input['text']))) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Content text is required']);
    exit;
}

$text = trim($input['text']);
$content_type = $input['content_type'] ?? 'comment';
$content_id = $input['content_id'] ?? null;
$user_id = getUserId();

error_log('MODERATION: Checking ' . $content_type . ' from user ' . $user_id . ': ' . substr($text, 0, 50));

$moderator = new ContentModerator();
$result = $moderator->moderateContent($text, $user_id, $content_type, $content_id);

error_log('MODERATION RESULT: ' . json_encode($result));

if (!$result['success']) {
    http_response_code(500);
    echo json_encode($result);
    exit;
}

if ($result['is_flagged']) {
    http_response_code(202);
}

echo json_encode($result);
?>
