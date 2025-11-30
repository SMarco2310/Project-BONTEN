const rsvpModal = document.getElementById("rsvp-modal");

const ticketsModal = document.getElementById("tickets-modal");

const rsvpBtn = document.getElementById("rsvp-btn");

const ticketsBtn = document.getElementById("tickets-btn");

const closeButtons = document.querySelectorAll(".modal-close");

const modalOverlays = document.querySelectorAll(".modal-overlay");

const emailInput = document.getElementById("email");

const passwordInput = document.getElementById("password");

if (emailInput) {
  emailInput.addEventListener("input", () => {
    if (emailInput.value.length > 0) {
      removeError(emailInput);
    }
  });

  emailInput.addEventListener("blur", () => {
    if (emailInput.value.length > 0) {
      validateEmailField(emailInput);
    }
  });
}

if (passwordInput) {
  passwordInput.addEventListener("input", () => {
    if (passwordInput.value.length > 0) {
      removeError(passwordInput);
    }
  });

  passwordInput.addEventListener("blur", () => {
    if (passwordInput.value.length > 0) {
      validatePasswordField(passwordInput);
    }
  });
}

if (rsvpBtn) {
  rsvpBtn.addEventListener("click", () => {
    // Check if button is disabled (past event)
    if (rsvpBtn.disabled) {
      return;
    }
    rsvpModal.style.display = "flex";
  });
}

if (ticketsBtn) {
  ticketsBtn.addEventListener("click", (e) => {
    // Check if button is disabled (past event)
    if (ticketsBtn.disabled) {
      e.preventDefault();
      return;
    }

    const emailInput = document.getElementById("rsvp-email"); // Fixed to use correct ID

    const passwordInput = document.getElementById("rsvp-password"); // Fixed to use correct ID

    const isLoggedIn = !passwordInput;

    const isEmailValid = validateEmailField(emailInput);

    const isPasswordValid = isLoggedIn
      ? true
      : validatePasswordField(passwordInput);

    if (isEmailValid && isPasswordValid) {
      rsvpModal.style.display = "none";
      ticketsModal.style.display = "flex";
    }
  });
}

closeButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    rsvpModal.style.display = "none";

    ticketsModal.style.display = "none";
  });
});

modalOverlays.forEach((overlay) => {
  overlay.addEventListener("click", () => {
    rsvpModal.style.display = "none";

    ticketsModal.style.display = "none";
  });
});

const qtyButtons = document.querySelectorAll(".qty-plus-btn, .qty-minus-btn");

const regularPriceFromDB =
  typeof regularPrice !== "undefined" ? regularPrice : 150;

const vipPriceFromDB = typeof vipPrice !== "undefined" ? vipPrice : 300;

function updateTotals() {
  const regularInput = document.getElementById("regular");

  const vipInput = document.getElementById("vip");

  const regularQty = regularInput ? parseInt(regularInput.value) || 0 : 0;

  const vipQty = vipInput ? parseInt(vipInput.value) || 0 : 0;

  const regularTotal = regularQty * regularPriceFromDB;

  const vipTotal = vipQty * vipPriceFromDB;

  const grandTotal = regularTotal + vipTotal;

  document.getElementById("regular-subtotal").textContent =
    regularTotal.toFixed(2);

  document.getElementById("vip-subtotal").textContent = vipTotal.toFixed(2);

  document.getElementById("grand-total").textContent = grandTotal.toFixed(2);
}

// Initialize totals when page loads
document.addEventListener("DOMContentLoaded", () => {
  updateTotals();
});

qtyButtons.forEach((btn) => {
  btn.addEventListener("click", () => {
    const ticketType = btn.getAttribute("data-ticket");
    let input;

    if (ticketType === "regular") {
      input = document.getElementById("regular");
    } else if (ticketType === "vip") {
      input = document.getElementById("vip");
    }

    if (!input) return;

    let value = parseInt(input.value);
    const maxQty = parseInt(btn.getAttribute("data-max")) || 999;

    if (btn.classList.contains("qty-plus-btn") && value < maxQty) {
      input.value = value + 1;
    } else if (btn.classList.contains("qty-plus-btn") && value >= maxQty) {
      alert("No more tickets available for this type.");
    } else if (btn.classList.contains("qty-minus-btn") && value > 0) {
      input.value = value - 1;
    }
    updateTotals();
  });
});

const checkoutBtn = document.getElementById("checkout-btn");

