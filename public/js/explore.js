// https://www.w3schools.com/howto/howto_js_slideshow.asp
// https://css-tricks.com/css-only-carousel/

document.addEventListener('DOMContentLoaded', () => {
    
    document.querySelectorAll('.carousel-wrapper').forEach(wrapper => {
        
        const carousel = wrapper.querySelector('.events-carousel');
        
        const prevBtn = wrapper.querySelector('.carousel-arrow.prev');
        
        const nextBtn = wrapper.querySelector('.carousel-arrow.next');

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

            console.log('Starting auto-scroll timer for:', carousel.id || 'unnamed');
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
        wrapper.addEventListener('mouseenter', () => {
            isHovered = true;
        });

        wrapper.addEventListener('mouseleave', () => {
            isHovered = false;
        });

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

       
        setTimeout(() => {
            
            const hasScrollableContent = carousel.scrollWidth > carousel.clientWidth;
            
            console.log('Carousel check:', {
                
                id: carousel.id || 'unnamed',
                
                scrollWidth: carousel.scrollWidth,
                
                clientWidth: carousel.clientWidth,
                
                hasScrollableContent: hasScrollableContent,
                
                cardCount: carousel.querySelectorAll('.event-card').length
            });
            
            if (hasScrollableContent) {
                startAutoScroll();
                console.log(' Auto-scroll started for carousel:', carousel.id || 'unnamed');
            } else {
                console.log('Carousel does not have scrollable content:', carousel.id || 'unnamed');
            }

        }, 1000);
    });


    document.querySelectorAll('.bookmark-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
       
        e.preventDefault();
       
        e.stopPropagation();
       
        if (btn.classList.contains('bookmarked')) {
            btn.classList.remove('bookmarked');
            btn.textContent = '⬜';
        } 
        
        else {
            btn.classList.add('bookmarked');
            btn.textContent = '🔖';
        }
    });
    });

    document.querySelectorAll('.event-card').forEach(card => {
        card.addEventListener('click', (e) => {
            if (e.target.classList.contains('bookmark-btn')) {
                return;
            }

            const eventName = card.querySelector('.event-name').textContent.trim();
            
            const eventImage = card.querySelector('.card-image img').src;
            
            const eventBadge = card.querySelector('.event-badge')?.textContent.trim() || 'Event';
            
            const eventLocation = card.querySelector('.event-location')?.textContent.trim() || '';

            const eventId = eventName.toLowerCase().replace(/\s+/g, '-');

            const eventData = {
                id: eventId,
                name: eventName,
                image: eventImage,
                badge: eventBadge,
                location: eventLocation
            };
            sessionStorage.setItem('currentEvent', JSON.stringify(eventData));

            window.location.href = `event.html?id=${eventId}`;
        });

        card.style.cursor = 'pointer';
    });

    const searchInput = document.getElementById('search-input');
    
    const searchBtn = document.getElementById('search-btn');
    
    const categoryFilter = document.getElementById('category-filter');
    
    const locationFilter = document.getElementById('location-filter');


    let allEventCards = Array.from(document.querySelectorAll('.event-card'));

    function performSearch() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        
        const selectedCategory = categoryFilter.value.toLowerCase();
        
        const selectedLocation = locationFilter.value.toLowerCase();

        let visibleCount = 0;

        allEventCards.forEach(card => {
            const eventName = card.querySelector('.event-name')?.textContent.toLowerCase() || '';
            
            
            const eventBadge = card.querySelector('.event-badge')?.textContent.toLowerCase() || '';
            
            const eventLocation = card.querySelector('.event-location')?.textContent.toLowerCase() || '';

            const matchesSearch = searchTerm === '' ||
                                eventName.includes(searchTerm) ||
                                eventBadge.includes(searchTerm) ||
                                eventLocation.includes(searchTerm);

            const matchesCategory = selectedCategory === '' || eventBadge.includes(selectedCategory);
           
           
            const matchesLocation = selectedLocation === '' || eventLocation.includes(selectedLocation);

            if (matchesSearch && matchesCategory && matchesLocation) {
                card.style.display = 'block';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        showNoResultsMessage(visibleCount);
    }

    function showNoResultsMessage(visibleCount) {
        const existingMessage = document.querySelector('.no-results');
        if (existingMessage) {
            existingMessage.remove();
        }

        if (visibleCount === 0) {
            const sections = document.querySelectorAll('.events-section');
            sections.forEach(section => {
                const carousel = section.querySelector('.events-carousel');
                if (carousel && !carousel.querySelector('.no-results')) {
                    const noResultsDiv = document.createElement('div');
                    noResultsDiv.className = 'no-results';
                    noResultsDiv.innerHTML = `
                        <h3>No events found</h3>
                        <p>Try adjusting your search or filters</p>
                    `;
                    carousel.appendChild(noResultsDiv);
                }
            });
        }
    }

    function clearSearch() {

        searchInput.value = '';
        categoryFilter.value = '';
        locationFilter.value = '';
        performSearch();
    }

    if (searchBtn) {
        searchBtn.addEventListener('click', performSearch);
    }

    if (searchInput) {
        searchInput.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                performSearch();
            }
        });

        searchInput.addEventListener('input', () => {
            performSearch();
        });
    }

    if (categoryFilter) {
        
        categoryFilter.addEventListener('change', performSearch);
    }

    if (locationFilter) {
        locationFilter.addEventListener('change', performSearch);
    }


    const hash = window.location.hash;
    if (hash === '#search-section') {
        
        const searchSection = document.getElementById('search-section');
        
        if (searchSection) {
            setTimeout(() => {
                searchSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                if (searchInput) searchInput.focus();
            }, 100);
        }

    }
});
