// Event page modal functionality

// Modal functionality
const rsvpModal = document.getElementById('rsvp-modal');
const ticketsModal = document.getElementById('tickets-modal');
const rsvpBtn = document.getElementById('rsvp-btn');
const ticketsBtn = document.getElementById('tickets-btn');
const closeButtons = document.querySelectorAll('.modal-close');
const modalOverlays = document.querySelectorAll('.modal-overlay');

// Open RSVP modal
if (rsvpBtn) {
    rsvpBtn.addEventListener('click', () => {
        rsvpModal.style.display = 'flex';
    });
}

// Open Tickets modal from RSVP modal
if (ticketsBtn) {
    ticketsBtn.addEventListener('click', () => {
        rsvpModal.style.display = 'none';
        ticketsModal.style.display = 'flex';
    });
}

// Close modals
closeButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        rsvpModal.style.display = 'none';
        ticketsModal.style.display = 'none';
    });
});

modalOverlays.forEach(overlay => {
    overlay.addEventListener('click', () => {
        rsvpModal.style.display = 'none';
        ticketsModal.style.display = 'none';
    });
});

// Ticket quantity controls
const qtyButtons = document.querySelectorAll('.qty-btn');
qtyButtons.forEach(btn => {
    btn.addEventListener('click', () => {
        const target = btn.getAttribute('data-target');
        const input = document.getElementById(target);
        let value = parseInt(input.value);

        if (btn.classList.contains('plus')) {
            input.value = value + 1;
        } else if (btn.classList.contains('minus') && value > 0) {
            input.value = value - 1;
        }
    });
});
