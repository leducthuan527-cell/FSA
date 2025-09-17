<?php
function parseBBCodeContent($text) {
    // Bold
    $text = preg_replace('/\[b\](.*?)\[\/b\]/is', '<strong>$1</strong>', $text);
    
    // Italic
    $text = preg_replace('/\[i\](.*?)\[\/i\]/is', '<em>$1</em>', $text);
    
    // Underline
    $text = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $text);
    
    // Links
    $text = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/is', '<a href="$1" target="_blank" rel="noopener">$2</a>', $text);
    $text = preg_replace('/\[url\](.*?)\[\/url\]/is', '<a href="$1" target="_blank" rel="noopener">$1</a>', $text);
    
    // Color
    $text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/is', '<span style="color: $1;">$2</span>', $text);
    
    // Headers
    $text = preg_replace('/\[h1\](.*?)\[\/h1\]/is', '<h1>$1</h1>', $text);
    $text = preg_replace('/\[h2\](.*?)\[\/h2\]/is', '<h2>$1</h2>', $text);
    $text = preg_replace('/\[h3\](.*?)\[\/h3\]/is', '<h3>$1</h3>', $text);
    
    // Center
    $text = preg_replace('/\[centre\](.*?)\[\/centre\]/is', '<div class="bbcode-center">$1</div>', $text);
    $text = preg_replace('/\[center\](.*?)\[\/center\]/is', '<div class="bbcode-center">$1</div>', $text);
    
    // Box
    $text = preg_replace('/\[box\](.*?)\[\/box\]/is', '<div class="bbcode-box">$1</div>', $text);
    
    // Notice
    $text = preg_replace('/\[notice\](.*?)\[\/notice\]/is', '<div class="bbcode-notice">$1</div>', $text);
    
    // Images
    $text = preg_replace('/\[img\](.*?)\[\/img\]/is', '<img src="$1" alt="Image" style="max-width: 100%; max-height: 400px; height: auto; border-radius: 8px; object-fit: contain;">', $text);
    
    // Lists
    $text = preg_replace('/\[ul\](.*?)\[\/ul\]/is', '<ul>$1</ul>', $text);
    $text = preg_replace('/\[ol\](.*?)\[\/ol\]/is', '<ol>$1</ol>', $text);
    $text = preg_replace('/\[li\](.*?)\[\/li\]/is', '<li>$1</li>', $text);
    
    // Line breaks
    $text = nl2br($text);
    
    return $text;
}

function parseBBCodeForPreview($text) {
    // Remove BBCode tags but keep content for preview
    $text = preg_replace('/\[b\](.*?)\[\/b\]/is', '$1', $text);
    $text = preg_replace('/\[i\](.*?)\[\/i\]/is', '$1', $text);
    $text = preg_replace('/\[u\](.*?)\[\/u\]/is', '$1', $text);
    $text = preg_replace('/\[url=(.*?)\](.*?)\[\/url\]/is', '$2', $text);
    $text = preg_replace('/\[url\](.*?)\[\/url\]/is', '$1', $text);
    $text = preg_replace('/\[color=(.*?)\](.*?)\[\/color\]/is', '$2', $text);
    $text = preg_replace('/\[img\](.*?)\[\/img\]/is', '[Image]', $text);
    $text = preg_replace('/\[h[1-3]\](.*?)\[\/h[1-3]\]/is', '$1', $text);
    $text = preg_replace('/\[centre\](.*?)\[\/centre\]/is', '$1', $text);
    $text = preg_replace('/\[center\](.*?)\[\/center\]/is', '$1', $text);
    $text = preg_replace('/\[box\](.*?)\[\/box\]/is', '$1', $text);
    $text = preg_replace('/\[notice\](.*?)\[\/notice\]/is', '$1', $text);
    $text = preg_replace('/\[ul\](.*?)\[\/ul\]/is', '$1', $text);
    $text = preg_replace('/\[ol\](.*?)\[\/ol\]/is', '$1', $text);
    $text = preg_replace('/\[li\](.*?)\[\/li\]/is', '• $1', $text);
    
    return $text;
}
?>