if (checkoutBtn) {
  checkoutBtn.addEventListener("click", payWithPaystack);
}

async function loadPaystackScript() {
  return new Promise((resolve, reject) => {
    if (typeof PaystackPop !== 'undefined') {
      resolve();
      return;
    }
    const script = document.createElement('script');
    script.src = 'https://js.paystack.co/v1/inline.js';
    script.onload = () => resolve();
    script.onerror = () => reject(new Error("Failed to load Paystack script"));
    document.head.appendChild(script);
  });
}

async function payWithPaystack() {
  console.log("payWithPaystack called");

  // Try multiple ways to get the email
  let email =
    document.getElementById("rsvp-email")?.value ||
    document.getElementById("email")?.value ||
    userEmail; // from PHP variable

  const regularInput = document.getElementById("regular");
  const vipInput = document.getElementById("vip");

  const regularQty = regularInput ? parseInt(regularInput.value) || 0 : 0;
  const vipQty = vipInput ? parseInt(vipInput.value) || 0 : 0;

  const currentEventId = typeof eventId !== "undefined" ? eventId : 0;

  console.log("Payment details:", {
    email,
    regularQty,
    vipQty,
    currentEventId,
    userEmail,
  });

  // Validation
  if (!email || email.trim() === "") {
    alert("Please enter your email in the RSVP form first.");
    if (ticketsModal) ticketsModal.style.display = "none";
    if (rsvpModal) rsvpModal.style.display = "flex";
    return;
  }

  if (regularQty === 0 && vipQty === 0) {
    alert("Please select at least one ticket.");
    return;
  }

  if (currentEventId === 0) {
    alert("Error: Event information is missing.");
    console.error("Event ID is missing or zero");
    return;
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address.");
    return;
  }

  if (!navigator.onLine) {
    alert("You appear to be offline. Please check your internet connection.");
    return;
  }

  const checkoutBtn = document.getElementById("checkout-btn");
  if (!checkoutBtn) {
    console.error("Checkout button not found");
    return;
  }

  const originalText = checkoutBtn.innerText;
  checkoutBtn.innerText = "Processing...";
  checkoutBtn.disabled = true;

  // Ensure Paystack is loaded before starting transaction
  if (typeof PaystackPop === "undefined") {
    checkoutBtn.innerText = "Loading Payment...";
    try {
      await loadPaystackScript();
      console.log("Paystack script loaded dynamically");
    } catch (error) {
      console.error("Failed to load Paystack:", error);
      alert("Failed to load payment system. Please check your internet connection.");
      checkoutBtn.innerText = originalText;
      checkoutBtn.disabled = false;
      return;
    }
    // Restore text to Processing...
    checkoutBtn.innerText = "Processing...";
  }

  console.log("Sending request to initialize_transaction.php...");

  fetch("../src/Controllers/initialize_transaction.php", {
    method: "POST",
    headers: {
      "Content-Type": "application/json",
    },
    body: JSON.stringify({
      email: email,
      event_id: currentEventId,
      regular_quantity: regularQty,
      vip_quantity: vipQty,
    }),
  })
    .then(async (response) => {
      console.log("Response status:", response.status);
      const text = await response.text();
      console.log("Response text:", text);

      try {
        return JSON.parse(text);
      } catch (e) {
        console.error("JSON parse error:", e);
        console.error("Server response:", text);

        // More helpful error message
        if (text.includes("<!DOCTYPE html>") || text.includes("<html>")) {
          throw new Error(
            "Server returned HTML instead of JSON. Please ensure you are using a PHP server (not Live Server) and the backend is properly configured."
          );
        } else if (text.trim().length === 0) {
          throw new Error(
            "Server returned empty response. Please check the backend server."
          );
        } else {
          throw new Error(
            "Invalid server response. Response: " +
            text.substring(0, 100) +
            "..."
          );
        }
      }
    })
    .then(async (data) => {
      console.log("Parsed response data:", data);

      if (data.status) {
        console.log("Initializing Paystack with:", {
          key: data.public_key ? "pk_test..." : "MISSING",
          amount: data.amount,
          reference: data.reference,
        });

        const handler = PaystackPop.setup({
          key: data.public_key,
          email: email,
          amount: data.amount,
          currency: "GHS",
          ref: data.reference,
          onClose: function () {
            console.log("Payment modal closed");
            alert("Transaction was closed.");
          },
          callback: function (response) {
            console.log("Payment successful:", response);
            alert("Payment successful! Reference: " + response.reference);

            // Close tickets modal and reset form
            if (ticketsModal) ticketsModal.style.display = "none";

            const regularInput = document.getElementById("regular");
            const vipInput = document.getElementById("vip");
            if (regularInput) regularInput.value = 0;
            if (vipInput) vipInput.value = 0;
            updateTotals();

            // Redirect to history page or refresh
            setTimeout(() => {
              window.location.href = "./history.php";
            }, 1500);
          },
        });
        handler.openIframe();
      } else {
        console.error("Payment initialization failed:", data.message);
        alert(
          "Error initializing payment: " + (data.message || "Unknown error")
        );
      }
    })
    .catch((error) => {
      console.error("Payment error:", error);

      // More specific error messages
      let errorMessage = "An error occurred: ";
      if (error.message.includes("PHP server")) {
        errorMessage +=
          "Please use a PHP server to test payments. Live Server and file:// protocol do not support PHP.";
      } else if (error.message.includes("Paystack library")) {
        errorMessage += "Payment system not loaded. Please refresh the page.";
      } else if (error.message.includes("Invalid server response")) {
        errorMessage += "Server configuration issue. Please contact support.";
      } else {
        errorMessage += error.message;
      }

      alert(errorMessage);
    })
    .finally(() => {
      // Restore button state
      if (checkoutBtn) {
        checkoutBtn.innerText = originalText;
        checkoutBtn.disabled = false;
      }
      console.log("Payment process completed");
    });
}

