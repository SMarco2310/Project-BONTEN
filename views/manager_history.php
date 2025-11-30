<?php

require_once '../config/security.php';

set_security_headers();

if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'manager') {
    header("Location: index.php");
    exit();
}

require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

$manager_id = $_SESSION['user_id'];

$full_name = $_SESSION['full_name'];
$profile_picture = $_SESSION['profile_picture'];

// Get summary statistics
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM events WHERE manager_id = ?");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$total_events = $stmt->get_result()->fetch_assoc()['total'];
$stmt->close();

$stmt = $conn->prepare("SELECT SUM(t.sold) as total_sold
                        FROM tickets t
                        JOIN events e ON t.event_id = e.event_id
                        WHERE e.manager_id = ?");

$stmt->bind_param("i", $manager_id);
$stmt->execute();
$tickets_sold = $stmt->get_result()->fetch_assoc()['total_sold'] ?? 0;

$stmt->close();

$stmt = $conn->prepare("SELECT SUM(t.sold * t.price) as total_revenue
                        FROM tickets t
                        JOIN events e ON t.event_id = e.event_id
                        WHERE e.manager_id = ?");
$stmt->bind_param("i", $manager_id);
$stmt->execute();
$total_revenue = $stmt->get_result()->fetch_assoc()['total_revenue'] ?? 0;
$stmt->close();

$stmt = $conn->prepare("SELECT AVG(r.rating) as avg_rating
                        FROM reviews r
                        JOIN events e ON r.event_id = e.event_id
                        WHERE e.manager_id = ?");

$stmt->bind_param("i", $manager_id);
$stmt->execute();
$avg_rating = round($stmt->get_result()->fetch_assoc()['avg_rating'] ?? 0, 1);

$stmt->close();

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event History - Manager</title>
    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/manager_history.css">
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">

    <script src="../public/js/manager_history.js" defer></script>
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
                <a href="./manager_history.php" class="nav-item active">History</a>
                <a href="./create_event.php" class="nav-item">Create Event</a>
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

            <div class="search-wrapper topnav-search">
                <input type="text" class="search-input" id="eventSearch" placeholder="Search events...">
                <svg class="search-icon" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="8"></circle>

                    <path d="m21 21-4.35-4.35"></path>
                </svg>
            </div>
        </div>

        <main class="main-content">
            <div class="page-header">
                <div class="header-text">
                    <h1>Event <span class="italic">History</span></h1>
                    <p>Track and manage all your events</p>
                </div>
                <div class="header-actions">
                    <div class="filter-group">

                        <select class="filter-select" id="statusFilter">
                            <option value="all">All Status</option>
                            <option value="active">Active</option>
                            <option value="completed">Completed</option>
                            <option value="cancelled">Cancelled</option>
                            <option value="draft">Draft</option>
                        </select>
                        <select class="filter-select" id="sortBy">
                            <option value="date-desc">Newest First</option>
                            <option value="date-asc">Oldest First</option>
                            <option value="revenue-desc">Highest Revenue</option>
                            <option value="revenue-asc">Lowest Revenue</option>
                            <option value="tickets-desc">Most Tickets Sold</option>
                        </select>
                    </div>
                    <button class="export-btn" id="exportHistoryBtn">
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"></path>
                            <polyline points="7 10 12 15 17 10"></polyline>
                            <line x1="12" y1="15" x2="12" y2="3"></line>
                        </svg>
                        Export
                    </button>
                </div>
            </div>

            <!-- Stats Summary -->
            <div class="stats-summary">
                <div class="stat-item">

                    <span class="stat-value" id="totalEvents"><?php echo $total_events; ?></span>
                    <span class="stat-label">Total Events</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-value" id="totalTicketsSold"><?php echo number_format($tickets_sold); ?></span>
                    <span class="stat-label">Tickets Sold</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-value" id="totalRevenue">GHC <?php echo number_format($total_revenue); ?></span>
                    <span class="stat-label">Total Revenue</span>
                </div>
                <div class="stat-divider"></div>
                <div class="stat-item">
                    <span class="stat-value" id="avgRating"><?php echo number_format($avg_rating, 1); ?></span>
                    <span class="stat-label">Avg. Rating</span>
                </div>
            </div>

            <!-- Active Events Section -->
            <section class="history-section">
                <div class="section-header">
                    <h2 class="section-title">Active Events</h2>
                    <span class="event-count" id="activeCount">0 events</span>
                </div>
                <div class="events-table-container">
                    <table class="events-table" id="activeEventsTable">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Tickets Sold</th>
                                <th>Revenue</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activeEventsBody">
                            <!-- Active events will be dynamically inserted -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Past Events Section -->
            <section class="history-section">
                <div class="section-header">
                    <h2 class="section-title">Past Events</h2>
                    <span class="event-count" id="pastCount">0 events</span>

                </div>
                <div class="events-table-container">
                    <table class="events-table" id="pastEventsTable">
                        <thead>
                            <tr>
                                <th>Event</th>
                                <th>Date</th>
                                <th>Tickets Sold</th>
                                <th>Revenue</th>
                                <th>Rating</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="pastEventsBody">
                            <!-- Past events will be dynamically inserted -->
                        </tbody>
                    </table>
                </div>
            </section>

            <!-- Cancelled/Draft Events Section -->
            <section class="history-section collapsible">
                <div class="section-header clickable" id="otherEventsHeader">
                    <div class="section-title-group">
                        <h2 class="section-title">Cancelled & Draft Events</h2>
                        <span class="event-count" id="otherCount">0 events</span>

                    </div>
                    <svg class="collapse-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                        <polyline points="6 9 12 15 18 9"></polyline>
                    </svg>
                </div>
                <div class="collapsible-content" id="otherEventsContent">
                    <div class="events-table-container">
                        <table class="events-table" id="otherEventsTable">
                            <thead>
                                <tr>
                                    <th>Event</th>
                                    <th>Created</th>
                                    <th>Status</th>
                                    <th>Reason</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="otherEventsBody">
                                <!-- Cancelled/Draft events will be dynamically inserted -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>

        </main>

    </div>

    <!-- Event Details Modal -->
    <div id="event-details-modal" class="modal">
        <div class="modal-overlay"></div>

        <div class="modal-content event-details-modal">
            <button class="modal-close">&times;</button>
            <div class="modal-header">
                <img src="" alt="" class="modal-event-image" id="modalEventImage">
                <div class="modal-event-info">
                    <h2 class="modal-title" id="modalEventName">Event Name</h2>
                    <span class="modal-badge" id="modalEventStatus">Active</span>
                </div>
            </div>
            <div class="modal-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <span class="detail-label">Date</span>
                        <span class="detail-value" id="modalEventDate">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Location</span>
                        <span class="detail-value" id="modalEventLocation">-</span>

                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Tickets Sold</span>
                        <span class="detail-value" id="modalTicketsSold">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Total Revenue</span>
                        <span class="detail-value" id="modalRevenue">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Avg. Rating</span>
                        <span class="detail-value" id="modalRating">-</span>
                    </div>
                    <div class="detail-item">
                        <span class="detail-label">Check-ins</span>
                        <span class="detail-value" id="modalCheckins">-</span>
                    </div>
                </div>
                <div class="ticket-breakdown">
                    <h3>Ticket Breakdown</h3>
                    <div class="ticket-types" id="modalTicketTypes">
                        <!-- Ticket types will be inserted here -->
                    </div>
                </div>
                <div class="recent-reviews">
                    <h3>Recent Reviews</h3>
                    <div class="reviews-list" id="modalReviews">
                        <!-- Reviews will be inserted here -->
                    </div>
                </div>
            </div>
            <div class="modal-actions">
                <button class="btn-secondary" id="modalEditBtn">Edit Event</button>

                <button class="btn-primary" id="modalViewAnalytics">View Full Analytics</button>
            </div>
        </div>
    </div>

    <!-- Confirm Action Modal -->
    <div id="confirm-modal" class="modal">
        <div class="modal-overlay"></div>

        <div class="modal-content confirm-modal">

            <button class="modal-close">&times;</button>

            <h2 class="modal-title" id="confirmTitle">Confirm Action</h2>
            <p class="confirm-message" id="confirmMessage">Are you sure you want to proceed?</p>

            <div class="modal-actions">
                <button class="btn-secondary" id="confirmCancelBtn">Cancel</button>
                <button class="btn-danger" id="confirmActionBtn">Confirm</button>
            </div>
        </div>
    </div>

    <input type="hidden" id="managerIdData" value="<?php echo $manager_id; ?>">


<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z" data-position="3"></script>
</body>
</html>
