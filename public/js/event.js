

document.addEventListener('DOMContentLoaded', () => {

    const rsvpBtn = document.getElementById('rsvp-btn');
    const rsvpModal = document.getElementById('rsvp-modal');
    const ticketsModal = document.getElementById('tickets-modal');

    const rsvpForm = document.getElementById('rsvp-form');

    const checkoutBtn = document.getElementById('checkout-btn');

    const modalCloses = document.querySelectorAll('.modal-close');

    const overlays = document.querySelectorAll('.modal-overlay');


    if(rsvpBtn) {

        rsvpBtn.addEventListener('click', () => {

            rsvpModal.classList.add('active');
        });

    }


    modalCloses.forEach(close => {

        close.addEventListener('click', () => {

            rsvpModal.classList.remove('active');

            ticketsModal.classList.remove('active');

        });
    });

    overlays.forEach(overlay => {

        overlay.addEventListener('click', () => {

            rsvpModal.classList.remove('active');
            ticketsModal.classList.remove('active');


        });


    });


    if(rsvpForm) {

        rsvpForm.addEventListener('submit', async (e) => {


            e.preventDefault();

            const email = document.getElementById('rsvp-email').value;


            const password = document.getElementById('rsvp-password').value;


            if(!password) {

                alert('Please enter your password');
                return;


            }

            try {


                const response = await fetch('../api/validate_user.php', {

                    method: 'POST',
                    headers: {

                        'Content-Type': 'application/json'

                    },


                    body: JSON.stringify({

                        email: email,

                        password: password

                    })
                });

                const result = await response.json();

                if(result.success) {

                    rsvpModal.classList.remove('active');
                    ticketsModal.classList.add('active');

                } else {

                    alert(result.message || 'Invalid password');

                }
            } catch(error) {

                console.error('Validation error:', error);


                alert('An error occurred. Please try again.');


            }
        });
    }



    const regularQty = document.getElementById('regular');
    const vipQty = document.getElementById('vip');
    const regularSubtotalElement = document.getElementById('regular-subtotal');
    const vipSubtotalElement = document.getElementById('vip-subtotal');
    const grandTotalElement = document.getElementById('grand-total');
    const plusBtns = document.querySelectorAll('.qty-plus-btn');
    const minusBtns = document.querySelectorAll('.qty-minus-btn');

    function updateSubtotal(ticketType, quantity, price, subtotalElement) {
        const subtotal = quantity * price;
        if (subtotalElement) {
            subtotalElement.textContent = subtotal.toFixed(2);
        }
    }

    function updateTotal() {
        let regularTickets = parseInt(regularQty.value) || 0;
        let vipTickets = parseInt(vipQty.value) || 0;

        updateSubtotal('regular', regularTickets, regularPrice, regularSubtotalElement);
        updateSubtotal('vip', vipTickets, vipPrice, vipSubtotalElement);

        const grandTotal = (regularTickets * regularPrice) + (vipTickets * vipPrice);
        if (grandTotalElement) {
            grandTotalElement.textContent = grandTotal.toFixed(2);
        }
    }

    plusBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const ticketType = btn.getAttribute('data-ticket');
            const maxQty = parseInt(btn.getAttribute('data-max'));
            let qtyInput;

            if (ticketType === 'regular') {
                qtyInput = regularQty;
            } else if (ticketType === 'vip') {
                qtyInput = vipQty;
            }

            if (qtyInput) {
                let current = parseInt(qtyInput.value);
                if (current < maxQty) {
                    qtyInput.value = current + 1;
                    updateTotal();
                }
            }
        });
    });

    minusBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const ticketType = btn.getAttribute('data-ticket');
            let qtyInput;

            if (ticketType === 'regular') {
                qtyInput = regularQty;
            } else if (ticketType === 'vip') {
                qtyInput = vipQty;
            }

            if (qtyInput) {
                let current = parseInt(qtyInput.value);
                if (current > 0) {
                    qtyInput.value = current - 1;
                    updateTotal();
                }
            }
        });
    });

    updateTotal(); // Initialize totals on page load

    if(checkoutBtn) {
        checkoutBtn.addEventListener('click', () => {
            console.log('Checkout button clicked (event.js handler)');
            if (typeof payWithPaystack === 'function') {
                console.log('Delegating to event_modals.js payment handler');
                payWithPaystack();
                return;
            }
            console.log('Using legacy payment implementation');
            const regQty = regularQty ? parseInt(regularQty.value) : 0;
            const vipQ = vipQty ? parseInt(vipQty.value) : 0;
            if(regQty === 0 && vipQ === 0) {
                alert('Please select at least one ticket');
                return;
            }
            if (typeof regularPrice === 'undefined' || typeof vipPrice === 'undefined') {
                alert('Error: Ticket prices not loaded. Please refresh the page.');
                return;
            }
            const total = (regQty * regularPrice) + (vipQ * vipPrice);
            if (total <= 0) {
                alert('Error: Invalid total amount. Please refresh the page.');
                return;
            }
            initiatePaystackPayment(total, regQty, vipQ);
        });
    }

    function initiatePaystackPayment(amount, regularQuantity, vipQuantity) {
        console.warn('Using deprecated payment method. Please use event_modals.js implementation.');
        if (typeof payWithPaystack === 'function') {
            payWithPaystack();
            return;
        }
        if (typeof PaystackPop === 'undefined') {
            alert('Payment system is not available. Please refresh the page and try again.');
            return;
        }
        const handler = PaystackPop.setup({
            key: 'pk_test_d1f61fd4add0486460c5a543b1a51e97015d1207',
            email: userEmail,
            amount: amount * 100,
            currency: 'GHS',
            ref: 'BONTEN_' + Math.floor((Math.random() * 1000000000) + 1),
            callback: function(response) {
                verifyPayment(response.reference, regularQuantity, vipQuantity);
            },
            onClose: function() {
                alert('Payment window closed');
            }
        });
        handler.openIframe();
    }

    async function verifyPayment(reference, regularQuantity, vipQuantity) {
        try {
            const response = await fetch('../api/verify_payment.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    reference: reference,
                    event_id: eventId,
                    regular_quantity: regularQuantity,
                    vip_quantity: vipQuantity
                })
            });
            const result = await response.json();
            if(result.success) {
                alert('Payment successful! Your tickets have been booked.');
                window.location.href = './history.php';
            } else {
                alert(result.message || 'Payment verification failed');
            }
        } catch(error) {
            console.error('Payment verification error:', error);
            alert('An error occurred during payment verification');
        }
    }

});
