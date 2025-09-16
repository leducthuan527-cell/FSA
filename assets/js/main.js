// Main JavaScript functionality

// Initialize hero background on all pages
document.addEventListener('DOMContentLoaded', function() {
    // Add hero background to all pages except index
    if (!document.body.classList.contains('hero-page')) {
        addHeroBackground();
    }
    
    // Initialize other functionality
    initializeTextareas();
    initializeSmoothScrolling();
    initializeFormValidation();
    initializeAlerts();
    initializeCharacterCounters();
    initializeDeleteConfirmations();
    initializeFormLoading();
});

function addHeroBackground() {
    const heroBackground = document.createElement('div');
    heroBackground.className = 'hero-background';
    heroBackground.innerHTML = `
        <div class="hero-background-gradient"></div>
        <div class="hero-shapes-container">
            <div class="elegant-shape shape-1" style="--rotate: 12deg;">
                <div class="elegant-shape-inner">
                    <div class="elegant-shape-element"></div>
                </div>
            </div>
            <div class="elegant-shape shape-2" style="--rotate: -15deg;">
                <div class="elegant-shape-inner">
                    <div class="elegant-shape-element"></div>
                </div>
            </div>
            <div class="elegant-shape shape-3" style="--rotate: -8deg;">
                <div class="elegant-shape-inner">
                    <div class="elegant-shape-element"></div>
                </div>
            </div>
            <div class="elegant-shape shape-4" style="--rotate: 20deg;">
                <div class="elegant-shape-inner">
                    <div class="elegant-shape-element"></div>
                </div>
            </div>
            <div class="elegant-shape shape-5" style="--rotate: -25deg;">
                <div class="elegant-shape-inner">
                    <div class="elegant-shape-element"></div>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(heroBackground);
}

// AJAX Delete functionality
function deletePostAjax(postId, callback) {
    const formData = new FormData();
    formData.append('action', 'delete_post');
    formData.append('post_id', postId);
    
    fetch('profile.php?id=' + getCurrentUserId(), {
        method: 'POST',
        body: formData
    })
    .then(response => response.text())
    .then(data => {
        if (callback) callback(true);
    })
    .catch(error => {
        console.error('Error:', error);
        if (callback) callback(false);
    });
}

function getCurrentUserId() {
    // Extract user ID from URL or other source
    const urlParams = new URLSearchParams(window.location.search);
    return urlParams.get('id');
}

// Password visibility toggle
function togglePasswordVisibility(inputId, toggleBtn) {
    const input = document.getElementById(inputId);
    const icon = toggleBtn.querySelector('svg');
    
    if (input.type === 'password') {
        input.type = 'text';
        icon.innerHTML = `
            <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"></path>
            <line x1="1" y1="1" x2="23" y2="23"></line>
        `;
    } else {
        input.type = 'password';
        icon.innerHTML = `
            <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
            <circle cx="12" cy="12" r="3"></circle>
        `;
    }
}

// Report content functionality
function reportContent(type, id) {
    const reason = prompt('Please provide a reason for reporting this ' + type + ':');
    if (reason && reason.trim()) {
        // Create form and submit
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = 'report.php';
        form.style.display = 'none';
        
        const typeInput = document.createElement('input');
        typeInput.type = 'hidden';
        typeInput.name = 'type';
        typeInput.value = type;
        
        const idInput = document.createElement('input');
        idInput.type = 'hidden';
        idInput.name = 'id';
        idInput.value = id;
        
        const reasonInput = document.createElement('input');
        reasonInput.type = 'hidden';
        reasonInput.name = 'reason';
        reasonInput.value = reason;
        
        form.appendChild(typeInput);
        form.appendChild(idInput);
        form.appendChild(reasonInput);
        
        document.body.appendChild(form);
        form.submit();
    }
}

// Auto-resize textareas
function initializeTextareas() {
    const textareas = document.querySelectorAll('textarea');
    textareas.forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = this.scrollHeight + 'px';
        });
    });
}

    // Smooth scrolling for anchor links
function initializeSmoothScrolling() {
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });
}

    // Form validation
function initializeFormValidation() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const requiredFields = form.querySelectorAll('[required]');
            let isValid = true;
            
            requiredFields.forEach(field => {
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc2626';
                    isValid = false;
                } else {
                    field.style.borderColor = '#d1d5db';
                }
            });
            
            if (!isValid) {
                e.preventDefault();
                alert('Please fill in all required fields.');
            }
        });
    });
}

    // Auto-hide alerts after 5 seconds
function initializeAlerts() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            setTimeout(() => {
                alert.remove();
            }, 300);
        }, 5000);
    });
}

// Character counter for textareas
function addCharacterCounter(textarea, maxLength) {
    const counter = document.createElement('div');
    counter.className = 'character-counter';
    counter.style.textAlign = 'right';
    counter.style.fontSize = '0.8rem';
    counter.style.color = '#64748b';
    counter.style.marginTop = '0.25rem';
    
    function updateCounter() {
        const remaining = maxLength - textarea.value.length;
        counter.textContent = `${remaining} characters remaining`;
        
        if (remaining < 0) {
            counter.style.color = '#dc2626';
        } else if (remaining < 50) {
            counter.style.color = '#d97706';
        } else {
            counter.style.color = '#64748b';
        }
    }
    
    textarea.addEventListener('input', updateCounter);
    textarea.parentNode.appendChild(counter);
    updateCounter();
}

// Initialize character counters
function initializeCharacterCounters() {
    const postContent = document.querySelector('#content');
    if (postContent) {
        addCharacterCounter(postContent, 5000);
    }
    
    const commentTextareas = document.querySelectorAll('.comment-form textarea');
    commentTextareas.forEach(textarea => {
        addCharacterCounter(textarea, 1000);
    });
}

// Image preview for avatar uploads
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// Confirmation dialogs for destructive actions
function confirmAction(message) {
    return confirm(message || 'Are you sure you want to perform this action?');
}

// Add confirmation to delete buttons
function initializeDeleteConfirmations() {
    const deleteButtons = document.querySelectorAll('.btn-danger');
    deleteButtons.forEach(button => {
        if (button.textContent.toLowerCase().includes('delete') || 
            button.textContent.toLowerCase().includes('remove')) {
            button.addEventListener('click', function(e) {
                if (!confirmAction('Are you sure you want to delete this item? This action cannot be undone.')) {
                    e.preventDefault();
                }
            });
        }
    });
}

// Loading states for forms
function showLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = true;
        submitButton.textContent = 'Loading...';
    }
}

function hideLoading(form) {
    const submitButton = form.querySelector('button[type="submit"]');
    if (submitButton) {
        submitButton.disabled = false;
        submitButton.textContent = submitButton.dataset.originalText || 'Submit';
    }
}

// Add loading states to forms
function initializeFormLoading() {
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        const submitButton = form.querySelector('button[type="submit"]');
        if (submitButton) {
            submitButton.dataset.originalText = submitButton.textContent;
        }
        
        form.addEventListener('submit', function() {
            showLoading(form);
        });
    });
}