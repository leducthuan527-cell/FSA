class ContentModeration {
    constructor() {
        this.apiEndpoint = '/api/moderate-content.php';
    }

    async moderateText(text, contentType = 'comment', contentId = null) {
        try {
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    text: text,
                    content_type: contentType,
                    content_id: contentId
                })
            });

            const data = await response.json();

            return {
                success: response.ok,
                data: data,
                status: response.status
            };
        } catch (error) {
            console.error('Moderation error:', error);
            return {
                success: false,
                data: {
                    message: 'Network error. Please try again.'
                },
                status: 500
            };
        }
    }

    showModerationFeedback(element, message, type = 'error') {
        const existingFeedback = element.querySelector('.moderation-feedback');
        if (existingFeedback) {
            existingFeedback.remove();
        }

        const feedback = document.createElement('div');
        feedback.className = `moderation-feedback moderation-${type}`;
        feedback.textContent = message;

        element.appendChild(feedback);

        if (type === 'success') {
            setTimeout(() => {
                feedback.remove();
            }, 3000);
        }
    }

    clearModerationFeedback(element) {
        const feedback = element.querySelector('.moderation-feedback');
        if (feedback) {
            feedback.remove();
        }
    }

    setLoadingState(button, isLoading) {
        if (isLoading) {
            button.disabled = true;
            button.dataset.originalText = button.textContent;
            button.textContent = 'Checking content...';
            button.classList.add('loading');
        } else {
            button.disabled = false;
            button.textContent = button.dataset.originalText || 'Submit';
            button.classList.remove('loading');
        }
    }
}

const contentModeration = new ContentModeration();
