<?php
session_start();
require_once 'database.php';

// Initialize database connection
$database = new Database();
$db = $database->getConnection();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
}

function getUserId() {
    return isset($_SESSION['user_id']) ? (int)$_SESSION['user_id'] : null;
}


function sanitizeInput($data) {
    return htmlspecialchars(strip_tags(trim($data)));
}

function redirect($url) {
    header("Location: $url");
    exit();
}

function formatDate($date) {
    return date('M j, Y g:i A', strtotime($date));
}
?>