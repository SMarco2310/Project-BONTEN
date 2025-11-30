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
      if (passwordInput.value.trim() === "") {
        showError(passwordInput, "Password is required");
      } else {
        removeError(passwordInput);
      }
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
    // Prevent form submission
    e.preventDefault();

    // Check if button is disabled (past event)
    if (ticketsBtn.disabled) {
      return;
    }

    const emailInput = document.getElementById("rsvp-email");
    const passwordInput = document.getElementById("rsvp-password");
    const isLoggedIn = !passwordInput;

    const isEmailValid = validateEmailField(emailInput);

    let isPasswordValid = true;
    if (!isLoggedIn) {
      if (!passwordInput.value || passwordInput.value.trim() === "") {
        showError(passwordInput, "Password is required");
        isPasswordValid = false;
      } else {
        removeError(passwordInput);
        isPasswordValid = true;
      }
    }

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
    if (typeof PaystackPop !== "undefined") {
      resolve();
      return;
    }
    const script = document.createElement("script");
    script.src = "https://js.paystack.co/v1/inline.js";
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

  // Get password for verification (only needed if not logged in via session)
  const passwordInputField = document.getElementById("rsvp-password");
  let password = passwordInputField?.value || "";

  // Check if user is logged in - password field won't exist if logged in
  // Also check the global isLoggedIn variable from PHP
  const userIsLoggedIn =
    (typeof isLoggedIn !== "undefined" && isLoggedIn) || !passwordInputField;

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
    userIsLoggedIn,
    hasPasswordField: !!passwordInputField,
  });

  // Validation
  if (!email || email.trim() === "") {
    alert("Please enter your email in the RSVP form first.");
    if (ticketsModal) ticketsModal.style.display = "none";
    if (rsvpModal) rsvpModal.style.display = "flex";
    return;
  }

  // Validate password if user is not logged in via session
  if (!userIsLoggedIn) {
    if (!password || password.trim() === "") {
      alert("Please enter your password to proceed with checkout.");
      if (ticketsModal) ticketsModal.style.display = "none";
      if (rsvpModal) rsvpModal.style.display = "flex";
      // Focus on password field
      const passwordInput = document.getElementById("rsvp-password");
      if (passwordInput) {
        passwordInput.focus();
        if (typeof showError === "function") {
          showError(passwordInput, "Password is required for checkout");
        }
      }
      return;
    }

    // Verify password with backend before proceeding
    try {
      const checkoutBtn = document.getElementById("checkout-btn");
      if (checkoutBtn) {
        checkoutBtn.innerText = "Verifying...";
        checkoutBtn.disabled = true;
      }

      const verifyResponse = await fetch("../api/validate_user.php", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify({
          email: email.trim(),
          password: password,
        }),
      });

      const verifyResult = await verifyResponse.json();

      if (!verifyResult.success) {
        alert("Invalid password. Please check your password and try again.");
        if (checkoutBtn) {
          checkoutBtn.innerText = "Proceed to Checkout";
          checkoutBtn.disabled = false;
        }
        // Show error on password field and redirect to RSVP modal
        const passwordInput = document.getElementById("rsvp-password");
        if (passwordInput && typeof showError === "function") {
          showError(passwordInput, verifyResult.message || "Invalid password");
          passwordInput.focus();
        }
        // Redirect back to RSVP modal to re-enter password
        if (ticketsModal) ticketsModal.style.display = "none";
        if (rsvpModal) rsvpModal.style.display = "flex";
        return;
      }

      // Password is valid, continue with checkout
      console.log("Password verified successfully");
    } catch (error) {
      console.error("Password verification error:", error);
      alert(
        "An error occurred while verifying your password. Please try again."
      );
      const checkoutBtn = document.getElementById("checkout-btn");
      if (checkoutBtn) {
        checkoutBtn.innerText = "Proceed to Checkout";
        checkoutBtn.disabled = false;
      }
      return;
    }
  }

  if (regularQty === 0 && vipQty === 0) {
    alert("Please select at least one ticket.");
    const checkoutBtn = document.getElementById("checkout-btn");
    if (checkoutBtn) {
      checkoutBtn.innerText = "Proceed to Checkout";
      checkoutBtn.disabled = false;
    }
    return;
  }

  if (currentEventId === 0) {
    alert("Error: Event information is missing.");
    console.error("Event ID is missing or zero");
    const checkoutBtn = document.getElementById("checkout-btn");
    if (checkoutBtn) {
      checkoutBtn.innerText = "Proceed to Checkout";
      checkoutBtn.disabled = false;
    }
    return;
  }

  // Validate email format
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  if (!emailRegex.test(email)) {
    alert("Please enter a valid email address.");
    const checkoutBtn = document.getElementById("checkout-btn");
    if (checkoutBtn) {
      checkoutBtn.innerText = "Proceed to Checkout";
      checkoutBtn.disabled = false;
    }
    return;
  }

  if (!navigator.onLine) {
    alert("You appear to be offline. Please check your internet connection.");
    const checkoutBtn = document.getElementById("checkout-btn");
    if (checkoutBtn) {
      checkoutBtn.innerText = "Proceed to Checkout";
      checkoutBtn.disabled = false;
    }
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

    // Wait for Paystack to load (with timeout)
    let paystackReady = false;
    let attempts = 0;
    const maxAttempts = 50; // 5 seconds max wait

    while (typeof PaystackPop === "undefined" && attempts < maxAttempts) {
      await new Promise((resolve) => setTimeout(resolve, 100));
      attempts++;
    }

    // If still not loaded, try loading manually
    if (typeof PaystackPop === "undefined") {
      try {
        await loadPaystackScript();
        // Wait a bit more for initialization
        await new Promise((resolve) => setTimeout(resolve, 200));
        console.log("Paystack script loaded dynamically");
      } catch (error) {
        console.error("Failed to load Paystack:", error);
        alert(
          "Failed to load payment system. Please check your internet connection and refresh the page."
        );
        checkoutBtn.innerText = originalText;
        checkoutBtn.disabled = false;
        return;
      }
    }

    // Final check
    if (typeof PaystackPop === "undefined") {
      alert(
        "Payment system not loaded. Please refresh the page and ensure you have an internet connection."
      );
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
        // Final check - wait a bit more if PaystackPop is still undefined
        if (typeof PaystackPop === "undefined") {
          console.warn("PaystackPop still undefined, waiting 500ms...");
          await new Promise((resolve) => setTimeout(resolve, 500));
          if (typeof PaystackPop === "undefined") {
            // Try to load the script again
            try {
              await loadPaystackScript();
              await new Promise((resolve) => setTimeout(resolve, 200));
            } catch (loadError) {
              console.error("Failed to load Paystack script:", loadError);
              throw new Error(
                "Payment system not loaded. Please refresh the page."
              );
            }
          }
        }

        if (typeof PaystackPop === "undefined") {
          throw new Error(
            "Payment system not loaded. Please refresh the page."
          );
        }

        console.log("PaystackPop is available:", typeof PaystackPop);
        console.log("PaystackPop object:", PaystackPop);

        // Verify PaystackPop.setup exists
        if (typeof PaystackPop.setup !== "function") {
          throw new Error(
            "Paystack setup function not available. Please refresh the page."
          );
        }

        const handler = PaystackPop.setup({
          key: data.public_key,
          email: email,
          amount: data.amount,
          currency: "GHS",
          ref: data.reference,
          onClose: function () {
            console.log("Payment modal closed");
            // Restore button state
            if (checkoutBtn) {
              checkoutBtn.innerText = originalText;
              checkoutBtn.disabled = false;
            }
            alert("Transaction was closed.");
          },
          callback: function (response) {
            console.log("Payment successful at Paystack:", response);

            // Show verifying status
            checkoutBtn.innerText = "Verifying...";

            // Verify transaction with backend
            fetch(
              `../src/Controllers/verify_transaction.php?reference=${response.reference}`
            )
              .then((res) => res.json())
              .then((verifyData) => {
                console.log("Verification response:", verifyData);

                if (verifyData.status) {
                  alert("Payment successful! Reference: " + response.reference);

                  // Close tickets modal and reset form
                  if (ticketsModal) ticketsModal.style.display = "none";

                  const regularInput = document.getElementById("regular");
                  const vipInput = document.getElementById("vip");
                  if (regularInput) regularInput.value = 0;
                  if (vipInput) vipInput.value = 0;
                  updateTotals();

                  // Redirect to history page
                  window.location.href = "./history.php";
                } else {
                  alert(
                    "Payment verification failed: " +
                      (verifyData.message || "Unknown error")
                  );
                }
              })
              .catch((err) => {
                console.error("Verification error:", err);
                alert(
                  "Payment was successful but verification failed. Please contact support with reference: " +
                    response.reference
                );
              });
          },
        });

        // Verify handler was created
        if (!handler) {
          console.error("Handler is null or undefined");
          throw new Error(
            "Failed to create Paystack payment handler. Please refresh the page."
          );
        }

        // Verify handler has openIframe method
        if (typeof handler.openIframe !== "function") {
          console.error(
            "handler.openIframe is not a function. Handler:",
            handler
          );
          throw new Error(
            "Paystack handler is not properly initialized. Please refresh the page."
          );
        }

        console.log("Opening Paystack payment modal...");
        try {
          handler.openIframe();
          console.log("✅ Paystack modal openIframe() called successfully");
        } catch (popupError) {
          console.error("❌ Error opening Paystack modal:", popupError);
          alert(
            "Failed to open payment window. Error: " +
              popupError.message +
              ". Please check your browser's popup settings or refresh the page."
          );
          if (checkoutBtn) {
            checkoutBtn.innerText = originalText;
            checkoutBtn.disabled = false;
          }
        }
      } else {
        console.error("Payment initialization failed:", data.message);
        alert(
          "Error initializing payment: " + (data.message || "Unknown error")
        );
        // Restore button
        if (checkoutBtn) {
          checkoutBtn.innerText = originalText;
          checkoutBtn.disabled = false;
        }
      }
    })
    .catch((error) => {
      console.error("Payment error:", error);

      // More specific error messages
      let errorMessage = "An error occurred: ";
      if (error.message.includes("PHP server")) {
        errorMessage +=
          "Please use a PHP server to test payments. Live Server and file:// protocol do not support PHP.";
      } else if (
        error.message.includes("Paystack library") ||
        error.message.includes("Payment system not loaded")
      ) {
        errorMessage +=
          "Payment system not loaded. Please refresh the page and try again.";
        // Try to reload the script one more time
        console.log("Attempting to reload Paystack script...");
        loadPaystackScript()
          .then(() => {
            console.log("Paystack script reloaded, please try again");
          })
          .catch((reloadError) => {
            console.error("Failed to reload Paystack:", reloadError);
          });
      } else if (error.message.includes("Invalid server response")) {
        errorMessage += "Server configuration issue. Please contact support.";
      } else {
        errorMessage += error.message;
      }

      alert(errorMessage);
      // Restore button
      if (checkoutBtn) {
        checkoutBtn.innerText = originalText;
        checkoutBtn.disabled = false;
      }
    })
    .finally(() => {
      // Restore button state only if we're not verifying
      if (checkoutBtn && checkoutBtn.innerText !== "Verifying...") {
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

  // Check if Paystack script is loaded and try to load if missing
  function checkPaystackLoaded() {
    if (typeof PaystackPop === "undefined") {
      console.warn("⚠️ Paystack library not loaded. Attempting to load...");
      // Try to load the script
      loadPaystackScript()
        .then(() => {
          console.log("✅ Paystack library loaded successfully");
        })
        .catch((error) => {
          console.error("❌ Failed to load Paystack:", error);
          console.warn(
            "Make sure the script tag is present: <script src='https://js.paystack.co/v1/inline.js'></script>"
          );
        });
    } else {
      console.log("✅ Paystack library loaded successfully");
    }
  }

  // Check immediately and after a delay
  checkPaystackLoaded();
  setTimeout(checkPaystackLoaded, 1000);
  setTimeout(checkPaystackLoaded, 2000);
});
