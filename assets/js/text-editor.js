// Text Editor Functionality

function formatText(elementId, format) {
    const element = document.getElementById(elementId);
    const start = element.selectionStart;
    const end = element.selectionEnd;
    const selectedText = element.value.substring(start, end);
    
    let formattedText = '';
    
    switch(format) {
        case 'bold':
            formattedText = `**${selectedText}**`;
            break;
        case 'italic':
            formattedText = `*${selectedText}*`;
            break;
        case 'underline':
            formattedText = `__${selectedText}__`;
            break;
        case 'h1':
            formattedText = `# ${selectedText}`;
            break;
        case 'h2':
            formattedText = `## ${selectedText}`;
            break;
        case 'ul':
            formattedText = `- ${selectedText}`;
            break;
        case 'ol':
            formattedText = `1. ${selectedText}`;
            break;
        default:
            formattedText = selectedText;
    }
    
    element.value = element.value.substring(0, start) + formattedText + element.value.substring(end);
    element.focus();
    element.setSelectionRange(start + formattedText.length, start + formattedText.length);
    
    // Update character counter
    if(elementId === 'title') {
        document.getElementById('title-count').textContent = element.value.length;
    } else if(elementId === 'content') {
        document.getElementById('content-count').textContent = element.value.length;
    }
}

function insertLink(elementId) {
    const element = document.getElementById(elementId);
    const start = element.selectionStart;
    const end = element.selectionEnd;
    const selectedText = element.value.substring(start, end);
    
    const url = prompt('Enter URL:');
    if(url) {
        const linkText = selectedText || 'Link';
        const formattedText = `[${linkText}](${url})`;
        
        element.value = element.value.substring(0, start) + formattedText + element.value.substring(end);
        element.focus();
        element.setSelectionRange(start + formattedText.length, start + formattedText.length);
        
        // Update character counter
        if(elementId === 'title') {
            document.getElementById('title-count').textContent = element.value.length;
        } else if(elementId === 'content') {
            document.getElementById('content-count').textContent = element.value.length;
        }
    }
}

// Preview media files
function previewMedia(input) {
    const preview = document.getElementById('media-preview');
    if(!preview) return;
    
    preview.innerHTML = '';
    
    if(input.files && input.files[0]) {
        const file = input.files[0];
        const fileType = file.type;
        
        if(fileType.startsWith('image/')) {
            const img = document.createElement('img');
            img.src = URL.createObjectURL(file);
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            preview.appendChild(img);
        } else if(fileType.startsWith('video/')) {
            const video = document.createElement('video');
            video.src = URL.createObjectURL(file);
            video.controls = true;
            video.style.maxWidth = '200px';
            video.style.maxHeight = '200px';
            preview.appendChild(video);
        } else if(fileType.startsWith('audio/')) {
            const audio = document.createElement('audio');
            audio.src = URL.createObjectURL(file);
            audio.controls = true;
            preview.appendChild(audio);
        }
    }
}