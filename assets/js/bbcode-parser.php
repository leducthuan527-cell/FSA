<?php
function parseBBCodeContent($text) {
    // Bold
    $text = preg_replace('/\[b\](.*?)\[\/b\]/i', '<strong>$1</strong>', $text);
    
    // Italic
    $text = preg_replace('/\[i\](.*?)\[\/i\]/i', '<em>$1</em>', $text);
    
    // Underline
    $text = preg_replace('/\[u\](.*?)\[\/u\]/i', '<u>$1</u>', $text);
    
    // Links
    $text = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/i', '<a href="$1" target="_blank" rel="noopener">$2</a>', $text);
    $text = preg_replace('/\[url\](.*?)\[\/url\]/i', '<a href="$1" target="_blank" rel="noopener">$1</a>', $text);
    
    // Color
    $text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/i', '<span style="color: $1;">$2</span>', $text);
    
    // Line breaks
    $text = nl2br($text);
    
    return $text;
}

function parseBBCodeForPreview($text) {
    // Remove BBCode tags but keep content for preview
    $text = preg_replace('/\[b\](.*?)\[\/b\]/i', '$1', $text);
    $text = preg_replace('/\[i\](.*?)\[\/i\]/i', '$1', $text);
    $text = preg_replace('/\[u\](.*?)\[\/u\]/i', '$1', $text);
    $text = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/i', '$2', $text);
    $text = preg_replace('/\[url\](.*?)\[\/url\]/i', '$1', $text);
    $text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/i', '$2', $text);
    $text = preg_replace('/\[img\](.*?)\[\/img\]/i', '[Image]', $text);
    $text = preg_replace('/\[h[1-3]\](.*?)\[\/h[1-3]\]/i', '$1', $text);
    $text = preg_replace('/\[centre\](.*?)\[\/centre\]/i', '$1', $text);
    $text = preg_replace('/\[center\](.*?)\[\/center\]/i', '$1', $text);
    $text = preg_replace('/\[box\](.*?)\[\/box\]/i', '$1', $text);
    $text = preg_replace('/\[notice\](.*?)\[\/notice\]/i', '$1', $text);
    $text = preg_replace('/\[ul\](.*?)\[\/ul\]/is', '$1', $text);
    $text = preg_replace('/\[ol\](.*?)\[\/ol\]/is', '$1', $text);
    $text = preg_replace('/\[li\](.*?)\[\/li\]/i', '• $1', $text);
    
    return $text;
}
?>