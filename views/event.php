<?php

require_once '../config/security.php';
require_once '../config/image_helpers.php';

set_security_headers();

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
$comments = [];


$category_name = '';

if ($event_id > 0) {


    $stmt = $conn->prepare("

        SELECT e.*, c.name as category_name, u.full_name as organizer_name
        FROM events e

        LEFT JOIN categories c ON e.category_id = c.category_id

        LEFT JOIN users u ON e.manager_id = u.user_id
        WHERE e.event_id = ? AND (e.status = 'active' OR e.status = 'completed')
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

        $comments = [];

        $stmt = $conn->prepare("

            SELECT c.*, u.full_name, u.profile_picture


            FROM comments c

            JOIN users u ON c.user_id = u.user_id


            WHERE c.event_id = ?

            ORDER BY c.created_at DESC

            LIMIT 20


        ");

        $stmt->bind_param("i", $event_id);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {


            $comments[] = $row;
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


    <script src="../public/js/logout_handler.js" defer></script>
    <script src="../public/js/validation.js"></script>


    <!-- Load Paystack script without defer/async to ensure it loads before event_modals.js -->

    <script src="https://js.paystack.co/v1/inline.js"></script>

    <script>

      (function() {


        if (typeof PaystackPop !== 'undefined') {
          console.log(' Paystack library already loaded');


          window.paystackReady = true;

          return;

        }

        function waitForPaystack(callback, maxAttempts = 100) {
          let attempts = 0;
          const checkInterval = setInterval(function() {

            attempts++;

            if (typeof PaystackPop !== 'undefined') {
              clearInterval(checkInterval);


              console.log(' Paystack library loaded after ' + (attempts * 100) + 'ms');

              window.paystackReady = true;
              if (callback) callback();
            } else if (attempts >= maxAttempts) {
              clearInterval(checkInterval);

              console.error(' Paystack library failed to load after ' + (maxAttempts * 100) + 'ms');


              console.error('This might be due to:');


              console.error('1. Network connectivity issues');
              console.error('2. Content Security Policy (CSP) blocking the script');


              console.error('3. Ad blockers or browser extensions');


              console.error('Please check the browser console for CSP violations');

              const script = document.createElement('script');

              script.src = 'https://js.paystack.co/v1/inline.js';
              script.async = false;
              script.onload = function() {

                console.log(' Paystack library loaded on retry');
                window.paystackReady = true;

                if (callback) callback();
              };

              script.onerror = function() {

                console.error(' Failed to load Paystack script on retry - check network or CSP settings');


                window.paystackReady = false;


              };

              document.head.appendChild(script);


            }

          }, 100);


        }

        waitForPaystack(function() {

          window.paystackReady = true;

          console.log('Paystack is ready for payments');


        });

      })();

    </script>
    <script src="../public/js/event_modals.js?v=<?php echo time(); ?>"></script>
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

          <a href="./logout.php" class="logout" data-translate="logout">Logout</a>

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
            src="<?php echo htmlspecialchars(get_profile_picture_path($profile_picture)); ?>"
            alt="Profile Picture"
            class="profile_picture"
          />

          <div class="user_info">

            <h4 class="username"><?php echo htmlspecialchars($full_name); ?></h4>

          </div>

        </a>

      </div>

      <div class="main-body">

        <div class="mid">
          <div class="event-container">

            <div class="event-box-left">

              <div class="event-img-wrapper">
                <img src="<?php echo htmlspecialchars(get_event_image_path($event['image_path'], '../public/assets/hero.png')); ?>" alt="<?php echo htmlspecialchars($event['name']); ?>" class="event-main-img" />

                <div class="event-overlay">

                  <h1 class="event-title"><?php echo htmlspecialchars($event['name']); ?></h1>

                  <span class="event-category-badge"><?php echo htmlspecialchars($category_name); ?></span>

                  <?php if ($event['status'] === 'completed' || strtotime($event['event_date'] . ' ' . $event['event_time']) < time()): ?>


                    <span class="event-category-badge" style="background-color: #666; margin-left: 10px;">Past Event</span>

                  <?php endif; ?>


                </div>
              </div>

            </div>

            <div class="event-box-right">

              <div class="description-box">

                <h2 class="section-header">Description</h2>

                <p class="event-desc-text"><?php echo htmlspecialchars($event['description']); ?></p>

                <div class="rsvp-btn-wrapper">


                  <?php if ($event['status'] === 'completed' || strtotime($event['event_date'] . ' ' . $event['event_time']) < time()): ?>

                    <button id="rsvp-btn" class="rsvp-button" disabled style="opacity: 0.5; cursor: not-allowed;">Event Ended</button>

                    <p style="color: #999; font-size: 14px; margin-top: 10px;">This event has already ended</p>

                  <?php else: ?>
                    <button id="rsvp-btn" class="rsvp-button">RSVP</button>

                  <?php endif; ?>
                </div>

              </div>


              <div class="comments-box">
                <h2 class="section-header">Comments</h2>

                <div class="comments-scroll" id="comments-container">
                  <?php if(count($comments) > 0): ?>
                    <?php foreach($comments as $comment): ?>

                    <div class="single-comment">

                      <div class="comment-top">

                        <img src="<?php echo htmlspecialchars(get_profile_picture_path($comment['profile_picture'] ?: 'user.jpg')); ?>" alt="user" class="comment-avatar" />


                        <div class="comment-info">
                          <p class="commenter-name"><?php echo htmlspecialchars($comment['full_name']); ?></p>

                        </div>

                      </div>

                      <p class="comment-content"><?php echo nl2br(htmlspecialchars($comment['comment_text'])); ?></p>

                      <span class="comment-timestamp"><?php echo timeAgo($comment['created_at']); ?></span>
                    </div>
                    <?php endforeach; ?>
                  <?php else: ?>
                    <p style="color: #999; text-align: center;">No comments yet</p>

                  <?php endif; ?>


                </div>

              </div>

              <?php if(count($reviews) > 0): ?>

              <div class="comments-box" style="margin-top: 30px;">

                <h2 class="section-header">Reviews</h2>

                <div class="comments-scroll">

                  <?php foreach($reviews as $review): ?>

                  <div class="single-comment">

                    <div class="comment-top">

                      <img src="<?php echo htmlspecialchars(get_profile_picture_path($review['profile_picture'] ?: 'user.jpg')); ?>" alt="user" class="comment-avatar" />

                      <div class="comment-info">

                        <p class="commenter-name"><?php echo htmlspecialchars($review['full_name']); ?></p>

                        <div class="star-rating">
                          <?php for($i = 1; $i <= 5; $i++): ?>

                          <span class="star <?php echo $i <= $review['rating'] ? 'filled' : 'empty'; ?>"></span>

                          <?php endfor; ?>

                        </div>


                      </div>


                    </div>

                    <p class="comment-content"><?php echo nl2br(htmlspecialchars($review['review_text'])); ?></p>

                    <span class="comment-timestamp"><?php echo timeAgo($review['created_at']); ?></span>
                  </div>
                  <?php endforeach; ?>


                </div>

              </div>
              <?php endif; ?>

            </div>

          </div>


        </div>

      </div>

    </div>

    <div id="rsvp-modal" class="modal">
      <div class="modal-overlay"></div>

      <div class="modal-content rsvp-modal-content">


        <button class="modal-close">&times;</button>

        <h2 class="modal-title">RSVP</h2>

        <form id="rsvp-form" class="modal-form">

          <div class="form-field">

            <label for="rsvp-email">Email</label>

            <input
              type="email"

              id="rsvp-email"

              name="email"


              value="<?php echo $is_logged_in ? htmlspecialchars($_SESSION['email']) : ''; ?>"

              readonly
              class="rsvp-email-input"

            />

          </div>

          <div class="form-field">

            <label for="rsvp-password">Password</label>

            <input


              type="password"

              id="rsvp-password"

              name="password"

              placeholder="••••••••"

              required


              class="rsvp-password-input"

            />


          </div>

          <button

            type="submit"
            id="tickets-btn"

            class="modal-button tickets-button"

            <?php if ($event['status'] === 'completed' || strtotime($event['event_date'] . ' ' . $event['event_time']) < time()): ?>
              disabled style="opacity: 0.5; cursor: not-allowed;"

            <?php endif; ?>

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

        <h2 class="modal-title">Tickets</h2>

        <div class="tickets-form">

        <?php
        $has_regular = false;

        $has_vip = false;


        $regular_ticket_price = 0;

        $vip_ticket_price = 0;

        foreach($tickets as $ticket):

          $ticket_name_lower = strtolower($ticket['ticket_name']);

          $available = $ticket['quantity'] - $ticket['sold'];

          if(strpos($ticket_name_lower, 'regular') !== false):


            $has_regular = true;

            $regular_ticket_price = $ticket['price'];


        ?>

          <div class="ticket-type-row">

            <div class="ticket-label-area">

              <span class="ticket-type-name">Regular</span>

              <span class="ticket-unit-price">GHS <?php echo number_format($ticket['price'], 2); ?></span>
            </div>

            <div class="ticket-quantity-controls">

              <button type="button" class="qty-minus-btn" data-ticket="regular">-</button>

              <input type="number" id="regular" class="qty-display" value="0" min="0" max="<?php echo $available; ?>" readonly />

              <button type="button" class="qty-plus-btn" data-ticket="regular" data-max="<?php echo $available; ?>">+</button>

            </div>

          </div>

        <?php

          elseif(strpos($ticket_name_lower, 'vip') !== false):

            $has_vip = true;
            $vip_ticket_price = $ticket['price'];

        ?>

          <div class="ticket-type-row">

            <div class="ticket-label-area">

              <span class="ticket-type-name">VIP</span>

              <span class="ticket-unit-price">GHS <?php echo number_format($ticket['price'], 2); ?></span>
            </div>

            <div class="ticket-quantity-controls">

              <button type="button" class="qty-minus-btn" data-ticket="vip">-</button>

              <input type="number" id="vip" class="qty-display" value="0" min="0" max="<?php echo $available; ?>" readonly />

              <button type="button" class="qty-plus-btn" data-ticket="vip" data-max="<?php echo $available; ?>">+</button>

            </div>

          </div>

        <?php

          endif;


        endforeach;

        ?>

          <div class="total-section">
            <div class="total-row">
              <span class="total-label">Regular Subtotal:</span>


              <span class="total-amount">GHS <span id="regular-subtotal">0.00</span></span>


            </div>


            <div class="total-row">


              <span class="total-label">VIP Subtotal:</span>

              <span class="total-amount">GHS <span id="vip-subtotal">0.00</span></span>

            </div>

            <div class="total-row">
              <span class="total-label">Total:</span>

              <span class="total-amount">GHS <span id="grand-total">0.00</span></span>
            </div>
          </div>

          <button type="button" id="checkout-btn" class="checkout-button">
            Proceed to Checkout
          </button>

        </div>

      </div>


    </div>

    <script>


      const regularPrice = <?php echo $regular_price; ?>;

      const vipPrice = <?php echo $vip_price; ?>;

      const eventId = <?php echo $event_id; ?>;
      const isLoggedIn = <?php echo $is_logged_in ? 'true' : 'false'; ?>;
      const userEmail = "<?php echo $is_logged_in ? htmlspecialchars($_SESSION['email']) : ''; ?>";

    </script>
    <script src="../public/js/event.js" defer></script>

    <script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z"></script>

  </body>
</html>
