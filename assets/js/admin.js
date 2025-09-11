// Admin Panel JavaScript

document.addEventListener('DOMContentLoaded', function() {
    // Navigation functionality
    const navLinks = document.querySelectorAll('.nav-link');
    const sections = document.querySelectorAll('.admin-section');
    
    navLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.getAttribute('href').startsWith('#')) {
                e.preventDefault();
                
                // Remove active class from all links and sections
                navLinks.forEach(l => l.classList.remove('active'));
                sections.forEach(s => s.classList.remove('active'));
                
                // Add active class to clicked link
                this.classList.add('active');
                
                // Show corresponding section
                const targetId = this.getAttribute('href').substring(1);
                const targetSection = document.getElementById(targetId);
                if (targetSection) {
                    targetSection.classList.add('active');
                }
            }
        });
    });
    
    // Auto-submit forms when select changes
    const autoSubmitSelects = document.querySelectorAll('select[onchange*="submit"]');
    autoSubmitSelects.forEach(select => {
        select.addEventListener('change', function() {
            if (confirm('Are you sure you want to change this user\'s status?')) {
                this.form.submit();
            } else {
                // Reset to original value
                this.selectedIndex = 0;
            }
        });
    });
    
    // Confirmation for report actions
    const reportForms = document.querySelectorAll('form[action*="report"]');
    reportForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const action = form.querySelector('input[name="report_action"]').value;
            const message = action === 'hide' ? 
                'Are you sure you want to hide this content?' : 
                'Are you sure you want to dismiss this report?';
            
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
    
    // Statistics animation
    const statNumbers = document.querySelectorAll('.stat-number');
    statNumbers.forEach(stat => {
        const finalValue = parseInt(stat.textContent);
        let currentValue = 0;
        const increment = Math.ceil(finalValue / 20);
        
        const timer = setInterval(() => {
            currentValue += increment;
            if (currentValue >= finalValue) {
                currentValue = finalValue;
                clearInterval(timer);
            }
            stat.textContent = currentValue;
        }, 50);
    });
    
    // Search functionality
    function addSearchToSection(sectionId, searchInputId) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        
        const searchInput = document.createElement('input');
        searchInput.type = 'text';
        searchInput.id = searchInputId;
        searchInput.placeholder = 'Search...';
        searchInput.style.marginBottom = '1rem';
        searchInput.style.padding = '0.5rem';
        searchInput.style.border = '1px solid #d1d5db';
        searchInput.style.borderRadius = '4px';
        searchInput.style.width = '300px';
        
        const title = section.querySelector('h1');
        title.parentNode.insertBefore(searchInput, title.nextSibling);
        
        searchInput.addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            const items = section.querySelectorAll('.admin-item');
            
            items.forEach(item => {
                const text = item.textContent.toLowerCase();
                if (text.includes(searchTerm)) {
                    item.style.display = 'block';
                } else {
                    item.style.display = 'none';
                }
            });
        });
    }
    
    // Add search to different sections
    addSearchToSection('posts', 'search-posts');
    addSearchToSection('comments', 'search-comments');
    addSearchToSection('reports', 'search-reports');
    addSearchToSection('users', 'search-users');
    
    // Bulk actions
    function addBulkActions(sectionId) {
        const section = document.getElementById(sectionId);
        if (!section) return;
        
        const items = section.querySelectorAll('.admin-item');
        if (items.length === 0) return;
        
        // Add checkboxes to items
        items.forEach((item, index) => {
            const checkbox = document.createElement('input');
            checkbox.type = 'checkbox';
            checkbox.className = 'bulk-checkbox';
            checkbox.value = index;
            checkbox.style.marginRight = '0.5rem';
            
            const header = item.querySelector('.item-header');
            header.insertBefore(checkbox, header.firstChild);
        });
        
        // Add bulk action controls
        const bulkControls = document.createElement('div');
        bulkControls.className = 'bulk-controls';
        bulkControls.style.marginBottom = '1rem';
        bulkControls.style.padding = '1rem';
        bulkControls.style.background = '#f8fafc';
        bulkControls.style.borderRadius = '4px';
        bulkControls.style.display = 'none';
        
        const selectAllCheckbox = document.createElement('input');
        selectAllCheckbox.type = 'checkbox';
        selectAllCheckbox.id = 'select-all-' + sectionId;
        
        const selectAllLabel = document.createElement('label');
        selectAllLabel.htmlFor = 'select-all-' + sectionId;
        selectAllLabel.textContent = 'Select All';
        selectAllLabel.style.marginLeft = '0.5rem';
        selectAllLabel.style.marginRight = '1rem';
        
        bulkControls.appendChild(selectAllCheckbox);
        bulkControls.appendChild(selectAllLabel);
        
        const title = section.querySelector('h1');
        title.parentNode.insertBefore(bulkControls, title.nextSibling);
        
        // Select all functionality
        selectAllCheckbox.addEventListener('change', function() {
            const checkboxes = section.querySelectorAll('.bulk-checkbox');
            checkboxes.forEach(cb => cb.checked = this.checked);
        });
        
        // Show/hide bulk controls
        const checkboxes = section.querySelectorAll('.bulk-checkbox');
        checkboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const checkedBoxes = section.querySelectorAll('.bulk-checkbox:checked');
                if (checkedBoxes.length > 0) {
                    bulkControls.style.display = 'block';
                } else {
                    bulkControls.style.display = 'none';
                }
            });
        });
    }
    
    // Add bulk actions to sections
    addBulkActions('posts');
    addBulkActions('comments');
    addBulkActions('reports');
    
    // Keyboard shortcuts
    document.addEventListener('keydown', function(e) {
        // Alt + 1-5 for quick navigation
        if (e.altKey && e.key >= '1' && e.key <= '5') {
            e.preventDefault();
            const navLinks = document.querySelectorAll('.nav-link[href^="#"]');
            const index = parseInt(e.key) - 1;
            if (navLinks[index]) {
                navLinks[index].click();
            }
        }
        
        // Ctrl/Cmd + K for search
        if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
            e.preventDefault();
            const activeSection = document.querySelector('.admin-section.active');
            if (activeSection) {
                const searchInput = activeSection.querySelector('input[type="text"]');
                if (searchInput) {
                    searchInput.focus();
                }
            }
        }
    });
    
    // Auto-refresh for real-time updates
    let autoRefreshInterval;
    
    function startAutoRefresh() {
        autoRefreshInterval = setInterval(() => {
            // Only refresh if user is active (not idle)
            if (document.hasFocus()) {
                location.reload();
            }
        }, 30000); // Refresh every 30 seconds
    }
    
    function stopAutoRefresh() {
        if (autoRefreshInterval) {
            clearInterval(autoRefreshInterval);
        }
    }
    
    // Start auto-refresh
    startAutoRefresh();
    
    // Stop auto-refresh when page is not visible
    document.addEventListener('visibilitychange', function() {
        if (document.hidden) {
            stopAutoRefresh();
        } else {
            startAutoRefresh();
        }
    });
    
    // Notification system for admin actions
    function showNotification(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.textContent = message;
        notification.style.position = 'fixed';
        notification.style.top = '20px';
        notification.style.right = '20px';
        notification.style.padding = '1rem';
        notification.style.borderRadius = '4px';
        notification.style.zIndex = '1000';
        notification.style.maxWidth = '300px';
        
        if (type === 'success') {
            notification.style.background = '#16a34a';
            notification.style.color = 'white';
        } else if (type === 'error') {
            notification.style.background = '#dc2626';
            notification.style.color = 'white';
        }
        
        document.body.appendChild(notification);
        
        setTimeout(() => {
            notification.remove();
        }, 3000);
    }
    
    // Show notifications for form submissions
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('success')) {
        showNotification('Action completed successfully!', 'success');
    } else if (urlParams.get('error')) {
        showNotification('An error occurred. Please try again.', 'error');
    }
});