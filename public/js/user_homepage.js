

document.addEventListener('DOMContentLoaded', () => {

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


    carousel.addEventListener('mouseenter', () => {

        isHovered = true;


    });

    carousel.addEventListener('mouseleave', () => {
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



    document.addEventListener('visibilitychange', () => {

        if (document.hidden) {


            stopAutoScroll();


        }

         else {

            startAutoScroll();

        }

    });


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


        }

        else {
            console.log(' Homepage carousel does not have scrollable content');
        }
    }, 1000);


    const bookmarkIcons = document.querySelectorAll('.bookmark_icon');

    bookmarkIcons.forEach(icon => {
        icon.addEventListener('click', () => {
            if (icon.classList.contains('bookmarked')) {
                icon.classList.remove('bookmarked');
                icon.textContent = '';
            } else {
                icon.classList.add('bookmarked');
                icon.textContent = '';
            }
        });
    });

    // Month selector functionality for Your Plans
    const monthSelector = document.querySelector('.month_selector');
    const yourPlansContainer = document.querySelector('.Your-plans');

    if (monthSelector && yourPlansContainer) {
        // Set current month as default
        const currentMonth = new Date().getMonth();
        monthSelector.selectedIndex = currentMonth;

        monthSelector.addEventListener('change', async function() {
            const selectedMonth = this.selectedIndex + 1;
            const currentYear = new Date().getFullYear();

            try {
                const response = await fetch(`../api/user/upcoming_events.php?month=${selectedMonth}&year=${currentYear}`);
                const data = await response.json();

                if (data.success) {
                    displayUpcomingEvents(data.events);
                } else {
                    console.error('Failed to fetch events:', data.message);
                }
            } catch (error) {
                console.error('Error fetching events:', error);
            }
        });
    }

    function displayUpcomingEvents(events) {
        const container = document.querySelector('.Your-plans');
        if (!container) return;

        if (events.length === 0) {
            container.innerHTML = '<p style="color: #888; text-align: center; padding: 20px 0;">No upcoming events for this month.</p>';
            return;
        }

        container.innerHTML = events.map(event => {
            let imageSrc = '../public/assets/bonten.png';
            if (event.image_path) {
                if (event.image_path.startsWith('../public/')) {
                    imageSrc = event.image_path.substring(3);
                } else if (event.image_path.startsWith('public/')) {
                    imageSrc = '../' + event.image_path;
                } else {
                    imageSrc = '../public/assets/' + event.image_path;
                }
            }

            const eventDate = new Date(event.event_date + ' ' + event.event_time);
            const formattedDate = eventDate.toLocaleString('en-US', {
                month: 'long',
                day: 'numeric',
                year: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            });

            return `
                <div class="event">
                    <img src="${imageSrc}" alt="Event Icon" class="event_icon" />
                    <div class="details">
                        <h4 class="event_title">${escapeHtml(event.name)}</h4>
                        <p class="event_time">${formattedDate}</p>
                    </div>
                    <div class="event_actions">
                        <a href="./event.php?id=${event.event_id}">
                            <button class="edit_event">View details</button>
                        </a>
                    </div>
                </div>
            `;
        }).join('');
    }

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

});