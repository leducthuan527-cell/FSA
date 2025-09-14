// Time ago functionality

function timeAgo(dateString) {
    const now = new Date();
    const date = new Date(dateString);
    const diffInSeconds = Math.floor((now - date) / 1000);
    
    if (diffInSeconds < 60) {
        return diffInSeconds <= 1 ? '1 second ago' : `${diffInSeconds} seconds ago`;
    }
    
    const diffInMinutes = Math.floor(diffInSeconds / 60);
    if (diffInMinutes < 60) {
        return diffInMinutes === 1 ? '1 minute ago' : `${diffInMinutes} minutes ago`;
    }
    
    const diffInHours = Math.floor(diffInMinutes / 60);
    if (diffInHours < 24) {
        return diffInHours === 1 ? '1 hour ago' : `${diffInHours} hours ago`;
    }
    
    const diffInDays = Math.floor(diffInHours / 24);
    if (diffInDays < 30) {
        return diffInDays === 1 ? '1 day ago' : `${diffInDays} days ago`;
    }
    
    const diffInMonths = Math.floor(diffInDays / 30);
    if (diffInMonths < 12) {
        return diffInMonths === 1 ? '1 month ago' : `${diffInMonths} months ago`;
    }
    
    const diffInYears = Math.floor(diffInMonths / 12);
    return diffInYears === 1 ? '1 year ago' : `${diffInYears} years ago`;
}

function formatDateTime(dateString) {
    const date = new Date(dateString);
    return date.toLocaleString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
        second: '2-digit'
    });
}

// Initialize time ago elements
document.addEventListener('DOMContentLoaded', function() {
    const timeElements = document.querySelectorAll('.time-ago');
    
    timeElements.forEach(element => {
        const dateTime = element.getAttribute('data-datetime');
        if (dateTime) {
            element.textContent = timeAgo(dateTime);
            element.title = formatDateTime(dateTime);
        }
    });
    
    // Update every minute
    setInterval(() => {
        timeElements.forEach(element => {
            const dateTime = element.getAttribute('data-datetime');
            if (dateTime) {
                element.textContent = timeAgo(dateTime);
            }
        });
    }, 60000);
});