function createCommentElement(commentData) {
    const commentItem = document.createElement('div');
    commentItem.className = 'comment-item';
    commentItem.setAttribute('data-comment-id', commentData.id);

    const starsHTML = Array(5).fill(0).map((_, index) => {
        const starType = index < commentData.rating ? 'star.svg' : 'star_w.svg';
        return `<img src="/assets/icons/${starType}" alt="star">`;
    }).join('');

    const timeAgo = formatTimeAgo(new Date(commentData.timestamp));

    commentItem.innerHTML = `
        <div class="comment-header">
            <div class="comment-user-avatar">
                <img src="${commentData.userAvatar || '/assets/icons/user.svg'}" alt="user">
            </div>
            <div class="comment-user-info">
                <p class="comment-user-name">${commentData.userName}</p>
                <div class="comment-rating">${starsHTML}</div>
            </div>
        </div>
        <div class="comment-text">
            <p>${commentData.comment}</p>
        </div>
        <div class="comment-time">${timeAgo}</div>
    `;

    return commentItem;
}

function formatTimeAgo(date) {
    const diffInSeconds = Math.floor((new Date() - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 604800)} weeks ago`;
    return `${Math.floor(diffInSeconds / 2592000)} months ago`;
}

async function loadCommentsFromDatabase(eventId) {
    try {
        const response = await fetch(`/api/events/${eventId}/comments`);
        if (!response.ok) throw new Error('Failed to fetch comments');
        return await response.json();
    } catch (error) {
        console.error('Error loading comments:', error);
        return [];
    }
}

function renderComments(comments) {
    const container = document.getElementById('comments-container');
    if (!container) {
        console.error('Comments container not found');
        return;
    }

    container.innerHTML = '';

    if (comments.length === 0) {
        container.innerHTML = `
            <div class="no-comments">
                <p>No comments yet. Be the first to share your experience!</p>
            </div>
        `;
        return;
    }

    comments.forEach(commentData => {
        const commentElement = createCommentElement(commentData);
        container.appendChild(commentElement);
    });
}

async function addComment(eventId, commentData) {
    try {
        const response = await fetch(`/api/events/${eventId}/comments`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(commentData)
        });

        if (!response.ok) throw new Error('Failed to add comment');

        const newComment = await response.json();
        const container = document.getElementById('comments-container');
        const commentElement = createCommentElement(newComment);
        container.insertBefore(commentElement, container.firstChild);

        return newComment;
    } catch (error) {
        console.error('Error adding comment:', error);
        throw error;
    }
}

if (typeof module !== 'undefined' && module.exports) {
    module.exports = { createCommentElement, formatTimeAgo, loadCommentsFromDatabase, renderComments, addComment };
}
