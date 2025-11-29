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

// Ticket quantity controls and Price Calculation
const qtyButtons = document.querySelectorAll('.qty-btn');
const regularPrice = 150;
const vipPrice = 300;

function updateTotals() {
    const regularQty = parseInt(document.getElementById('regular').value) || 0;
    const vipQty = parseInt(document.getElementById('vip').value) || 0;

    const regularTotal = regularQty * regularPrice;
    const vipTotal = vipQty * vipPrice;
    const grandTotal = regularTotal + vipTotal;

    document.getElementById('regular-subtotal').textContent = regularTotal.toFixed(2);
    document.getElementById('vip-subtotal').textContent = vipTotal.toFixed(2);
    document.getElementById('grand-total').textContent = grandTotal.toFixed(2);
}

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
        updateTotals();
    });
});


// Paystack Payment Integration
const checkoutBtn = document.getElementById('checkout-btn');

if (checkoutBtn) {
    checkoutBtn.addEventListener('click', payWithPaystack);
}

function payWithPaystack() {
    const email = document.getElementById('email').value;
    const regularQty = parseInt(document.getElementById('regular').value) || 0;
    const vipQty = parseInt(document.getElementById('vip').value) || 0;

    if (!email) {
        alert('Please enter your email in the RSVP form first.');
        // Switch back to RSVP modal
        ticketsModal.style.display = 'none';
        rsvpModal.style.display = 'flex';
        return;
    }

    if (regularQty === 0 && vipQty === 0) {
        alert('Please select at least one ticket.');
        return;
    }

    const checkoutBtn = document.getElementById('checkout-btn');
    const originalText = checkoutBtn.innerText;
    checkoutBtn.innerText = 'Processing...';
    checkoutBtn.disabled = true;

    // Initialize transaction on backend
    fetch('../src/Controllers/initialize_transaction.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
        },
        body: JSON.stringify({
            email: email,
            regular_quantity: regularQty,
            vip_quantity: vipQty
        })
    })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Server response:', text);
                throw new Error('The server returned an invalid response. If you are using Live Server, please use a PHP server instead. Response start: ' + text.substring(0, 50));
            }
        })
        .then(data => {
            if (data.status) {
                const handler = PaystackPop.setup({
                    key: data.public_key, // Get public key from backend response
                    email: email,
                    amount: data.amount, // Amount in kobo
                    currency: 'GHS',
                    ref: data.reference,
                    onClose: function () {
                        alert('Transaction was closed.');
                    },
                    callback: function (response) {
                        alert('Payment successful! Reference: ' + response.reference);
                        // Verify transaction on backend (optional but recommended)
                        // window.location.href = 'verify_payment.html?reference=' + response.reference;
                        ticketsModal.style.display = 'none';
                        // Reset form
                        document.getElementById('regular').value = 0;
                        document.getElementById('vip').value = 0;
                        updateTotals();
                    }
                });
                handler.openIframe();
            } else {
                alert('Error initializing payment: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred: ' + error.message);
        })
        .finally(() => {
            checkoutBtn.innerText = originalText;
            checkoutBtn.disabled = false;
        });
}
