// BBCode Parser and Editor Functions

function parseBBCode(text) {
    // Bold
    text = text.replace(/\[b\](.*?)\[\/b\]/gi, '<strong>$1</strong>');
    
    // Italic
    text = text.replace(/\[i\](.*?)\[\/i\]/gi, '<em>$1</em>');
    
    // Underline
    text = text.replace(/\[u\](.*?)\[\/u\]/gi, '<u>$1</u>');
    
    // Links
    text = text.replace(/\[url=(.*?)\](.*?)\[\/url\]/gi, '<a href="$1" target="_blank" rel="noopener">$2</a>');
    text = text.replace(/\[url\](.*?)\[\/url\]/gi, '<a href="$1" target="_blank" rel="noopener">$1</a>');
    
    // Images
    text = text.replace(/\[img\](.*?)\[\/img\]/gi, '<img src="$1" alt="Image" style="max-width: 100%; max-height: 400px; height: auto; border-radius: 8px; object-fit: contain;">');
    
    // Headers
    text = text.replace(/\[h1\](.*?)\[\/h1\]/gi, '<h1>$1</h1>');
    text = text.replace(/\[h2\](.*?)\[\/h2\]/gi, '<h2>$1</h2>');
    text = text.replace(/\[h3\](.*?)\[\/h3\]/gi, '<h3>$1</h3>');
    
    // Lists
    text = text.replace(/\[ul\](.*?)\[\/ul\]/gis, '<ul>$1</ul>');
    text = text.replace(/\[ol\](.*?)\[\/ol\]/gis, '<ol>$1</ol>');
    text = text.replace(/\[li\](.*?)\[\/li\]/gi, '<li>$1</li>');
    
    // Center
    text = text.replace(/\[centre\](.*?)\[\/centre\]/gi, '<div class="bbcode-center">$1</div>');
    text = text.replace(/\[center\](.*?)\[\/center\]/gi, '<div class="bbcode-center">$1</div>');
    
    // Box
    text = text.replace(/\[box\](.*?)\[\/box\]/gi, '<div class="bbcode-box">$1</div>');
    
    // Color
    text = text.replace(/\[color=(.*?)\](.*?)\[\/color\]/gi, '<span style="color: $1;">$2</span>');
    
    // Notice
    text = text.replace(/\[notice\](.*?)\[\/notice\]/gi, '<div class="bbcode-notice">$1</div>');
    
    // Line breaks
    text = text.replace(/\n/g, '<br>');
    
    return text;
}

function parseBBCodeForPreview(text) {
    // Remove images for preview
    text = text.replace(/\[img\](.*?)\[\/img\]/gi, '[Image]');
    
    // Remove other BBCode tags but keep content
    text = text.replace(/\[b\](.*?)\[\/b\]/gi, '$1');
    text = text.replace(/\[i\](.*?)\[\/i\]/gi, '$1');
    text = text.replace(/\[u\](.*?)\[\/u\]/gi, '$1');
    text = text.replace(/\[url=(.*?)\](.*?)\[\/url\]/gi, '$2');
    text = text.replace(/\[url\](.*?)\[\/url\]/gi, '$1');
    text = text.replace(/\[h[1-3]\](.*?)\[\/h[1-3]\]/gi, '$1');
    text = text.replace(/\[centre\](.*?)\[\/centre\]/gi, '$1');
    text = text.replace(/\[center\](.*?)\[\/center\]/gi, '$1');
    text = text.replace(/\[box\](.*?)\[\/box\]/gi, '$1');
    text = text.replace(/\[color=(.*?)\](.*?)\[\/color\]/gi, '$2');
    text = text.replace(/\[notice\](.*?)\[\/notice\]/gi, '$1');
    text = text.replace(/\[ul\](.*?)\[\/ul\]/gis, '$1');
    text = text.replace(/\[ol\](.*?)\[\/ol\]/gis, '$1');
    text = text.replace(/\[li\](.*?)\[\/li\]/gi, '• $1');
    
    return text;
}

function insertBBCode(elementId, tag, hasClosing = true) {
    const element = document.getElementById(elementId);
    const start = element.selectionStart;
    const end = element.selectionEnd;
    const selectedText = element.value.substring(start, end);
    
    let insertText = '';
    
    switch(tag) {
        case 'b':
            insertText = `[b]${selectedText}[/b]`;
            break;
        case 'i':
            insertText = `[i]${selectedText}[/i]`;
            break;
        case 'u':
            insertText = `[u]${selectedText}[/u]`;
            break;
        case 'url':
            const url = prompt('Enter URL:');
            if (url) {
                insertText = selectedText ? `[url=${url}]${selectedText}[/url]` : `[url]${url}[/url]`;
            } else {
                return;
            }
            break;
        case 'img':
            const imgUrl = prompt('Enter image URL:');
            if (imgUrl) {
                insertText = `[img]${imgUrl}[/img]`;
            } else {
                return;
            }
            break;
        case 'h1':
            insertText = `[h1]${selectedText}[/h1]`;
            break;
        case 'h2':
            insertText = `[h2]${selectedText}[/h2]`;
            break;
        case 'h3':
            insertText = `[h3]${selectedText}[/h3]`;
            break;
        case 'ul':
            insertText = `[ul]\n[li]${selectedText || 'List item'}[/li]\n[/ul]`;
            break;
        case 'ol':
            insertText = `[ol]\n[li]${selectedText || 'List item'}[/li]\n[/ol]`;
            break;
        case 'li':
            insertText = `[li]${selectedText}[/li]`;
            break;
        case 'color':
            const color = prompt('Enter color (e.g., #ff0000, red):');
            if (color) {
                insertText = `[color=${color}]${selectedText}[/color]`;
            } else {
                return;
            }
            break;
        default:
            insertText = selectedText;
    }
    
    element.value = element.value.substring(0, start) + insertText + element.value.substring(end);
    element.focus();
    
    // Update character counter
    const countElement = document.getElementById(elementId.replace('-', '-') + '-count');
    if (countElement) {
        countElement.textContent = element.value.length;
    }
}

// Preview BBCode
function previewBBCode(sourceId, previewId) {
    const source = document.getElementById(sourceId);
    const preview = document.getElementById(previewId);
    
    if (source && preview) {
        preview.innerHTML = parseBBCode(source.value);
    }
}