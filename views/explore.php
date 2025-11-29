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

// Fetch user data
$stmt = $conn->prepare("SELECT full_name, email, profile_picture FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

$full_name = $user['full_name'] ?? 'User';
$profile_picture = $user['profile_picture'] ?? 'user.jpg';

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
                    <h2 class="section-title" data-translate="trendingEvents">Trending Events</h2>
                    <div class="carousel-wrapper">
                        <button class="carousel-arrow prev">‹</button>
                        <div class="events-carousel" id="trending-carousel">

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/a.png" alt="Afro Nation">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Afro Nation</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> East Legon </span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/sank.jpg" alt="Sankrofi">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name"> Sankrofi </h3>
                                    <div class="event-meta">
                                        <span class="event-location">  Asahley Botwe </span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/phoenix.avif" alt="Phoenix">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name"> Phoenix</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Kumasi </span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/bO.jpg" alt="Band Out!">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Band Out!</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Oyarifa</span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/osibisa.avif" alt="Osibisa">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Osibisa</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Jazz Bar</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <button class="carousel-arrow next">›</button>
                    </div>
                </section>


                                <!-- Events Around You Section -->
                <section class="events-section">
                    <h2 class="section-title" data-translate="eventsSuggestedForYou">Events Around You</h2>
                    <div class="carousel-wrapper">
                        <button class="carousel-arrow prev">‹</button>
                        <div class="events-carousel" id="suggested-carousel">

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/tgma.jpg" alt="TGMA">
                                    <span class="event-badge">Awards Ceremony</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">TGMA</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> AICC </span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/waakye.JPG" alt="Waakye Festival">
                                    <span class="event-badge">Food Festival</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Waakye Festival</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Adenta </span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/gh.jpg" alt=" Black Stars">
                                    <span class="event-badge">Football</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Ghana World Cup Qualifiers </h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Accra Sports Stadium</span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/nsmq.jpg" alt="NSMQ">
                                    <span class="event-badge">Quiz</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">NSMQ</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> AICC </span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/beehive.webp" alt="Beehive Festival">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name"> Beehive Festival</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Bloombar </span>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <button class="carousel-arrow next">›</button>
                    </div>
                </section>


                                <!-- Events Suggested For You Section -->
                <section class="events-section">
                    <h2 class="section-title" data-translate="eventsSuggestedForYou">Events Suggested For You</h2>
                    <div class="carousel-wrapper">
                        <button class="carousel-arrow prev">‹</button>
                        <div class="events-carousel" id="suggested-carousel">

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/ashchella.jpg" alt="Ashchella">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Ashchella</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Ashesi University</span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/y2k.JPG" alt="Y2K Neon">
                                    <span class="event-badge">Beach</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Y2K Neon</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Lemon Beach</span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/tidalrave.jpg" alt="Tidal Rave">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">Tidal Rave</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Labadi Beach</span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/gff.jpg" alt="GFF">
                                    <span class="event-badge">Food</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">GFF</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Accra Mall</span>
                                    </div>
                                </div>
                            </div>

                            <div class="event-card">
                                <div class="card-image">
                                    <img src="../public/assets/imullar.jpg" alt="iMullar">
                                    <span class="event-badge">Concert</span>

                                </div>
                                <div class="card-content">
                                    <h3 class="event-name">iMullar</h3>
                                    <div class="event-meta">
                                        <span class="event-location"> Jazz Bar</span>
                                    </div>
                                </div>
                            </div>

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
