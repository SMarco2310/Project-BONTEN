

document.addEventListener('DOMContentLoaded', () => {

    const cancelButtons = document.querySelectorAll('.cancel-btn');

    const writeReviewButtons = document.querySelectorAll('.write-review-btn');

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

    let currentEventName = '';


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

                try {
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

                        const card = document.querySelector(`[data-event-id="${currentEventId}"]`);
                        if (card) {

                            card.style.transition = 'opacity 0.3s ease, transform 0.3s ease';


                            card.style.opacity = '0';
                            card.style.transform = 'scale(0.9)';

                            setTimeout(() => {
                                card.remove();

                                const upcomingSection = document.querySelector('.history-section:first-child .events-grid');
                                if (upcomingSection && upcomingSection.children.length === 0) {
                                    upcomingSection.innerHTML = '<p style="color: #888; text-align: center; padding: 40px;">No upcoming events</p>';

                                }

                            }, 300);
                        }

                        closeCancelModal();

                        showSuccessMessage('RSVP cancelled successfully');


                    } else {

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


    writeReviewButtons.forEach(button => {
        button.addEventListener('click', (e) => {


            e.stopPropagation();

            currentEventId = button.getAttribute('data-event-id');
            currentEventName = button.getAttribute('data-event-name');

            const eventNameSpan = document.getElementById('review-event-name');


            if (eventNameSpan) {
                eventNameSpan.textContent = currentEventName;

            }

            if (reviewForm) {

                reviewForm.reset();
            }
            selectedRating = 0;

            const ratingValue = document.getElementById('rating-value');

            if(ratingValue) {


                ratingValue.value = 0;

            }

            updateStarDisplay();

            if (reviewModal) {

                reviewModal.classList.add('active');
                document.body.style.overflow = 'hidden';

            }

        });


    });


    const closeReviewModal = () => {
        if (reviewModal) {

            reviewModal.classList.remove('active');

            document.body.style.overflow = 'auto';
            currentEventId = null;

            currentEventName = '';


        }

    };

    if (reviewModalClose) {

        reviewModalClose.addEventListener('click', closeReviewModal);
    }

    if (reviewModalOverlay) {

        reviewModalOverlay.addEventListener('click', closeReviewModal);

    }


    if (starRatingContainer) {


        const stars = starRatingContainer.querySelectorAll('.rating-star');

        stars.forEach((star, index) => {

            star.addEventListener('click', () => {

                selectedRating = index + 1;

                const ratingValue = document.getElementById('rating-value');
                if(ratingValue) {


                    ratingValue.value = selectedRating;


                }
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


        const stars = starRatingContainer?.querySelectorAll('.rating-star');

        stars?.forEach((star, index) => {

            if (index < rating) {


                star.classList.add('active');
            } else {
                star.classList.remove('active');

            }

        });

    }


    if (reviewForm) {

        reviewForm.addEventListener('submit', async (e) => {


            e.preventDefault();

            const reviewTitle = document.getElementById('review-title')?.value;


            const reviewText = document.getElementById('review-text')?.value;
            const ratingValue = document.getElementById('rating-value')?.value;

            if (!ratingValue || ratingValue == 0) {


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

            submitReviewBtn.disabled = true;
            submitReviewBtn.textContent = 'Submitting...';

            try {

                const response = await fetch('../api/submit_review.php', {
                    method: 'POST',

                    headers: {

                        'Content-Type': 'application/json',

                    },


                    body: JSON.stringify({


                        event_id: currentEventId,

                        rating: parseInt(ratingValue),

                        review_title: reviewTitle.trim(),

                        review_text: reviewText.trim()
                    })

                });

                const data = await response.json();

                if (data.success) {

                    closeReviewModal();
                    showSuccessMessage('Review submitted successfully!');

                    setTimeout(() => {
                        window.location.reload();
                    }, 1500);

                } else {
                    showErrorMessage(data.message || 'Failed to submit review');


                    submitReviewBtn.disabled = false;
                    submitReviewBtn.textContent = 'Submit Review';
                }

            } catch (error) {


                console.error('Error submitting review:', error);
                showErrorMessage('Failed to submit review. Please try again.');

                submitReviewBtn.disabled = false;

                submitReviewBtn.textContent = 'Submit Review';

            }


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
