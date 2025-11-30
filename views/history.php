<?php

require_once '../config/security.php';

set_security_headers();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'user') {
    header("Location: index.php");
    exit();
}

require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT full_name, email, profile_picture FROM users WHERE user_id = ?");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user = $result->fetch_assoc();

$stmt->close();

$full_name = $user['full_name'] ?? 'User';

$profile_picture = $user['profile_picture'] ?? 'user.jpg';


$stmt = $conn->prepare("
    SELECT e.event_id, e.name, e.event_date, e.event_time, e.location, e.image_path, c.name as category_name
    FROM rsvps r
    JOIN events e ON r.event_id = e.event_id
    LEFT JOIN categories c ON e.category_id = c.category_id
    WHERE r.user_id = ? AND e.event_date >= CURDATE()
    ORDER BY e.event_date ASC
");
$stmt->bind_param("i", $user_id);

$stmt->execute();

$upcoming_events = $stmt->get_result();

$stmt->close();


$stmt = $conn->prepare("
    SELECT e.event_id, e.name, e.event_date, e.event_time, e.location, e.image_path, c.name as category_name
    FROM rsvps r
    JOIN events e ON r.event_id = e.event_id
    LEFT JOIN categories c ON e.category_id = c.category_id
    WHERE r.user_id = ? AND e.event_date < CURDATE()
    ORDER BY e.event_date DESC
");
$stmt->bind_param("i", $user_id);

$stmt->execute();

$past_events = $stmt->get_result();

$stmt->close();

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History</title>
    <link rel="stylesheet" href="../public/css/event.css">
    <link rel="stylesheet" href="../public/css/history.css">
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">
    <script src="../public/js/language.js"></script>
    <script src="../public/js/profile_loader.js"></script>
</head>
<body>
<div class="container">


        <aside class="sidebar">
            <a href="./user_homepage.php" style="text-decoration: none;">
                <div class="logo">
                    <h3 class="left">Bon</h3>
                    <h3>ten</h3>
                </div>
            </a>

            <nav class="nav-menu">

            <a href="./user_homepage.php" class="nav-item" data-translate="home">Home</a>
            <a href="./explore.php" class="nav-item" data-translate="explore">Explore</a>

            <a href="./history.php" class="nav-item active" data-translate="history">History</a>

            </nav>

            <div class="lower-menu">
                <a href="./settings.php" class="nav-item" data-translate="settings">Settings</a>
                <a href="./logout.php" class="logout" data-translate="logout">Logout</a>

            </div>

        </aside>

        <div class="topnav">
            <a
                href="./settings.php"
                class="user_section"
                style="cursor: pointer; text-decoration: none"
            >
                <img
                    src="../public/assets/<?php echo htmlspecialchars($profile_picture); ?>"
                    alt="Profile Picture"
                    class="profile_picture"
                />
                <div class="user_info">
                    <h4 class="username"><?php echo htmlspecialchars($full_name); ?></h4>
                </div>
            </a>
        </div>

        <div class="main-body">
            <div class="history-content">

                
                <section class="history-section">

                    <div class="section-header">

                        <h2 class="section-title" data-translate="upcomingEvents">Upcoming Events</h2>
                        <p class="section-subtitle" data-translate="eventsRegisteredFor">Events you've registered for</p>

                    </div>

                    <div class="events-grid">
                        <?php if ($upcoming_events->num_rows > 0): ?>
                            <?php while ($event = $upcoming_events->fetch_assoc()): ?>
                                <div class="history-card" data-event-id="<?php echo $event['event_id']; ?>">
                                    <div class="card-image-wrapper">
                                        <?php
                                        
                                        
                                        $image_src = '../public/assets/bonten.png'; 
                                        
                                        if (!empty($event['image_path'])) {
                                            $event_image = $event['image_path'];
                                            
                                            
                                            if (strpos($event_image, '../public/') === 0) {

                                                $image_src = substr($event_image, 3);

                                            } else if (strpos($event_image, 'public/') === 0) {
                                                
                                                $image_src = '../' . $event_image;
                                            } else {
                                                
                                                $image_src = '../public/assets/' . $event_image;
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" class="card-image">
                                        <span class="event-status upcoming">Registered</span>
                                    </div>
                                    <div class="card-content">
                                        <div class="card-header">
                                            <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                                            <span class="event-badge"><?php echo htmlspecialchars($event['category_name'] ?? 'Event'); ?></span>
                                        </div>
                                        <p class="event-date"><?php echo date('F j, Y - g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                                        <p class="event-location"><?php echo htmlspecialchars($event['location']); ?></p>
                                        <div class="card-actions">
                                            <a href="./event.php?id=<?php echo $event['event_id']; ?>"><button class="view-btn" data-translate="viewDetails">View Details</button></a>
                                            <button class="cancel-btn" data-translate="cancelRSVP">Cancel RSVP</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #888; text-align: center; padding: 40px;">No upcoming events. Start exploring!</p>
                        <?php endif; ?>
                    </div>
                </section>

                
                <section class="history-section">
                    <div class="section-header">
                        <h2 class="section-title" data-translate="pastEvents">Past Events</h2>
                        <p class="section-subtitle" data-translate="eventsAttended">Events you've attended</p>
                    </div>

                    <div class="events-grid">
                        <?php if ($past_events->num_rows > 0): ?>
                            <?php while ($event = $past_events->fetch_assoc()): ?>
                                <div class="past-event-card" data-event-id="<?php echo $event['event_id']; ?>" data-event-name="<?php echo htmlspecialchars($event['name']); ?>">
                                    <div class="past-event-image-container">
                                        <?php
                                       
                                        $image_src = '../public/assets/bonten.png'; // default
                                        
                                        if (!empty($event['image_path'])) {
                                            $event_image = $event['image_path'];
                                            
                                           
                                            if (strpos($event_image, '../public/') === 0) {
                                                $image_src = substr($event_image, 3);
                                                
                                            } else if (strpos($event_image, 'public/') === 0) {
                                                
                                                $image_src = '../' . $event_image;

                                            } else {
                                                
                                                $image_src = '../public/assets/' . $event_image;
                                            }
                                        }
                                        ?>
                                        <img src="<?php echo htmlspecialchars($image_src); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" class="past-event-image">
                                        <span class="attended-badge">Attended</span>
                                    </div>

                                    <div class="past-event-details">
                                        <div class="past-event-title-row">
                                            <h3 class="past-event-title"><?php echo htmlspecialchars($event['name']); ?></h3>
                                            <span class="past-event-category"><?php echo htmlspecialchars($event['category_name'] ?? 'Event'); ?></span>
                                        </div>

                                        <p class="past-event-datetime"><?php echo date('F j, Y - g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                                        <p class="past-event-location">📍 <?php echo htmlspecialchars($event['location']); ?></p>

                                        <div class="past-event-actions">
                                            <a href="./event.php?id=<?php echo $event['event_id']; ?>">
                                                <button class="view-details-btn" data-translate="viewDetails">View Details</button>
                                            </a>
                                            <button class="write-review-btn" data-event-id="<?php echo $event['event_id']; ?>" data-event-name="<?php echo htmlspecialchars($event['name']); ?>" data-translate="writeReview">Write Review</button>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <p style="color: #888; text-align: center; padding: 40px;">No past events yet.</p>
                        <?php endif; ?>
                    </div>
                </section>

            </div>
        </div>

    </div>


    <div id="cancel-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content cancel-modal-content">
            
        <button class="modal-close">&times;</button>
        
        <h2 class="modal-title">Cancel RSVP</h2>
        
        <div class="modal-body">
        
        <p class="cancel-message">Are you sure you want to cancel your RSVP for <strong><span id="cancel-event-name"></span></strong>?</p>
        
        <p class="cancel-warning">This action cannot be undone.</p>
        
    </div>
            <div class="modal-actions">
                <button id="cancel-cancel-btn" class="btn-secondary">Keep RSVP</button>
                <button id="cancel-confirm-btn" class="btn-danger">Yes, Cancel RSVP</button>
            </div>
        </div>
    </div>


    <div id="review-modal" class="modal">
        <div class="modal-overlay"></div>
        <div class="modal-content review-modal-content">
            <button class="modal-close">&times;</button>

            <h2 class="review-modal-title">Write a Review</h2>

            <p class="review-modal-subtitle">Share your experience at <span class="event-name-highlight" id="review-event-name"></span></p>

            <form id="review-form" class="review-form">

                <div class="review-form-group">
                    <label class="review-label">Rating *</label>
                    <div id="star-rating" class="star-rating-input">
                        <span class="rating-star" data-rating="1">★</span>
                        <span class="rating-star" data-rating="2">★</span>
                        <span class="rating-star" data-rating="3">★</span>

                        <span class="rating-star" data-rating="4">★</span>
                        <span class="rating-star" data-rating="5">★</span>
                    </div>
                    <input type="hidden" id="rating-value" value="0">
                </div>

                <div class="review-form-group">
                    <label for="review-title" class="review-label">Review Title *</label>
                    <input type="text" id="review-title" class="review-input" placeholder="Sum up your experience" required>
                </div>

                <div class="review-form-group">
                    <label for="review-text" class="review-label">Your Review *</label>
                    <textarea id="review-text" class="review-textarea" rows="6" placeholder="Share details of your experience..." required></textarea>
                </div>

                <button type="submit" id="submit-review-btn" class="submit-review-button">Submit Review</button>
            </form>
        </div>
    </div>

    <script src="../public/js/language.js"></script>
    
    <script src="../public/js/logout_handler.js" defer></script>
    
    <script src="../public/js/history.js" defer></script>

<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>
</body>
</html>
