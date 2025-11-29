<?php
session_start();


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    header("Location: index.php");
    exit();
}


require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

$manager_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT full_name, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$result = $stmt->get_result();
$manager = $result->fetch_assoc();
$stmt->close();

$full_name = $manager['full_name'] ?? 'Manager';
$profile_picture = $manager['profile_picture'] ?? 'user.jpg';


$categories = [];
$result = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC");
while ($row = $result->fetch_assoc()) {
    $categories[] = $row;
}


$db->close();


$success_message = $_SESSION['success_message'] ?? '';
$error_message = $_SESSION['error_message'] ?? '';
unset($_SESSION['success_message']);
unset($_SESSION['error_message']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Event - Bonten</title>
    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/create_event.css">
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>
        const managerId = <?php echo $manager_id; ?>;
        const managerEmail = "<?php echo htmlspecialchars($manager['email']); ?>";
    </script>
    <script src="../public/js/create_event.js" defer></script>
</head>
<body>
    <div class="container">


        <aside class="sidebar">
            <a href="./manager_dashboard.php" style="text-decoration: none;">
                <div class="logo">
                    <h3 class="left">Bon</h3>
                    <h3>ten</h3>
                </div>
            </a>

            <nav class="nav-menu">
                <a href="./manager_dashboard.php" class="nav-item">Home</a>
                <a href="./manager_history.php" class="nav-item">History</a>

                <a href="./create_event.php" class="nav-item active">Create Event</a>
            </nav>

            <div class="lower-menu">
                <a href="./manager_settings.php" class="nav-item">Settings</a>

                <a href="./index.php" class="logout">Logout</a>
            </div>

        </aside>

        <div class="topnav">

            <a
                href="./manager_settings.php"
                class="user_section"
                style="cursor: pointer; text-decoration: none"

            >
                <img
                    src="../public/assets/<?php echo htmlspecialchars($profile_picture); ?>"
                    alt="Profile Picture"

                    class="profile_picture"
                />
                <div class="user_info">

                    <h4 class="username" id="managerName"><?php echo htmlspecialchars($full_name); ?></h4>
                </div>
            </a>
        </div>

        <main class="main-content">
            <div class="page-header">
                <div class="header-text">
                    <h1>Create <span class="italic">New Event</span></h1>

                    <p>Fill in the details to create your event</p>
                </div>
            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>

            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>
            <?php endif; ?>

            
            <div class="progress-container">
                <div class="progress-steps">
                    <div class="step active" data-step="1">

                        <div class="step-number">1</div>
                        <span class="step-label">Basic Info</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="2">

                        <div class="step-number">2</div>
                        <span class="step-label">Date & Location</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="3">
                        <div class="step-number">3</div>
                        <span class="step-label">Tickets</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step" data-step="4">

                        <div class="step-number">4</div>
                        <span class="step-label">Review</span>
                    </div>
                </div>
                <div class="progress-bar">
                    <div class="progress-fill" id="progressFill"></div>
                </div>
            </div>

            
            <form id="createEventForm" class="event-form" method="POST" action="../src/Controllers/handle_create_event.php" enctype="multipart/form-data">

                
                <div class="form-step active" data-step="1">
                    <div class="step-content">
                        <h2 class="step-title">Basic Information</h2>
                        <p class="step-description">Let's start with the essentials about your event</p>

                        <div class="form-grid">
                            <div class="form-group full-width">

                                <label for="eventName">Event Name <span class="required">*</span></label>
                                <input type="text" id="eventName" name="eventName" placeholder="Enter your event name" required maxlength="100">
                                <span class="char-count"><span id="nameCount">0</span>/100</span>
                            </div>

                            <div class="form-group">
                                <label for="eventCategory">Category <span class="required">*</span></label>

                                <select id="eventCategory" name="eventCategory" required>
                                    <option value="" disabled selected>Select category</option>
                                    <?php foreach ($categories as $category): ?>
                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <div class="form-group">
                                <label for="eventStatus">Visibility <span class="required">*</span></label>
                                <select id="eventStatus" name="eventStatus" required>
                                    <option value="active">Public - Anyone can find</option>
                                    <option value="draft">Draft - Save for later</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label for="eventDescription">Description <span class="required">*</span></label>

                                <textarea id="eventDescription" name="eventDescription" rows="5" placeholder="Describe your event, what attendees can expect, highlights, etc." required maxlength="2000"></textarea>
                                <span class="char-count"><span id="descCount">0</span>/2000</span>
                            </div>

                            <div class="form-group full-width">
                                <label>Event Image <span class="required">*</span></label>

                                <div class="image-upload-container">
                                    <div class="image-preview" id="imagePreview">
                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>
                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>
                                            <polyline points="21 15 16 10 5 21"></polyline>
                                        </svg>
                                        <p>Click to upload or drag and drop</p>
                                        <span>PNG, JPG up to 5MB</span>
                                    </div>
                                    <input type="file" id="eventImage" name="eventImage" accept="image/*" required>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="form-step" data-step="2">
                    <div class="step-content">

                        <h2 class="step-title">Date & Location</h2>
                        <p class="step-description">When and where is your event happening?</p>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="eventStartDate">Event Date <span class="required">*</span></label>
                                <input type="date" id="eventStartDate" name="eventStartDate" required>
                            </div>

                            <div class="form-group">
                                <label for="eventStartTime">Event Time <span class="required">*</span></label>
                                <input type="time" id="eventStartTime" name="eventStartTime" required>
                            </div>

                            <div class="form-group full-width">
                                <label>Event Type <span class="required">*</span></label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="eventType" value="in-person" checked required>
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">In-Person</span>

                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="eventType" value="online">
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Online</span>
                                    </label>
                                    <label class="radio-option">
                                        <input type="radio" name="eventType" value="hybrid">
                                        <span class="radio-custom"></span>
                                        <span class="radio-label">Hybrid</span>
                                    </label>
                                </div>
                            </div>

                            <div class="form-group full-width location-fields" id="locationFields">
                                <label for="eventVenue">Venue/Location <span class="required">*</span></label>
                                <input type="text" id="eventVenue" name="eventVenue" placeholder="e.g., Ashesi University, Labadi Beach" required>
                            </div>

                            <div class="form-group location-fields">
                                <label for="eventCity">City <span class="required">*</span></label>
                                <input type="text" id="eventCity" name="eventCity" placeholder="e.g., Accra" required>
                            </div>

                            <div class="form-group full-width">
                                <label for="eventCapacity">Event Capacity</label>
                                <input type="number" id="eventCapacity" name="eventCapacity" placeholder="Maximum number of attendees" min="1">
                                <span class="helper-text">Leave empty for unlimited</span>

                            </div>
                        </div>
                    </div>
                </div>

               
                <div class="form-step" data-step="3">
                    <div class="step-content">
                        <h2 class="step-title">Tickets</h2>
                        <p class="step-description">Set up your ticket types and pricing</p>

                        <div class="ticket-type-toggle">
                            <label class="toggle-option">

                                <input type="radio" name="ticketType" value="free" checked>
                                <span class="toggle-btn">Free Event</span>
                            </label>
                            <label class="toggle-option">
                                <input type="radio" name="ticketType" value="paid">
                                <span class="toggle-btn">Paid Event</span>

                            </label>
                        </div>

                        <div class="tickets-container" id="ticketsContainer">
                           
                            <div class="free-ticket-section" id="freeTicketSection">
                                <div class="ticket-card">
                                    <div class="ticket-header">
                                        <span class="ticket-icon">🎟️</span>
                                        <h3>Free Registration</h3>
                                    </div>
                                    <div class="form-group">
                                        <label for="freeTicketQuantity">Number of Free Tickets</label>
                                        <input type="number" id="freeTicketQuantity" name="freeTicketQuantity" value="100" min="1">
                                        <span class="helper-text">How many free registrations are available?</span>
                                    </div>
                                </div>
                            </div>

                            
                            <div class="paid-tickets-section" id="paidTicketsSection" style="display: none;">
                                <div class="tickets-list" id="ticketsList">
                                 
                                    <div class="ticket-card" data-ticket-index="0">
                                        <div class="ticket-header">

                                            <h3>Regular Ticket</h3>
                                        </div>
                                        <input type="hidden" name="tickets[0][name]" value="Regular">
                                        <div class="form-group">
                                            <label>Price (GHS)</label>
                                            <input type="number" name="tickets[0][price]" placeholder="0.00" min="0" step="0.01" required>

                                        </div>
                                        <div class="form-group">

                                            <label>Quantity</label>
                                            <input type="number" name="tickets[0][quantity]" placeholder="100" min="1" required>

                                        </div>
                                    </div>

                                    <div class="ticket-card" data-ticket-index="1">
                                        <div class="ticket-header">
                                            <h3>VIP Ticket</h3>
                                        </div>
                                        <input type="hidden" name="tickets[1][name]" value="VIP">
                                        <div class="form-group">
                                            <label>Price (GHS)</label>
                                            <input type="number" name="tickets[1][price]" placeholder="0.00" min="0" step="0.01" required>
                                        </div>
                                        <div class="form-group">
                                            <label>Quantity</label>
                                            <input type="number" name="tickets[1][quantity]" placeholder="50" min="1" required>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

      
                <div class="form-step" data-step="4">

                    <div class="step-content">

                        <h2 class="step-title">Review Your Event</h2>
                        <p class="step-description">Double-check everything before publishing</p>

                        <div class="review-container">
                            <div class="review-preview">

                                <div class="preview-image" id="reviewImage">
                                    <img src="../public/assets/hero.png" alt="Event preview" id="reviewImageSrc">
                                </div>
                                <div class="preview-content">
                                    <span class="preview-category" id="reviewCategory">Category</span>
                                    <h2 class="preview-title" id="reviewTitle">Event Title</h2>
                                    <p class="preview-description" id="reviewDescription">Event description will appear here...</p>
                                </div>
                            </div>

                            <div class="review-details">
                                <div class="review-section">
                                    <h3>Date & Time</h3>
                                    <div class="review-item">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                                            <rect x="3" y="4" width="18" height="18" rx="2" ry="2"></rect>
                                            <line x1="16" y1="2" x2="16" y2="6"></line>

                                            <line x1="8" y1="2" x2="8" y2="6"></line>
                                            <line x1="3" y1="10" x2="21" y2="10"></line>
                                        </svg>
                                        <span id="reviewDateTime">-</span>
                                    </div>
                                </div>

                                <div class="review-section">
                                    <h3>Location</h3>
                                    <div class="review-item">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                            <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"></path>
                                            <circle cx="12" cy="10" r="3"></circle>
                                        </svg>
                                        <span id="reviewLocation">-</span>
                                    </div>
                                </div>

                                <div class="review-section">
                                    <h3>Tickets</h3>
                                    <div class="review-tickets" id="reviewTickets">
                                        
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                
                <div class="form-navigation">

                    <button type="button" class="btn-secondary" id="prevBtn" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15 18 9 12 15 6"></polyline>
                        </svg>
                        Previous
                    </button>
                    <div class="nav-spacer"></div>
                    <button type="button" class="btn-primary" id="nextBtn">

                        Next
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>
                    <button type="submit" class="btn-primary" id="publishBtn" style="display: none;" name="publish_event">

                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Publish Event
                    </button>
                </div>
            </form>
        </main>
    </div>

   
    <div id="success-modal" class="modal">

        <div class="modal-overlay"></div>
        <div class="modal-content success-modal">
            <div class="success-icon">
                <svg width="64" height="64" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"></path>
                    <polyline points="22 4 12 14.01 9 11.01"></polyline>
                </svg>
            </div>
            <h2 class="modal-title">Event Created!</h2>

            <p class="modal-message" id="successMessage">Your event has been published successfully.</p>
            <div class="modal-actions">
                <button class="btn-secondary" id="viewEventBtn">View Event</button>
                <button class="btn-primary" id="goToDashboardBtn">Go to Dashboard</button>
            </div>
        </div>
    </div>

<script>

document.addEventListener('DOMContentLoaded', function() {
    const publishBtn = document.getElementById('publishBtn');

    const form = document.getElementById('createEventForm');

    if (publishBtn && form) {
        
        const newPublishBtn = publishBtn.cloneNode(true);
        publishBtn.parentNode.replaceChild(newPublishBtn, publishBtn);

       
        newPublishBtn.addEventListener('click', function(e) {
            e.preventDefault();
            
            if (form.checkValidity()) {
                form.submit();
            } else {
                form.reportValidity();
            }

        });
    }



    const goToDashboardBtn = document.getElementById('goToDashboardBtn');

    if (goToDashboardBtn) {
        goToDashboardBtn.addEventListener('click', function() {
            window.location.href = 'manager_dashboard.php';
        });
    }

});
</script>

<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z" data-position="3"></script>
</body>
</html>
