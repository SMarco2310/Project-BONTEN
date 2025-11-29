<?php
session_start();

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


$events = [];
$stmt = $conn->prepare("
    SELECT e.event_id, e.name, e.description, e.image_path, e.location, e.city,
           e.event_date, e.event_time, c.name as category_name
    FROM events e
    LEFT JOIN categories c ON e.category_id = c.category_id
    WHERE e.status = 'active'
    ORDER BY e.event_date ASC, e.created_at DESC
");
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $events[] = $row;
}
$stmt->close();

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Explore Events</title>
    <link rel="stylesheet" href="../public/css/style.css">
    <link rel="stylesheet" href="../public/css/event.css">
    <link rel="stylesheet" href="../public/css/explore.css">
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">
    <script src="../public/js/language.js"></script>
    <script src="../public/js/profile_loader.js"></script>
    <script src="../public/js/explore.js" defer></script>
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
            <a href="./explore.php" class="nav-item active" data-translate="explore">Explore</a>
            <a href="./history.php" class="nav-item" data-translate="history">History</a>

            </nav>

            <div class="lower-menu">
                <a href="./settings.php" class="nav-item" data-translate="settings">Settings</a>
                <a href="./index.php" class="logout" data-translate="logout">Logout</a>

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
            <div class="explore-container">

                <!-- Search Section -->
                <section class="search-section" id="search-section">
                    <div class="search-container">
                        <div class="search-wrapper">
                            <input type="text" id="search-input" class="search-input" placeholder="Search events by name, location, or category...">
                            <button class="search-btn" id="search-btn">
                                <img src="../public/assets/search.png" alt="Search" class="search-icon">
                            </button>
                        </div>
                        <div class="search-filters">
                            <select id="category-filter" class="filter-select">
                                <option value="">All Categories</option>
                                <option value="concert">Concert</option>
                                <option value="fashion">Fashion</option>
                                <option value="football">Football</option>
                                <option value="asc-week">ASC Week</option>
                                <option value="food">Food</option>
                            </select>
                            <select id="location-filter" class="filter-select">
                                <option value="">All Locations</option>
                                <option value="accra">Accra</option>
                                <option value="kumasi">Kumasi</option>
                                <option value="east-legon">East Legon</option>
                                <option value="labadi">Labadi</option>
                            </select>
                        </div>
                    </div>
                </section>

                <!-- Trending Events Section -->
                <section class="events-section">
                    <h2 class="section-title" data-translate="trendingEvents">All Events</h2>
                    <div class="carousel-wrapper">
                        <button class="carousel-arrow prev">‹</button>
                        <div class="events-carousel" id="trending-carousel">

                            <?php if (count($events) > 0): ?>
                                <?php foreach ($events as $event): ?>
                                <a href="event.php?id=<?php echo $event['event_id']; ?>" style="text-decoration: none; color: inherit;">
                                    <div class="event-card">
                                        <div class="card-image">
                                            <img src="<?php echo htmlspecialchars($event['image_path'] ?? '../public/assets/hero.png'); ?>"
                                                 alt="<?php echo htmlspecialchars($event['name']); ?>">
                                            <span class="event-badge"><?php echo htmlspecialchars($event['category_name'] ?? 'Event'); ?></span>
                                        </div>
                                        <div class="card-content">
                                            <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
                                            <div class="event-meta">
                                                <span class="event-location"><?php echo htmlspecialchars($event['city'] ?? $event['location']); ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </a>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <div style="padding: 40px; text-align: center; color: #999;">
                                    <p>No events available at the moment. Check back soon!</p>
                                </div>
                            <?php endif; ?>

                        </div>
                        <button class="carousel-arrow next">›</button>
                    </div>
                </section>

            </div>
        </div>

    </div>

<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z" data-position="3"></script>
</body>
</html>
