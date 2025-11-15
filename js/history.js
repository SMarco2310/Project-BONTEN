// History page JavaScript - handles Cancel RSVP and Write Review functionality

document.addEventListener('DOMContentLoaded', () => {
    // Get all cancel and review buttons
    const cancelButtons = document.querySelectorAll('.cancel-btn');
    const reviewButtons = document.querySelectorAll('.review-btn');

    // Cancel RSVP Modal Elements
    const cancelModal = document.getElementById('cancel-modal');
    const cancelModalOverlay = cancelModal?.querySelector('.modal-overlay');
    const cancelModalClose = cancelModal?.querySelector('.modal-close');
    const cancelConfirmBtn = document.getElementById('cancel-confirm-btn');
    const cancelCancelBtn = document.getElementById('cancel-cancel-btn');

    // Review Modal Elements
    const reviewModal = document.getElementById('review-modal');
    const reviewModalOverlay = reviewModal?.querySelector('.modal-overlay');
    const reviewModalClose = reviewModal?.querySelector('.modal-close');
    const submitReviewBtn = document.getElementById('submit-review-btn');
    const reviewForm = document.getElementById('review-form');

    // Star rating elements
    const starRatingContainer = document.getElementById('star-rating');
    let selectedRating = 0;

    // Current event ID for tracking
    let currentEventId = null;

    // ========== Cancel RSVP Functionality ==========

    // Open cancel modal when cancel button is clicked
    cancelButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent card click
            const card = button.closest('.history-card');
            currentEventId = card?.getAttribute('data-event-id');
            const eventName = card?.querySelector('.event-name')?.textContent || 'this event';

            // Update modal with event name
            const eventNameSpan = document.getElementById('cancel-event-name');
            if (eventNameSpan) {
                eventNameSpan.textContent = eventName;
            }

            // Show modal
            if (cancelModal) {
                cancelModal.style.display = 'flex';
                document.body.style.overflow = 'hidden'; // Prevent background scroll
            }
        });
    });

    // Close cancel modal
    const closeCancelModal = () => {
        if (cancelModal) {
            cancelModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            currentEventId = null;
        }
    };

    if (cancelModalClose) {
        cancelModalClose.addEventListener('click', closeCancelModal);
    }

    if (cancelModalOverlay) {
        cancelModalOverlay.addEventListener('click', closeCancelModal);
    }

    if (cancelCancelBtn) {
        cancelCancelBtn.addEventListener('click', closeCancelModal);
    }

    // Confirm cancellation
    if (cancelConfirmBtn) {
        cancelConfirmBtn.addEventListener('click', () => {
            if (currentEventId) {
                // Here you would make an API call to cancel the RSVP
                console.log('Cancelling RSVP for event:', currentEventId);

                // Remove the event card from the page
                const card = document.querySelector(`[data-event-id="${currentEventId}"]`);
                if (card) {
                    card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
                    card.style.opacity = '0';
                    card.style.transform = 'scale(0.9)';

                    setTimeout(() => {
                        card.remove();

                        // Check if there are no more upcoming events
                        const upcomingSection = document.querySelector('.history-section:first-child .events-grid');
                        if (upcomingSection && upcomingSection.children.length === 0) {
                            upcomingSection.innerHTML = '<p style="color: #888; text-align: center; padding: 40px;">No upcoming events</p>';
                        }
                    }, 300);
                }

                closeCancelModal();

                // Show success message (you could create a toast notification)
                showSuccessMessage('RSVP cancelled successfully');
            }
        });
    }

    // ========== Write Review Functionality ==========

    // Open review modal when review button is clicked
    reviewButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevent card click
            const card = button.closest('.history-card');
            currentEventId = card?.getAttribute('data-event-id');
            const eventName = card?.querySelector('.event-name')?.textContent || 'this event';

            // Update modal with event name
            const eventNameSpan = document.getElementById('review-event-name');
            if (eventNameSpan) {
                eventNameSpan.textContent = eventName;
            }

            // Reset form
            if (reviewForm) {
                reviewForm.reset();
            }
            selectedRating = 0;
            updateStarDisplay();

            // Show modal
            if (reviewModal) {
                reviewModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    // Close review modal
    const closeReviewModal = () => {
        if (reviewModal) {
            reviewModal.style.display = 'none';
            document.body.style.overflow = 'auto';
            currentEventId = null;
        }
    };

    if (reviewModalClose) {
        reviewModalClose.addEventListener('click', closeReviewModal);
    }

    if (reviewModalOverlay) {
        reviewModalOverlay.addEventListener('click', closeReviewModal);
    }

    // Star rating functionality
    if (starRatingContainer) {
        const stars = starRatingContainer.querySelectorAll('.star');

        stars.forEach((star, index) => {
            // Click to select rating
            star.addEventListener('click', () => {
                selectedRating = index + 1;
                updateStarDisplay();
            });

            // Hover effect
            star.addEventListener('mouseenter', () => {
                highlightStars(index + 1);
            });
        });

        // Reset to selected rating on mouse leave
        starRatingContainer.addEventListener('mouseleave', () => {
            updateStarDisplay();
        });
    }

    // Update star display based on selected rating
    function updateStarDisplay() {
        highlightStars(selectedRating);
    }

    // Highlight stars up to the given rating
    function highlightStars(rating) {
        const stars = starRatingContainer?.querySelectorAll('.star');
        stars?.forEach((star, index) => {
            if (index < rating) {
                star.classList.add('selected');
            } else {
                star.classList.remove('selected');
            }
        });
    }

    // Submit review
    if (submitReviewBtn) {
        submitReviewBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const reviewTitle = document.getElementById('review-title')?.value;
            const reviewText = document.getElementById('review-text')?.value;

            // Validate form
            if (selectedRating === 0) {
                showErrorMessage('Please select a rating');
                return;
            }

            if (!reviewTitle || reviewTitle.trim() === '') {
                showErrorMessage('Please enter a review title');
                return;
            }

            if (!reviewText || reviewText.trim() === '') {
                showErrorMessage('Please write your review');
                return;
            }

            // Create review object
            const review = {
                eventId: currentEventId,
                rating: selectedRating,
                title: reviewTitle.trim(),
                review: reviewText.trim(),
                timestamp: new Date().toISOString()
            };

            // Here you would make an API call to submit the review
            console.log('Submitting review:', review);

            // Update the button to show review submitted
            const card = document.querySelector(`[data-event-id="${currentEventId}"]`);
            const reviewButton = card?.querySelector('.review-btn');
            if (reviewButton) {
                reviewButton.textContent = 'Review Submitted ✓';
                reviewButton.style.backgroundColor = 'rgba(76, 175, 80, 0.2)';
                reviewButton.style.borderColor = '#4CAF50';
                reviewButton.style.color = '#4CAF50';
                reviewButton.disabled = true;
                reviewButton.style.cursor = 'not-allowed';
            }

            closeReviewModal();
            showSuccessMessage('Review submitted successfully!');
        });
    }

    // ========== Helper Functions ==========

    // Show success message
    function showSuccessMessage(message) {
        const toast = createToast(message, 'success');
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Show error message
    function showErrorMessage(message) {
        const toast = createToast(message, 'error');
        document.body.appendChild(toast);

        setTimeout(() => {
            toast.classList.add('show');
        }, 100);

        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
            }, 300);
        }, 3000);
    }

    // Create toast notification
    function createToast(message, type = 'success') {
        const toast = document.createElement('div');
        toast.className = `toast toast-${type}`;
        toast.textContent = message;
        return toast;
    }
});
