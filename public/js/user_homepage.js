        // Carousel navigation functionality
        const carousel = document.querySelector('.events_carousel');

        const prevBtn = document.querySelector('.carousel_nav.prev');

        const nextBtn = document.querySelector('.carousel_nav.next');

        const cardWidth = 220;
        const gap = 20;
        const scrollAmount = cardWidth + gap;

        // Manual navigation buttons
        prevBtn.addEventListener('click', () => {
            carousel.scrollBy({
                left: -scrollAmount,
                behavior: 'smooth'
            });
        });

        nextBtn.addEventListener('click', () => {
            carousel.scrollBy({
                left: scrollAmount,
                behavior: 'smooth'
            });
        });

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