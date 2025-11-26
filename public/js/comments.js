// Comments Management System
// This file provides functions to load and display comments from a database

/**
 * Database structure for comments:
 * {
 *   id: number,
 *   userId: number,
 *   userName: string,
 *   userAvatar: string (URL),
 *   rating: number (1-5),
 *   comment: string,
 *   timestamp: Date,
 *   eventId: number
 * }
 */

// Function to create a comment element from data
function createCommentElement(commentData) {
    const commentItem = document.createElement('div');
    commentItem.className = 'comment-item';
    commentItem.setAttribute('data-comment-id', commentData.id);

    // Create stars based on rating
    const starsHTML = Array(5).fill(0).map((_, index) => {
        const starType = index < commentData.rating ? 'star.svg' : 'star_w.svg';
        return `<img src="/assets/icons/${starType}" alt="star">`;
    }).join('');

    // Format timestamp (you can customize this)
    const timeAgo = formatTimeAgo(new Date(commentData.timestamp));

    commentItem.innerHTML = `
        <div class="comment-header">
            <div class="comment-user-avatar">
                <img src="${commentData.userAvatar || '/assets/icons/user.svg'}" alt="user">
            </div>
            <div class="comment-user-info">
                <p class="comment-user-name">${commentData.userName}</p>
                <div class="comment-rating">
                    ${starsHTML}
                </div>
            </div>
        </div>
        <div class="comment-text">
            <p>${commentData.comment}</p>
        </div>
        <div class="comment-time">${timeAgo}</div>
    `;

    return commentItem;
}

// Function to format timestamp to "time ago" format
function formatTimeAgo(date) {
    const now = new Date();
    const diffInSeconds = Math.floor((now - date) / 1000);

    if (diffInSeconds < 60) return 'Just now';
    if (diffInSeconds < 3600) return `${Math.floor(diffInSeconds / 60)} minutes ago`;
    if (diffInSeconds < 86400) return `${Math.floor(diffInSeconds / 3600)} hours ago`;
    if (diffInSeconds < 604800) return `${Math.floor(diffInSeconds / 86400)} days ago`;
    if (diffInSeconds < 2592000) return `${Math.floor(diffInSeconds / 604800)} weeks ago`;
    return `${Math.floor(diffInSeconds / 2592000)} months ago`;
}

// Function to load comments from database (API call)
async function loadCommentsFromDatabase(eventId) {
    try {
        // Replace this URL with your actual API endpoint
        const response = await fetch(`/api/events/${eventId}/comments`);

        if (!response.ok) {
            throw new Error('Failed to fetch comments');
        }

        const comments = await response.json();
        return comments;
    } catch (error) {
        console.error('Error loading comments:', error);
        return [];
    }
}

// Function to render comments to the page
function renderComments(comments) {
    const container = document.getElementById('comments-container');

    if (!container) {
        console.error('Comments container not found');
        return;
    }

    // Clear existing comments (if any)
    container.innerHTML = '';

    // If no comments, show a message
    if (comments.length === 0) {
        container.innerHTML = `
            <div class="no-comments">
                <p>No comments yet. Be the first to share your experience!</p>
            </div>
        `;
        return;
    }

    // Create and append comment elements
    comments.forEach(commentData => {
        const commentElement = createCommentElement(commentData);
        container.appendChild(commentElement);
    });
}

// Function to add a new comment (POST to database)
async function addComment(eventId, commentData) {
    try {
        // Replace this URL with your actual API endpoint
        const response = await fetch(`/api/events/${eventId}/comments`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify(commentData)
        });

        if (!response.ok) {
            throw new Error('Failed to add comment');
        }

        const newComment = await response.json();

        // Add the new comment to the DOM
        const container = document.getElementById('comments-container');
        const commentElement = createCommentElement(newComment);
        container.insertBefore(commentElement, container.firstChild);

        return newComment;
    } catch (error) {
        console.error('Error adding comment:', error);
        throw error;
    }
}

// Example usage when page loads:
// document.addEventListener('DOMContentLoaded', async () => {
//     const eventId = getEventIdFromURL(); // Implement this based on your routing
//     const comments = await loadCommentsFromDatabase(eventId);
//     renderComments(comments);
// });

// Export functions for use in other files (if using modules)
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        createCommentElement,
        formatTimeAgo,
        loadCommentsFromDatabase,
        renderComments,
        addComment
    };
}
