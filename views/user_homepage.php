
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

$full_name = $_SESSION['full_name'];

$first_name = explode(' ', $full_name)[0];

$profile_picture = $_SESSION['profile_picture'];

$stmt = $conn->prepare("SELECT e.event_id, e.name, e.event_date, e.event_time, e.image_path
                        FROM rsvps r
                        JOIN events e ON r.event_id = e.event_id
                        WHERE r.user_id = ? AND e.event_date >= CURDATE()
                        ORDER BY e.event_date ASC LIMIT 5");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user_events = $stmt->get_result();
$stmt->close();

$stmt = $conn->prepare("SELECT event_id, name, description, image_path, category_id
                        FROM events
                        WHERE status = 'active' AND event_date >= CURDATE()
                        ORDER BY event_date ASC");
$stmt->execute();
$all_events = $stmt->get_result();
$stmt->close();

$db->close();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Homepage</title>
    <link rel="stylesheet" href="../public/css/style.css" />

    <link rel="stylesheet" href="../public/css/user_homepage.css" />
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon" />
   
    <script src="../public/js/language.js"></script>
    <script src="../public/js/profile_loader.js"></script>
   
    <script src="../public/js/user_homepage.js" defer></script>
  
  </head>
  <body>
    <div class="container">
      <aside class="sidebar">
        <a href="./user_homepage.php" style="text-decoration: none">
          <div class="logo">
            <h3 class="left">Bon</h3>
            <h3>ten</h3>
          </div>
        </a>

        <nav class="nav-menu">
          <a
            href="./user_homepage.php"
            class="nav-item active"

            data-translate="home"
            >Home</a
          >
          <a href="./explore.php" class="nav-item" data-translate="explore"
            >Explore</a
          >
          <a href="./history.php" class="nav-item" data-translate="history"

            >History</a
          >
        </nav>

        <div class="lower-menu">
          <a href="./settings.php" class="nav-item" data-translate="settings"
            >Settings</a
          >
          <a href="./index.php" class="logout" data-translate="logout"
            >Logout</a
          >
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

      <div class="hero_wrapper">
        <div class="hero">

          <div class="welcome">
            <h1>Welcome back, <?php echo htmlspecialchars($first_name); ?>!</h1>
            <p>ready to explore what's happening near you?</p>
          </div>
          <a href="./explore.php#search-section" style="text-decoration: none">
            <button class="explore_button">
              Find Events Near Me
              <img
                src="../public/assets/search.png"
                alt="Search"
                class="button_icon"
              />
            </button>
          </a>
        </div>

        <aside class="schedule_widget">
          <div class="widget_header">
            <h3 class="widget_title">Your Plans</h3>
            <select class="month_selector">

              <option>January</option>

              <option>February</option>

              <option>March</option>

              <option>April</option>

              <option>May</option>

              <option>June</option>

              <option>July</option>

              <option>August</option>
              <option>September</option>

              <option>October</option>

              <option>November</option>

              <option>December</option>

            </select>
          </div>

          <div class="Your-plans">
            <?php if ($user_events->num_rows > 0): ?>
              
              <?php while ($event = $user_events->fetch_assoc()): ?>
                <div class="event">
                  <img
                    src="../public/assets/<?php echo htmlspecialchars($event['image_path'] ?: 'bonten.png'); ?>"
                    alt="Event Icon"
                    class="event_icon"
                  />
                  <div class="details">

                    <h4 class="event_title"><?php echo htmlspecialchars($event['name']); ?></h4>
                    
                    <p class="event_time"><?php echo date('F j, Y - g:i A', strtotime($event['event_date'] . ' ' . $event['event_time'])); ?></p>
                  </div>
                  <div class="event_actions">

                    <a href="./event.php?id=<?php echo $event['event_id']; ?>">
                      <button class="edit_event">View details</button>
                    </a>
                  </div>
                </div>
              <?php endwhile; ?>
            <?php else: ?>
              <p>No upcoming events. Start exploring!</p>
            <?php endif; ?>
          </div>

          <a href="./history.php" class="view_more_link">View all plans →</a>
        </aside>
      </div>

      <section class="upcoming_events">

        <h2
          class="section_title"
          style="
            font-style: italic;
            font-family: 'Times New Roman', Times, serif;
            font-weight: 500;
          "
          data-translate="eventsForYou"
        >
          Events For You
        </h2>
        <div class="event-container">
          <div class="carousel_container">
            <button class="carousel_nav prev">‹</button>

            <div class="events_carousel">
              <?php if ($all_events->num_rows > 0): ?>
                <?php while ($event = $all_events->fetch_assoc()): ?>
                  <div class="event_card">
                    <span class="event_badge">Event</span>
                    <div class="bookmark_icon">⬜</div>
                    <img
                      src="../public/assets/<?php echo htmlspecialchars($event['image_path'] ?: 'bonten.png'); ?>"
                      alt="<?php echo htmlspecialchars($event['name']); ?>"
                      class="event_image"
                    />
                    <div class="event_info">

                      <h3 class="event_title"><?php echo htmlspecialchars($event['name']); ?></h3>
                      <p class="event_description">
                        <?php echo htmlspecialchars(substr($event['description'], 0, 120)) . '...'; ?>
                      </p>
                      <a href="./event.php?id=<?php echo $event['event_id']; ?>">
                        <button class="rsvp_button">Read More →</button>

                      </a>
                    </div>
                  </div>
                <?php endwhile; ?>
              <?php endif; ?>
            </div>

            <button class="carousel_nav next">›</button>

          </div>
        </div>
      </section>

      <section class="explore_section">

        <a href="./explore.php" style="text-decoration: none; color: inherit">
          <h2
            class="explore_title"
            style="
              font-style: italic;
              font-family: 'Times New Roman', Times, serif;
              font-weight: 500;
            "
          >
            Explore
          </h2>
          <div class="arrow_down">▼</div>
        </a>
      </section>
    </div>

    <script
      src="https://cdn.userway.org/widget.js"
      data-account="yHxBfPK57z"
      data-position="3"
    ></script>
  </body>
</html>
