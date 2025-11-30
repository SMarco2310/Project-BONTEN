


document.addEventListener('DOMContentLoaded', () => {

    const cancelButtons = document.querySelectorAll('.cancel-btn');
    
    const reviewButtons = document.querySelectorAll('.review-btn');


    const cancelModal = document.getElementById('cancel-modal');
    
    const cancelModalOverlay = cancelModal?.querySelector('.modal-overlay');
    
    const cancelModalClose = cancelModal?.querySelector('.modal-close');
    
    const cancelConfirmBtn = document.getElementById('cancel-confirm-btn');
    
    const cancelCancelBtn = document.getElementById('cancel-cancel-btn');


    
    const reviewModal = document.getElementById('review-modal');
    
    const reviewModalOverlay = reviewModal?.querySelector('.modal-overlay');
    
    const reviewModalClose = reviewModal?.querySelector('.modal-close');
    
    const submitReviewBtn = document.getElementById('submit-review-btn');
    
    const reviewForm = document.getElementById('review-form');


    
    const starRatingContainer = document.getElementById('star-rating');
    let selectedRating = 0;


    
    let currentEventId = null;


    
    cancelButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            
            e.stopPropagation();
            
            const card = button.closest('.history-card');
            
            currentEventId = card?.getAttribute('data-event-id');
            
            const eventName = card?.querySelector('.event-name')?.textContent || 'this event';

            
            const eventNameSpan = document.getElementById('cancel-event-name');
            if (eventNameSpan) {
                eventNameSpan.textContent = eventName;
            }

           
            if (cancelModal) {
                cancelModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });

    });

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


    if (cancelConfirmBtn) {
        cancelConfirmBtn.addEventListener('click', async () => {
            if (currentEventId) {


                console.log('Cancelling RSVP for event:', currentEventId);

                try {
                    // Call the API to cancel the RSVP
                    const response = await fetch('../api/cancel_rsvp.php', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                        },
                        body: JSON.stringify({
                            event_id: currentEventId
                        })
                    });

                    const data = await response.json();

                    if (response.ok && data.success) {
                        // Remove the card from the UI
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
                        showSuccessMessage('RSVP cancelled successfully');
                    } else {
                        // Show error message from server
                        closeCancelModal();
                        showErrorMessage(data.error || 'Failed to cancel RSVP');
                    }
                } catch (error) {
                    console.error('Error cancelling RSVP:', error);
                    closeCancelModal();
                    showErrorMessage('Failed to cancel RSVP. Please try again.');
                }
            }
        });
    }

 
    
    reviewButtons.forEach(button => {
        button.addEventListener('click', (e) => {
            
            e.stopPropagation(); 
            
            const card = button.closest('.history-card');

            currentEventId = card?.getAttribute('data-event-id');
            
            const eventName = card?.querySelector('.event-name')?.textContent || 'this event';

           
            const eventNameSpan = document.getElementById('review-event-name');

            if (eventNameSpan) {
                eventNameSpan.textContent = eventName;
            }

            
            if (reviewForm) {
                reviewForm.reset();
            }
            selectedRating = 0;
            
            updateStarDisplay();



           
            if (reviewModal) {
                reviewModal.style.display = 'flex';
                document.body.style.overflow = 'hidden';
            }
        });
    });

    
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

    
    if (starRatingContainer) {
        const stars = starRatingContainer.querySelectorAll('.star');

        stars.forEach((star, index) => {
            
            star.addEventListener('click', () => {
                selectedRating = index + 1;
                updateStarDisplay();
            });


        
            star.addEventListener('mouseenter', () => {
                highlightStars(index + 1);
            });
        });

        
        starRatingContainer.addEventListener('mouseleave', () => {
            updateStarDisplay();
        });
    }

    
    function updateStarDisplay() {
        highlightStars(selectedRating);
    }

    
    
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

    
    if (submitReviewBtn) {
        submitReviewBtn.addEventListener('click', (e) => {
            e.preventDefault();

            const reviewTitle = document.getElementById('review-title')?.value;
            const reviewText = document.getElementById('review-text')?.value;

           
            
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

            
            const review = {
            
                eventId: currentEventId,
            
                rating: selectedRating,
            
                title: reviewTitle.trim(),
            
                review: reviewText.trim(),
            
                timestamp: new Date().toISOString()
            };

            
            submitReviewBtn.disabled = true;
            
            submitReviewBtn.textContent = 'Submitting...';

           
            fetch('../api/reviews.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(review)
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    
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
                } else {
                   
                    throw new Error(data.error || 'Failed to submit review');
                }
            })
            .catch(error => {
                console.error('Error submitting review:', error);
                showErrorMessage(error.message || 'Failed to submit review. Please try again.');

                
                submitReviewBtn.disabled = false;
                
                submitReviewBtn.textContent = 'Submit Review';
            });
        });
    }


    
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

    
    
    function createToast(message, type = 'success') {
        
        const toast = document.createElement('div');
        
        toast.className = `toast toast-${type}`;
        
        toast.textContent = message;
        
        return toast;
    }

});
