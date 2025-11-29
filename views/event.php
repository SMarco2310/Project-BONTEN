<?php
session_start();


$is_logged_in = isset($_SESSION['user_id']);
$user_id = $is_logged_in ? $_SESSION['user_id'] : null;
$user_type = $is_logged_in ? $_SESSION['user_type'] : null;
$full_name = $is_logged_in ? $_SESSION['full_name'] : 'Guest';
$profile_picture = $is_logged_in ? ($_SESSION['profile_picture'] ?? 'user.jpg') : 'user.jpg';

require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();


$event_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;


$event = null;
$tickets = [];
$reviews = [];
$category_name = '';

if ($event_id > 0) {
   
  
    $stmt = $conn->prepare("
        SELECT e.*, c.name as category_name, u.full_name as organizer_name
        FROM events e
        LEFT JOIN categories c ON e.category_id = c.category_id
        LEFT JOIN users u ON e.manager_id = u.user_id
        WHERE e.event_id = ? AND e.status = 'active'
    ");
    $stmt->bind_param("i", $event_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $event = $result->fetch_assoc();
    $stmt->close();

    if ($event) {
        $category_name = $event['category_name'] ?? 'Event';

        
        $stmt = $conn->prepare("
            SELECT ticket_id, ticket_name, price, quantity, sold
            FROM tickets
            WHERE event_id = ?
            ORDER BY price ASC
        ");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $tickets[] = $row;
        }
        $stmt->close();

       
        $stmt = $conn->prepare("
            SELECT r.*, u.full_name, u.profile_picture
            FROM reviews r
            JOIN users u ON r.user_id = u.user_id
            WHERE r.event_id = ?
            ORDER BY r.created_at DESC
            LIMIT 10
        ");
        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $reviews[] = $row;
        }
        $stmt->close();
    }
}

$db->close();


if (!$event) {
    header("Location: explore.php");
    exit();
}


$regular_price = 0;
$vip_price = 0;
foreach ($tickets as $ticket) {
    $ticket_name_lower = strtolower($ticket['ticket_name']);
    if (strpos($ticket_name_lower, 'regular') !== false) {
        $regular_price = $ticket['price'];
    } elseif (strpos($ticket_name_lower, 'vip') !== false) {
        $vip_price = $ticket['price'];
    }
}


$event_date_formatted = date('F j, Y', strtotime($event['event_date']));
$event_time_formatted = date('g:i A', strtotime($event['event_time']));


function timeAgo($datetime) {
    $timestamp = strtotime($datetime);
    $difference = time() - $timestamp;

    if ($difference < 60) {
        return 'Just now';
    } elseif ($difference < 3600) {
        $minutes = floor($difference / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 86400) {
        $hours = floor($difference / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 604800) {
        $days = floor($difference / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    } elseif ($difference < 2419200) {
        $weeks = floor($difference / 604800);
        return $weeks . ' week' . ($weeks > 1 ? 's' : '') . ' ago';
    } else {
        $months = floor($difference / 2419200);
        return $months . ' month' . ($months > 1 ? 's' : '') . ' ago';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo htmlspecialchars($event['name']); ?> - BONTEN</title>
    <link rel="stylesheet" href="../public/css/style.css" />
    <link rel="stylesheet" href="../public/css/event.css" />
    <link rel="stylesheet" href="../public/css/explore.css" />
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon" />
    <script src="../public/js/language.js"></script>
    <script src="../public/js/profile_loader.js"></script>
    <script src="../public/js/validation.js"></script>
    <script src="../public/js/event_modals.js" defer></script>
    <script src="https://js.paystack.co/v1/inline.js"></script>
  </head>

  <body>
    <div class="container">
      <aside class="sidebar">
        <a href="<?php echo $is_logged_in ? ($user_type === 'manager' ? './manager_dashboard.php' : './user_homepage.php') : './index.php'; ?>" style="text-decoration: none">
          <div class="logo">
            <h3 class="left">Bon</h3>
            <h3>ten</h3>
          </div>
        </a>

        <nav class="nav-menu">
          <?php if ($is_logged_in && $user_type === 'user'): ?>
          <a href="./user_homepage.php" class="nav-item" data-translate="home">Home</a>
          <a href="./explore.php" class="nav-item" data-translate="explore">Explore</a>
          <a href="./history.php" class="nav-item" data-translate="history">History</a>
          <?php elseif ($is_logged_in && $user_type === 'manager'): ?>
          <a href="./manager_dashboard.php" class="nav-item" data-translate="dashboard">Dashboard</a>
          <a href="./create_event.php" class="nav-item" data-translate="createEvent">Create Event</a>
          <?php endif; ?>
        </nav>

        <div class="lower-menu">
          <?php if ($is_logged_in): ?>
          <a href="<?php echo $user_type === 'manager' ? './manager_settings.php' : './settings.php'; ?>" class="nav-item" data-translate="settings">Settings</a>
          <a href="./index.php" class="logout" data-translate="logout">Logout</a>
          <?php else: ?>
          <a href="./index.php" class="nav-item" data-translate="login">Login</a>
          <?php endif; ?>
        </div>
      </aside>

      <div class="topnav">
        <a
          href="<?php echo $is_logged_in ? ($user_type === 'manager' ? './manager_settings.php' : './settings.php') : './index.php'; ?>"
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

      <!-- * Main page content -->
      <div class="main-body">
        <div class="mid">
          <div class="event-banner">
            <div class="event-image" id="event-img" style="background-image: url('<?php echo htmlspecialchars($event['image_path'] ? $event['image_path'] : '../public/assets/hero.png'); ?>');">
              <div class="event-header">
                <p class="diff-font" id="event-name"><?php echo htmlspecialchars($event['name']); ?></p>
                <button id="event-tag"><?php echo htmlspecialchars($category_name); ?></button>
              </div>
            </div>

            <div class="desc-comments">
              <div class="desc">
                <div class="desc-title">
                  <h2
                    style="
                      font-family: 'Inter', sans-serif;
                      color: #ffffff;
                      margin: 0;
                    "
                    data-translate="description"
                  >
                    Description
                  </h2>
                </div>
                <div id="desc-text">
                  <p id="event-description">
                    <?php echo nl2br(htmlspecialchars($event['description'])); ?>
                  </p>
                  <div style="margin-top: 15px; color: #ccc;">
                    <p><strong>📅 Date:</strong> <?php echo $event_date_formatted; ?></p>
                    <p><strong>🕐 Time:</strong> <?php echo $event_time_formatted; ?></p>
                    <p><strong>📍 Location:</strong> <?php echo htmlspecialchars($event['location']); ?><?php if ($event['city']): ?>, <?php echo htmlspecialchars($event['city']); ?><?php endif; ?></p>
                    <?php if ($event['capacity']): ?>
                    <p><strong>👥 Capacity:</strong> <?php echo number_format($event['capacity']); ?> attendees</p>
                    <?php endif; ?>
                    <p><strong>🎭 Event Type:</strong> <?php echo ucfirst($event['event_type']); ?></p>
                  </div>
                </div>
                <div class="rsvp">
                  <button
                    id="rsvp-btn"
                    style="font-family: 'MartianMono Nerd Font', sans-serif"
                    data-translate="rsvp"
                  >
                    RSVP ->
                  </button>
                </div>
              </div>
              <div class="comments">
                <div class="desc-title">
                  <h2
                    style="
                      font-family: 'Inter', sans-serif;
                      color: #ffffff;
                      margin: 0;
                    "
                    data-translate="comments"
                  >
                    Reviews
                  </h2>
                </div>

                <div class="comments-list" id="comments-container">
                  <?php if (count($reviews) > 0): ?>
                    <?php foreach ($reviews as $review): ?>
                      <div class="comment-item" data-comment-id="<?php echo $review['review_id']; ?>">
                        <div class="comment-header">
                          <div class="comment-user-avatar">
                            <img src="../public/assets/<?php echo htmlspecialchars($review['profile_picture']); ?>" alt="user" />
                          </div>
                          <div class="comment-user-info">
                            <p class="comment-user-name"><?php echo htmlspecialchars($review['full_name']); ?></p>
                            <div class="comment-rating">
                              <?php for ($i = 1; $i <= 5; $i++): ?>
                                <img
                                  src="../public/assets/icons/<?php echo $i <= $review['rating'] ? 'star.svg' : 'star_w.svg'; ?>"
                                  alt="star"
                                />
                              <?php endfor; ?>
                            </div>
                          </div>
                        </div>
                        <div class="comment-text">
                          <p><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>
                        </div>
                        <div class="comment-time"><?php echo timeAgo($review['created_at']); ?></div>
                      </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <div style="text-align: center; padding: 20px; color: #999;">
                      <p>No reviews yet. Be the first to review this event!</p>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div id="rsvp-modal" class="modal">
      <div class="modal-overlay"></div>
      <div class="modal-content rsvp-modal-content">
        <button class="modal-close">&times;</button>
        <h2 class="modal-title" data-translate="rsvp">RSVP</h2>
        <form id="rsvp-form" class="modal-form">
          <div class="form-field">
            <label for="email" data-translate="email">Email</label>
            <input
              type="email"
              id="email"
              placeholder="Enter your email"
              data-translate="enterYourEmail"
              value="<?php echo $is_logged_in ? htmlspecialchars($_SESSION['email']) : ''; ?>"
              <?php echo $is_logged_in ? 'readonly' : ''; ?>
              required
            />
          </div>
          <?php if (!$is_logged_in): ?>
          <div class="form-field">
            <label for="password" data-translate="password">Password</label>
            <input
              type="password"
              id="password"
              placeholder="••••••••"
              required
            />
          </div>
          <?php endif; ?>
          <button
            type="button"
            id="tickets-btn"
            class="modal-button"
            data-translate="tickets"
          >
            Tickets
          </button>
        </form>
      </div>
    </div>

    <div id="tickets-modal" class="modal">
      <div class="modal-overlay"></div>
      <div class="modal-content tickets-modal-content">
        <button class="modal-close">&times;</button>
        <h2 class="modal-title" data-translate="tickets">Tickets</h2>
        <div class="tickets-form">
          <?php
          $has_regular = false;
          $has_vip = false;
          foreach ($tickets as $ticket):
            $ticket_name_lower = strtolower($ticket['ticket_name']);
            $available = $ticket['quantity'] - $ticket['sold'];

            if (strpos($ticket_name_lower, 'regular') !== false):
              $has_regular = true;
          ?>
          <div class="ticket-option">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
              <label for="regular" data-translate="regular">Regular - GHS <?php echo number_format($ticket['price'], 2); ?></label>
              <span style="font-size: 12px; color: #999;"><?php echo $available; ?> left</span>
            </div>
            <div class="quantity-control">
              <button type="button" class="qty-btn minus" data-target="regular">-</button>
              <input
                type="number"
                id="regular"
                class="qty-input"
                value="0"
                min="0"
                max="<?php echo $available; ?>"
                readonly
              />
              <button type="button" class="qty-btn plus" data-target="regular" data-max="<?php echo $available; ?>">+</button>
            </div>
          </div>
          <?php
            elseif (strpos($ticket_name_lower, 'vip') !== false):
              $has_vip = true;
          ?>
          <div class="ticket-option">
            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
              <label for="vip" data-translate="vip">VIP - GHS <?php echo number_format($ticket['price'], 2); ?></label>
              <span style="font-size: 12px; color: #999;"><?php echo $available; ?> left</span>
            </div>
            <div class="quantity-control">
              <button type="button" class="qty-btn minus" data-target="vip">-</button>
              <input
                type="number"
                id="vip"
                class="qty-input"
                value="0"
                min="0"
                max="<?php echo $available; ?>"
                readonly
              />
              <button type="button" class="qty-btn plus" data-target="vip" data-max="<?php echo $available; ?>">+</button>
            </div>
          </div>
          <?php
            endif;
          endforeach;
          ?>

          <div class="price-details"
            style="margin: 20px 0; padding: 15px; background: rgba(255,255,255,0.05); border-radius: 8px;">
            <?php if ($has_regular): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 5px;">
              <span style="color: #ccc;">Regular (<span id="regular-subtotal">0.00</span> GHS)</span>
            </div>
            <?php endif; ?>
            <?php if ($has_vip): ?>
            <div style="display: flex; justify-content: space-between; margin-bottom: 10px;">
              <span style="color: #ccc;">VIP (<span id="vip-subtotal">0.00</span> GHS)</span>
            </div>
            <?php endif; ?>
            <div
              style="display: flex; justify-content: space-between; border-top: 1px solid rgba(255,255,255,0.2); padding-top: 10px;">
              <strong style="color: white;">Total:</strong>
              <strong style="color: rgb(212, 102, 62);">GHS <span id="grand-total">0.00</span></strong>
            </div>
          </div>
          <button
            type="button"
            id="checkout-btn"
            class="modal-button"
            data-translate="proceedToCheckout"
          >
            Proceed to Checkout
          </button>
        </div>
      </div>
    </div>

    <script>
      // Pass PHP variables to JavaScript
      const regularPrice = <?php echo $regular_price; ?>;
      const vipPrice = <?php echo $vip_price; ?>;
      const eventId = <?php echo $event_id; ?>;
      const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
    </script>
    <script src="../public/js/event.js" defer></script>
    <script
      src="https://cdn.userway.org/widget.js"
      data-account="yHxBfPK57z"
      data-position="3"
    ></script>
  </body>
</html>
