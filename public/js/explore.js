// Explore page JavaScript

// Carousel functionality
document.querySelectorAll('.carousel-wrapper').forEach(wrapper => {
    const carousel = wrapper.querySelector('.events-carousel');
    const prevBtn = wrapper.querySelector('.prev');
    const nextBtn = wrapper.querySelector('.next');

    const cardWidth = 200;
    const gap = 20;
    const scrollAmount = cardWidth + gap;

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
});

// Bookmark functionality
document.querySelectorAll('.bookmark-btn').forEach(btn => {
    btn.addEventListener('click', (e) => {
        e.preventDefault();
        e.stopPropagation(); // Prevent card click when clicking bookmark
        if (btn.classList.contains('bookmarked')) {
            btn.classList.remove('bookmarked');
            btn.textContent = '⬜';
        } else {
            btn.classList.add('bookmarked');
            btn.textContent = '🔖';
        }
    });
});

// Event card click functionality - navigate to event page
document.querySelectorAll('.event-card').forEach(card => {
    card.addEventListener('click', (e) => {
        // Don't navigate if clicking bookmark button
        if (e.target.classList.contains('bookmark-btn')) {
            return;
        }

        // Get event details from the card
        const eventName = card.querySelector('.event-name').textContent.trim();
        const eventImage = card.querySelector('.card-image img').src;
        const eventBadge = card.querySelector('.event-badge')?.textContent.trim() || 'Event';
        const eventLocation = card.querySelector('.event-location')?.textContent.trim() || '';

        // Create event ID from name (lowercase, replace spaces with hyphens)
        const eventId = eventName.toLowerCase().replace(/\s+/g, '-');

        // Store event data in sessionStorage to use on event page
        const eventData = {
            id: eventId,
            name: eventName,
            image: eventImage,
            badge: eventBadge,
            location: eventLocation
        };
        sessionStorage.setItem('currentEvent', JSON.stringify(eventData));

        // Navigate to event page with the event ID
        window.location.href = `event.html?id=${eventId}`;
    });

    // Add hover effect
    card.style.cursor = 'pointer';
});

// ========== Search Functionality ==========

const searchInput = document.getElementById('search-input');
const searchBtn = document.getElementById('search-btn');
const categoryFilter = document.getElementById('category-filter');
const locationFilter = document.getElementById('location-filter');

let allEventCards = [];

// Collect all event cards on page load
document.addEventListener('DOMContentLoaded', () => {
    allEventCards = Array.from(document.querySelectorAll('.event-card'));
});

// Search function
function performSearch() {
    const searchTerm = searchInput.value.toLowerCase().trim();
    const selectedCategory = categoryFilter.value.toLowerCase();
    const selectedLocation = locationFilter.value.toLowerCase();

    let visibleCount = 0;

    allEventCards.forEach(card => {
        const eventName = card.querySelector('.event-name')?.textContent.toLowerCase() || '';
        const eventBadge = card.querySelector('.event-badge')?.textContent.toLowerCase() || '';
        const eventLocation = card.querySelector('.event-location')?.textContent.toLowerCase() || '';

        // Check if card matches all filters
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

    // Show/hide "no results" message
    showNoResultsMessage(visibleCount);
}

// Show no results message
function showNoResultsMessage(visibleCount) {
    // Remove existing no results message
    const existingMessage = document.querySelector('.no-results');
    if (existingMessage) {
        existingMessage.remove();
    }

    // If no results, show message
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

// Clear search and show all events
function clearSearch() {
    searchInput.value = '';
    categoryFilter.value = '';
    locationFilter.value = '';
    performSearch();
}

// Event listeners for search
if (searchBtn) {
    searchBtn.addEventListener('click', performSearch);
}

if (searchInput) {
    // Search on Enter key
    searchInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            performSearch();
        }
    });

    // Real-time search as user types
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

// Smooth scroll to search section from external links
window.addEventListener('load', () => {
    const hash = window.location.hash;
    if (hash === '#search-section') {
        const searchSection = document.getElementById('search-section');
        if (searchSection) {
            setTimeout(() => {
                searchSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
                searchInput.focus();
            }, 100);
        }
    }
});
