<?php

require_once '../config/security.php';
require_once '../config/image_helpers.php';

set_security_headers();

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

    <script src="../public/js/profile_loader.js"></script>
    <script src="../public/js/logout_handler.js"></script>
    <script src="../public/js/create_event.js"></script>


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

                <a href="./logout.php" class="logout">Logout</a>

            </div>

        </aside>

        <div class="topnav">

            <a


                href="./manager_settings.php"


                class="user_section"
                style="cursor: pointer; text-decoration: none"

            >
                <img
                    src="<?php echo htmlspecialchars(get_profile_picture_path($profile_picture)); ?>"
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


                                <label for="eventVisibility">Visibility <span class="required">*</span></label>

                                <select id="eventVisibility" name="eventVisibility" required>
                                    <option value="public">Public - Anyone can find</option>

                                    <option value="private">Private - Invite only</option>

                                </select>

                            </div>

                            <!-- Hidden field to always set status as active for new events -->
                            <input type="hidden" id="eventStatus" name="eventStatus" value="active">

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

                                    <input type="file" id="eventImage" name="eventImage" accept="image/*">

                                </div>
                            </div>

                            <div class="form-group full-width">
                                <label>Tags</label>

                                <div class="tags-input-container">
                                    <div class="tags-list" id="tagsList"></div>
                                    <input type="text" id="tagsInput" placeholder="Type and press Enter to add tags">
                                </div>

                                <span class="helper-text">Add up to 5 tags to help people discover your event</span>
                            </div>

                        </div>

                    </div>
                </div>

                <!-- Step 2: Date & Location -->
                <div class="form-step" data-step="2">
                    <div class="step-content">
                        <h2 class="step-title">Date & Location</h2>
                        <p class="step-description">When and where is your event happening?</p>

                        <div class="form-grid">
                            <div class="form-group">
                                <label for="eventStartDate">Start Date <span class="required">*</span></label>
                                <input type="date" id="eventStartDate" name="eventStartDate" required>
                            </div>

                            <div class="form-group">
                                <label for="eventStartTime">Start Time <span class="required">*</span></label>
                                <input type="time" id="eventStartTime" name="eventStartTime" required>
                            </div>

                            <div class="form-group">
                                <label for="eventEndDate">End Date</label>
                                <input type="date" id="eventEndDate" name="eventEndDate">
                            </div>

                            <div class="form-group">
                                <label for="eventEndTime">End Time</label>
                                <input type="time" id="eventEndTime" name="eventEndTime">
                            </div>

                            <div class="form-group full-width">
                                <label for="eventTimezone">Timezone</label>
                                <select id="eventTimezone" name="eventTimezone">
                                    <option value="GMT">GMT (Greenwich Mean Time)</option>
                                    <option value="WAT" selected>WAT (West Africa Time)</option>
                                </select>
                            </div>

                            <div class="form-group full-width">
                                <label>Event Type <span class="required">*</span></label>
                                <div class="radio-group">
                                    <label class="radio-option">
                                        <input type="radio" name="eventType" value="in-person" checked>
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

                            <div class="form-group full-width location-fields">
                                <label for="eventVenue">Venue Name <span class="required">*</span></label>
                                <input type="text" id="eventVenue" name="eventVenue" placeholder="e.g., Ashesi University, Labadi Beach">
                            </div>

                            <div class="form-group location-fields">
                                <label for="eventCity">City <span class="required">*</span></label>
                                <input type="text" id="eventCity" name="eventCity" placeholder="e.g., Accra">
                            </div>

                            <div class="form-group full-width online-fields" style="display: none;">
                                <label for="eventPlatform">Platform</label>
                                <select id="eventPlatform" name="eventPlatform">
                                    <option value="">Select platform</option>
                                    <option value="zoom">Zoom</option>
                                    <option value="teams">Microsoft Teams</option>
                                    <option value="meet">Google Meet</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>

                            <div class="form-group full-width online-fields" style="display: none;">
                                <label for="eventStreamUrl">Stream/Meeting URL</label>
                                <input type="url" id="eventStreamUrl" name="eventStreamUrl" placeholder="https://">
                                <span class="helper-text">This will be shared with ticket holders only</span>
                            </div>

                            <div class="form-group full-width">
                                <label for="eventCapacity">Event Capacity</label>
                                <input type="number" id="eventCapacity" name="eventCapacity" placeholder="Maximum number of attendees" min="1">
                                <span class="helper-text">Leave empty for unlimited</span>
                            </div>

                        </div>
                    </div>
                </div>

                <!-- Step 3: Tickets -->
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
                                        <span class="ticket-icon">🎫</span>
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
                                </div>

                                <button type="button" class="add-ticket-btn" id="addTicketBtn">
                                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                    Add Ticket Type
                                </button>
                            </div>
                        </div>

                        <div class="additional-options">
                            <h3>Additional Options</h3>

                            <label class="checkbox-option">
                                <input type="checkbox" id="requireApproval" name="requireApproval">
                                <span class="checkbox-custom"></span>
                                <div class="checkbox-content">
                                    <span class="checkbox-label">Require approval for registrations</span>
                                    <span class="checkbox-description">You'll need to manually approve each registration</span>
                                </div>
                            </label>

                            <label class="checkbox-option">
                                <input type="checkbox" id="collectPhone" name="collectPhone">
                                <span class="checkbox-custom"></span>
                                <div class="checkbox-content">
                                    <span class="checkbox-label">Collect phone numbers</span>
                                    <span class="checkbox-description">Request attendees' phone numbers during registration</span>
                                </div>
                            </label>

                            <label class="checkbox-option">
                                <input type="checkbox" id="allowRefunds" name="allowRefunds" checked>
                                <span class="checkbox-custom"></span>
                                <div class="checkbox-content">
                                    <span class="checkbox-label">Allow refunds</span>
                                    <span class="checkbox-description">Attendees can request refunds up to 24 hours before the event</span>
                                </div>
                            </label>
                        </div>

                    </div>
                </div>

                <!-- Step 4: Review -->
                <div class="form-step" data-step="4">
                    <div class="step-content">
                        <h2 class="step-title">Review Your Event</h2>
                        <p class="step-description">Double-check everything before publishing</p>

                        <div class="review-container">
                            <div class="review-grid">
                                <div class="review-section">
                                    <h3>Basic Info</h3>
                                    <div class="review-item">
                                        <span id="reviewName">Event Name</span>
                                    </div>
                                    <div class="review-item">
                                        <span id="reviewCategory">Category</span>
                                    </div>
                                    <div class="review-item">
                                        <span id="reviewDescription">Description</span>
                                    </div>
                                </div>

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

                                <div class="review-section">
                                    <h3>Settings</h3>
                                    <div class="review-settings" id="reviewSettings">
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

                    <button type="button" class="btn-outline" id="saveDraftBtn">Save as Draft</button>

                    <button type="button" class="btn-primary" id="nextBtn">
                        Next
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9 18 15 12 9 6"></polyline>
                        </svg>
                    </button>

                    <button type="submit" class="btn-primary" id="publishBtn" style="display: none;">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="20 6 9 17 4 12"></polyline>
                        </svg>
                        Publish Event
                    </button>
                </div>

            </form>

        </main>
    </div>

    <script>
        const createEventController = new CreateEventController();
    </script>

</body>
</html>