

document.addEventListener('DOMContentLoaded', () => {
    // Carousel navigation functionality
    const carousel = document.querySelector('.events_carousel');

    const prevBtn = document.querySelector('.carousel_nav.prev');

    
    const nextBtn = document.querySelector('.carousel_nav.next');

    if (!carousel || !prevBtn || !nextBtn) return;

    const cardWidth = 220;
    const gap = 20;

    const scrollAmount = cardWidth + gap;

    let autoScrollTimer;
    let isHovered = false;

    function scrollCarousel(direction) {
        console.log('Scrolling carousel:', direction, 'Current scroll:', carousel.scrollLeft);
        carousel.scrollBy({
            left: direction === 'next' ? scrollAmount : -scrollAmount,
            behavior: 'smooth'
        });

    }


    function startAutoScroll() {
        if (autoScrollTimer) clearInterval(autoScrollTimer);

        console.log('Starting auto-scroll timer for user homepage carousel');
        autoScrollTimer = setInterval(() => {
            console.log('Auto-scroll tick - hovered:', isHovered, 'hidden:', document.hidden);
            if (!isHovered && !document.hidden) {
                const maxScroll = carousel.scrollWidth - carousel.clientWidth;

                // Only auto-scroll if there's content to scroll
                if (maxScroll > 0) {
                    if (carousel.scrollLeft >= maxScroll - 5) {
                        console.log('Resetting carousel to start');
                        carousel.scrollTo({
                            left: 0,
                            behavior: 'smooth'
                        });
                    } else {
                        console.log('Auto-scrolling next');
                        scrollCarousel('next');
                    }
                }
            }
        }, 3000);
    }

    function stopAutoScroll() {
        if (autoScrollTimer) {
            clearInterval(autoScrollTimer);
            autoScrollTimer = null;
        }
    }

    // Hover detection to pause auto-scroll
    carousel.addEventListener('mouseenter', () => {
        isHovered = true;
    });

    carousel.addEventListener('mouseleave', () => {
        isHovered = false;
    });

    // Manual navigation buttons
    prevBtn.addEventListener('click', () => {
        scrollCarousel('prev');
        stopAutoScroll();
        setTimeout(startAutoScroll, 5000);
    });

    nextBtn.addEventListener('click', () => {
        scrollCarousel('next');
        stopAutoScroll();
        setTimeout(startAutoScroll, 5000);
    });


    // Pause auto-scroll when page is not visible
    document.addEventListener('visibilitychange', () => {
        if (document.hidden) {
            stopAutoScroll();
        } else {
            startAutoScroll();
        }
    });

    // Start auto-scroll after content loads
    setTimeout(() => {
        const hasScrollableContent = carousel.scrollWidth > carousel.clientWidth;
        console.log('Homepage Carousel check:', {
            scrollWidth: carousel.scrollWidth,
            clientWidth: carousel.clientWidth,
            hasScrollableContent: hasScrollableContent,
            cardCount: carousel.querySelectorAll('.event_card').length
        });
        
        if (hasScrollableContent) {
            startAutoScroll();
            console.log('Auto-scroll started for homepage carousel');
        } else {
            console.log(' Homepage carousel does not have scrollable content');
        }
    }, 1000);

    // Bookmark functionality
    const bookmarkIcons = document.querySelectorAll('.bookmark_icon');

    bookmarkIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            if (icon.classList.contains('bookmarked')) {
                icon.classList.remove('bookmarked');
                icon.textContent = '⬜';
            } else {
                icon.classList.add('bookmarked');
                icon.textContent = '🔖';
            }
        });

    });
});