// Debug function to test Paystack connection
window.testPaystackConnection = function () {
  console.log("Testing Paystack connection...");

  // Check if Paystack script is loaded
  if (typeof PaystackPop === "undefined") {
    console.error("❌ Paystack library not loaded");
    console.log(
      "Make sure this script is included: https://js.paystack.co/v1/inline.js"
    );
    return false;
  }
  console.log("✅ Paystack library loaded");

  // Check if required variables are defined
  console.log("Variables check:");
  console.log(
    "- eventId:",
    typeof eventId !== "undefined" ? eventId : "❌ MISSING"
  );
  console.log(
    "- regularPrice:",
    typeof regularPrice !== "undefined" ? regularPrice : "❌ MISSING"
  );
  console.log(
    "- vipPrice:",
    typeof vipPrice !== "undefined" ? vipPrice : "❌ MISSING"
  );

  // Check if required DOM elements exist
  console.log("DOM elements check:");
  console.log(
    "- checkout-btn:",
    document.getElementById("checkout-btn") ? "✅" : "❌"
  );
  console.log("- email input:", document.getElementById("email") ? "✅" : "❌");
  console.log(
    "- regular input:",
    document.getElementById("regular") ? "✅" : "❌"
  );
  console.log("- vip input:", document.getElementById("vip") ? "✅" : "❌");

  // Test backend endpoint
  console.log("Testing backend endpoint...");
  fetch("../src/Controllers/initialize_transaction.php", {
    method: "POST",
    headers: { "Content-Type": "application/json" },
    body: JSON.stringify({
      email: "test@example.com",
      event_id: 1,
      regular_quantity: 1,
      vip_quantity: 0,
    }),
  })
    .then((response) => response.text())
    .then((text) => {
      console.log("Backend response:", text);
      try {
        const data = JSON.parse(text);
        console.log("✅ Backend responding with JSON");
        console.log("Backend data:", data);
      } catch (e) {
        console.error("❌ Backend not returning valid JSON");
        console.error("Response:", text.substring(0, 200));
      }
    })
    .catch((error) => {
      console.error("❌ Backend connection failed:", error);
    });

  console.log("Test completed. Check the console output above.");
};

// Auto-run test when page loads (for debugging)
document.addEventListener("DOMContentLoaded", () => {
  console.log(
    "Page loaded. Run testPaystackConnection() in console to test payment setup"
  );

  // Check if Paystack script is loaded after a short delay
  setTimeout(() => {
    if (typeof PaystackPop === "undefined") {
      console.warn(
        "⚠️ Paystack library not loaded. This may cause payment issues."
      );
      console.warn(
        "Make sure the script tag is present: <script src='https://js.paystack.co/v1/inline.js'></script>"
      );
    } else {
      console.log("✅ Paystack library loaded successfully");
    }
  }, 1000);
});
