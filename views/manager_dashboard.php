
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

$first_name = explode(' ', $full_name)[0];

$profile_picture = $_SESSION['profile_picture'];

$stmt = $conn->prepare("SELECT COUNT(*) as total FROM events WHERE manager_id = ? AND status = 'active'");

$stmt->bind_param("i", $manager_id);

$stmt->execute();

$active_events = $stmt->get_result()->fetch_assoc()['total'];
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

$stmt = $conn->prepare("SELECT r.review_id, u.full_name, r.review_text, r.rating, r.created_at, e.name as event_name
                        FROM reviews r
                        JOIN users u ON r.user_id = u.user_id
                        JOIN events e ON r.event_id = e.event_id
                        WHERE e.manager_id = ?
                        ORDER BY r.created_at DESC LIMIT 10");
$stmt->bind_param("i", $manager_id);

$stmt->execute();

$reviews = $stmt->get_result();

$stmt->close();

$db->close();

?>
<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Event Manager Dashboard</title>./
    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/manager_dashboard.css">
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="../public/js/manager_dashboard.js" defer></script>
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
                <a href="./manager_dashboard.php" class="nav-item active">Home</a>

                <a href="./manager_history.php" class="nav-item">History</a>
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
                    id="headerAvatar"
                />
                <div class="user_info">
                    <h4 class="username" id="managerName"><?php echo htmlspecialchars($full_name); ?></h4>
                </div>
            </a>
        </div>

        <main class="main-content">
            <div class="welcome-header">
                <div class="welcome-text">
                    <h1><span class="italic">Welcome</span> back, <span id="welcomeName"><?php echo htmlspecialchars($first_name); ?></span>!</h1>
                    <p>Here's an overview of your events and performance</p>
                </div>
            </div>

            <div class="dashboard-grid">
                <section class="summary-section card">
                    <div class="section-header">
                        <h2>Summary</h2>
                        <p class="section-subtitle">Track your ticket sales</p>
                        <select class="period-selector" id="summaryPeriod">
                            <option value="january">January</option>
                            <option value="february">February</option>
                            <option value="march">March</option>
                            <option value="april">April</option>
                            <option value="may">May</option>
                            <option value="june">June</option>
                            <option value="july">July</option>
                            <option value="august">August</option>
                            <option value="september">September</option>
                            <option value="october">October</option>
                            <option value="november">November</option>
                            <option value="december">December</option>

                        </select>
                    </div>

                    <div class="metrics-row">
                        <div class="metric-card primary">
                            <div class="metric-header">
                                <span class="metric-dot"></span>
                                <span class="metric-label">Total Revenue</span>
                                <svg class="metric-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>

                                </svg>
                            </div>
                            <div class="metric-value" id="totalRevenue">GHC<?php echo number_format($total_revenue, 2); ?></div>
                            <div class="metric-change positive" id="revenueChange">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 14l5-5 5 5H7z"/>
                                </svg>
                                <span>0%</span>
                            </div>
                        </div>

                        <div class="metric-card primary">

                            <div class="metric-header">

                                <span class="metric-dot"></span>

                                <span class="metric-label">Tickets Sold</span>
                                <svg class="metric-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                            <div class="metric-value" id="ticketsSold"><?php echo $tickets_sold; ?></div>
                            <div class="metric-change negative" id="ticketsChange">
                                <svg width="12" height="12" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M7 10l5 5 5-5H7z"/>

                                </svg>
                                <span>0%</span>
                            </div>
                        </div>

                        <div class="metric-card primary">
                            <div class="metric-header">
                                <span class="metric-dot"></span>
                                <span class="metric-label">Active Events</span>
                                <svg class="metric-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <polyline points="9 18 15 12 9 6"></polyline>
                                </svg>
                            </div>
                            <div class="metric-value" id="activeEvents"><?php echo $active_events; ?></div>
                        </div>
                    </div>
                </section>

                <section class="statistics-section card">
                    <div class="section-header">
                        <h2>Statistics</h2>
                    </div>
                    <div class="chart-container">
                        <canvas id="salesChart"></canvas>
                    </div>
                    <div class="chart-legend">
                        <div class="legend-item">
                            <span class="legend-dot sales"></span>
                            <span>Sales</span>
                        </div>
                        <div class="legend-item">
                            <span class="legend-dot returns"></span>
                            <span>Returns</span>
                        </div>
                    </div>
                </section>

                <section class="secondary-metrics">
                    <div class="metric-card secondary">
                        <div class="metric-header">
                            <span class="metric-label">Avg. Rating</span>
                            <svg class="metric-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="metric-value-row">
                            <svg class="trend-icon negative" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5H7z"/>
                            </svg>
                            <span class="metric-value" id="avgRating"><?php echo $avg_rating; ?>/5</span>
                        </div>
                    </div>

                    <div class="metric-card secondary">
                        <div class="metric-header">
                            <span class="metric-label">Engagement Rate</span>
                            <svg class="metric-arrow" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <polyline points="9 18 15 12 9 6"></polyline>
                            </svg>
                        </div>
                        <div class="metric-value-row">
                            <svg class="trend-icon negative" width="16" height="16" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M7 10l5 5 5-5H7z"/>
                            </svg>
                            <span class="metric-value" id="engagementRate">0%</span>
                        </div>
                    </div>
                </section>

                <section class="search-section">
                    <div class="search-wrapper">
                        <input type="text" class="search-input" id="insightsSearch" placeholder="Search for event insights">
                        <svg class="search-icon" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <circle cx="11" cy="11" r="8"></circle>
                            <path d="m21 21-4.35-4.35"></path>
                        </svg>
                    </div>
                </section>

                <section class="reviews-section card">
                    <div class="section-header">
                        <h2>Event Reviews</h2>
                        <button class="icon-btn add-btn" id="addReviewBtn" title="Add Review">
                            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <line x1="12" y1="5" x2="12" y2="19"></line>
                                <line x1="5" y1="12" x2="19" y2="12"></line>
                            </svg>
                        </button>
                    </div>
                    <div class="reviews-table-container">
                        <table class="reviews-table" id="reviewsTable">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th>Name</th>
                                    <th>Comment</th>
                                    <th>Date</th>
                                    <th>Rating</th>
                                </tr>
                            </thead>
                            <tbody id="reviewsTableBody">
                                <?php if ($reviews->num_rows > 0): ?>
                                    <?php while ($review = $reviews->fetch_assoc()): ?>
                                        <tr>
                                            <td></td>
                                            <td><?php echo htmlspecialchars($review['full_name']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($review['review_text'], 0, 50)) . '...'; ?></td>
                                            <td><?php echo date('M d, Y', strtotime($review['created_at'])); ?></td>
                                            <td><?php echo $review['rating']; ?>/5</td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" style="text-align: center;">No reviews yet</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>

            </div>
        </main>

    </div>

<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z" data-position="3"></script>
</body>
</html>
