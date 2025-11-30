
<?php

require_once '../config/security.php';

set_security_headers();

if (isset($_SESSION['user_id'])) {
    if ($_SESSION['user_type'] === 'manager') {
        header("Location: manager_dashboard.php");
    } else {
        header("Location: user_homepage.php");
    }
    exit();
}

require_once '../config/Database.php';

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $db = new Database();
    $conn = $db->connect();

    if (isset($_POST['login'])) {

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $error = 'Security check failed. Please try again.';
            log_security_event("CSRF token validation failed on login", 'WARNING');
        } else {

            $email = trim($_POST['email']);
            $password = $_POST['password'];

            if (!check_rate_limit('login_' . $email, 5, 900)) {
                $error = 'Too many login attempts. Please try again in 15 minutes.';
                log_security_event("Rate limit exceeded for email: $email", 'WARNING');
            } else {

                record_attempt('login_' . $email);

                $stmt = $conn->prepare("SELECT user_id, email, password, full_name, user_type, profile_picture FROM users WHERE email = ?");
                $stmt->bind_param("s", $email);
                $stmt->execute();
                $result = $stmt->get_result();

                if ($result->num_rows > 0) {

                    $user = $result->fetch_assoc();

                    if (password_verify($password, $user['password'])) {
                        unset($_SESSION['rate_limit']['login_' . $email]);

                        $_SESSION['user_id'] = $user['user_id'];
                        $_SESSION['email'] = $user['email'];
                        $_SESSION['full_name'] = $user['full_name'];
                        $_SESSION['user_type'] = $user['user_type'];
                        $_SESSION['profile_picture'] = $user['profile_picture'];

                        log_security_event("Successful login for: $email", 'INFO');

                        if ($user['user_type'] === 'manager') {
                            header("Location: manager_dashboard.php");
                        } else {
                            header("Location: user_homepage.php");
                        }
                        exit();
                    } else {
                        $error = 'Invalid email or password';
                        log_security_event("Failed login attempt for: $email", 'WARNING');
                    }
                } else {
                    $error = 'Invalid email or password';
                    log_security_event("Login attempt for non-existent email: $email", 'WARNING');
                }

                $stmt->close();
            }
        }
    }


    if (isset($_POST['signup'])) {

        if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
            $error = 'Security check failed. Please try again.';
            log_security_event("CSRF token validation failed on signup", 'WARNING');
        } else {

            $first_name = trim($_POST['first_name']);
            $last_name = trim($_POST['last_name']);
            $email = trim($_POST['email']);
            $password = $_POST['password'];
            $phone = trim($_POST['phone']);
            $role = $_POST['role'];

            
            error_log("Signup attempt - First: $first_name, Last: $last_name, Email: $email, Phone: $phone, Role: $role");

            if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone) || empty($role)) {
                $error = 'All fields are required';
                error_log("Signup failed: Missing fields");
            }

            else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $error = 'Invalid email format';
            } else {
                // Use validate_password function from security.php for full validation
                $password_validation = validate_password($password);
                if ($password_validation !== true) {
                    // $password_validation contains an array of error messages
                    $error = implode(', ', $password_validation);
                }
            }
            
            // Proceed only if no errors so far
            if (empty($error)) {
                $password = password_hash($password, PASSWORD_DEFAULT);

                $full_name = "$first_name $last_name";
                $user_type = ($role === 'event-organizer') ? 'manager' : 'user';
                $username = $first_name . $last_name;

                $stmt = $conn->prepare("INSERT INTO users (email, password, username, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->bind_param("ssssss", $email, $password, $username, $full_name, $phone, $user_type);

                if ($stmt->execute()) {
                    // Get the newly inserted user ID
                    $new_user_id = $conn->insert_id;
                    
                    // Only auto-add RSVPs for regular users, not managers
                    if ($user_type === 'user') {
                     
                      
                        $past_events_stmt = $conn->prepare("
                            SELECT event_id 
                            FROM events 
                            WHERE (status = 'completed' OR (event_date < CURDATE() AND status = 'active'))
                            AND status != 'cancelled'
                            ORDER BY event_date DESC
                            LIMIT 3
                        ");
                        $past_events_stmt->execute();
                        $past_events_result = $past_events_stmt->get_result();
                        
                        
                        $past_count = 0;
                        while ($past_event = $past_events_result->fetch_assoc()) {
                            if ($past_count >= 3) break;
                            
                            
                            $check_stmt = $conn->prepare("SELECT rsvp_id FROM rsvps WHERE event_id = ? AND user_id = ?");
                            $check_stmt->bind_param("ii", $past_event['event_id'], $new_user_id);
                            $check_stmt->execute();
                            if ($check_stmt->get_result()->num_rows === 0) {
                                $rsvp_stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES (?, ?, 1, DATE_SUB(NOW(), INTERVAL ? DAY))");
                                
                                
                                $days_ago = rand(30, 60);
                                
                                $rsvp_stmt->bind_param("iii", $past_event['event_id'], $new_user_id, $days_ago);
                                
                                $rsvp_stmt->execute();
                                
                                
                                $rsvp_stmt->close();
                                
                                $past_count++;
                            }
                            $check_stmt->close();
                        }
                        $past_events_stmt->close();
                        
                        // Get upcoming events (for homepage) - get a mix of popular events
                        // This will include events from populate_events.sql like "Afro Nation", "Rapperholic", etc.
                        $upcoming_events_stmt = $conn->prepare("
                            SELECT event_id 
                            FROM events 
                            WHERE status = 'active' 
                            AND event_date >= CURDATE()
                            ORDER BY event_date ASC, capacity DESC
                            LIMIT 3
                        ");
                        $upcoming_events_stmt->execute();
                        $upcoming_events_result = $upcoming_events_stmt->get_result();
                        
                        // Add RSVPs for upcoming events (not yet attended)
                        $upcoming_count = 0;
                        while ($upcoming_event = $upcoming_events_result->fetch_assoc()) {
                            if ($upcoming_count >= 3) break;
                            
                            
                            $check_stmt = $conn->prepare("SELECT rsvp_id FROM rsvps WHERE event_id = ? AND user_id = ?");
                            
                            $check_stmt->bind_param("ii", $upcoming_event['event_id'], $new_user_id);
                            
                            $check_stmt->execute();
                            if ($check_stmt->get_result()->num_rows === 0) {
                                $rsvp_stmt = $conn->prepare("INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES (?, ?, 0, DATE_SUB(NOW(), INTERVAL ? DAY))");
                                
                                $days_ago = rand(1, 7);
                               
                               
                                $rsvp_stmt->bind_param("iii", $upcoming_event['event_id'], $new_user_id, $days_ago);
                                $rsvp_stmt->execute();
                               
                                $rsvp_stmt->close();
                               
                                $upcoming_count++;
                            }
                            $check_stmt->close();
                        }
                        $upcoming_events_stmt->close();
                    }
                    
                    // Set session variables
                    $_SESSION['user_id'] = $new_user_id;
                    $_SESSION['email'] = $email;
                    $_SESSION['full_name'] = $full_name;
                    $_SESSION['user_type'] = $user_type;
                    $_SESSION['profile_picture'] = 'user.jpg';
                    
                    
                    session_write_close();

                    log_security_event("New user registered: $email", 'INFO');

                    
                    if ($user_type === 'manager') {
                        header("Location: manager_dashboard.php");
                    } else {
                        header("Location: user_homepage.php");
                    }
                    exit();
                } else {
                    $error = 'Email already exists';
                    log_security_event("Duplicate email signup attempt: $email", 'WARNING');
                }

                $stmt->close();
            }
        }
    }

    $db->close();
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Cabin:ital,wght@0,400..700;1,400..700&display=swap"
      rel="stylesheet"
    />

    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link
      href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
      rel="stylesheet"
    />

    <link rel="stylesheet" href="../public/css/login_style.css" />
    <title class="tab-name">Authentication</title>
    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon" />
  </head>
  <body>
    <script src="../public/js/script.js"></script>
    <?php if (isset($_POST['signup']) && !empty($error)): ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showSignUpForm();
        });
    </script>
    <?php else: ?>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            showLoginForm();
        });
    </script>
    <?php endif; ?>

    <div class="login-container" style="color: white">
      <div class="left-side">
        <h1 style="margin: 0">
          THE
          <span
            style="
              font-family: 'Times New Roman', Times, serif;
              font-style: italic;
              font-weight: lighter;
            "
            >GENZ</span
          >
          EVENT DISCOVERY PLATFORM
        </h1>
        <p style="margin: 0; font-size: x-small">
          Discover events, go outside!
        </p>
      </div>

      <div class="right-side">

        <h3 style="font-size: x-large">
          <span class="text-orange" style="color: #c05f47">Bon</span
          ><span>ten</span>
        </h3>
        <p style="font-size: small; padding-bottom: 25px">
          welcome back! Please enter your details.
        </p>

        <?php if ($error): ?>
        <p style="color: red; font-size: small;"><?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?></p>
        <?php endif; ?>

        <form id="login-form" name="login-form" method="POST">
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">
          <label for="email-field">
            <p>Email</p>
            <input
              type="email"
              name="email"
              id="email-field"
              placeholder="Enter your email"
              aria-required="true"
              required
            />
          </label>

          <label for="password-field">
            <p>Password</p>
            <div class="password-wrapper">
              <input
                type="password"
                name="password"
                id="password-field"
                placeholder="********"
                required
              />
              <button type="button" class="pwd-eye">
                <svg
                  id="eye-icon"
                  xmlns="http://www.w3.org/2000/svg"
                  height="24px"
                  viewBox="0 -960 960 960"
                  width="24px"
                  fill="#e3e3e3"
                >
                  <path
                    d="M480-320q75 0 127.5-52.5T660-500q0-75-52.5-127.5T480-680q-75 0-127.5 52.5T300-500q0 75 52.5 127.5T480-320Zm0-72q-45 0-76.5-31.5T372-500q0-45 31.5-76.5T480-608q45 0 76.5 31.5T588-500q0 45-31.5 76.5T480-392Zm0 192q-146 0-266-81.5T40-500q54-137 174-218.5T480-800q146 0 266 81.5T920-500q-54 137-174 218.5T480-200Zm0-300Zm0 220q113 0 207.5-59.5T832-500q-50-101-144.5-160.5T480-720q-113 0-207.5 59.5T128-500q50 101 144.5 160.5T480-280Z"
                  />
                </svg>
              </button>
            </div>
          </label>

          <p id="password-cue"></p>

          <label for="login-submit-btn">
            <input
              style="
                background: linear-gradient(to bottom, #cc653c, #d87652);
                font-weight: 550;
                color: white;
                border: none;
                height: 40px;
                font-size: x-small;
              "
              type="submit"
              name="login"
              value="Login"
              id="login-submit-btn"
            />
          </label>

          <p style="font-size: x-small">
            Don't have an account?<a
              style="
                color: rgb(210, 83, 37);
                text-decoration: none;
                padding-left: 5px;
              "
              href="#"
              id="switch-to-signup"
            >
              Sign up for free!</a
            >
          </p>
        </form>

        <form
          id="signup-form"
          name="signup-form"
          method="POST"
          style="display: none;"
        >
          <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8'); ?>">

          <label for="first-name-field">
            <p>First Name</p>
            <input
              type="text"
              name="first_name"
              id="first-name-field"
              placeholder="Enter your first name"
              required
            />
          </label>

          <label for="last-name-field">
            <p>Last Name</p>
            <input
              type="text"
              name="last_name"
              id="last-name-field"
              placeholder="Enter your last name"
              required
            />
          </label>

          <label for="signup-email-field">
            <p>Email</p>
            <input
              type="email"
              name="email"
              id="signup-email-field"
              placeholder="Enter your email"
              aria-required="true"
              required
              style="
                border: solid 1px #f9f9f8;

                background-color: #020b13;
                color: white;
              "
            />
          </label>

          <label for="signup-password-field">
            <p>Password</p>
            <div class="password-wrapper">
              <input
                type="text"
                name="password"
                id="signup-password-field"
                placeholder="********"
                required
                style="
                border: solid 1px #f9f9f8;

                background-color: #020b13;
                color: white;
              "
              />
            </div>
          </label>

          <p id="password-cue"></p>

          <label for="phoneNumber-field">
            <p>Phone Number</p>
            <input
              type="tel"
              name="phone"
              id="phoneNumber-field"
              placeholder="Enter your phoneNumber"
              aria-required="true"
              required
            />
          </label>

          <label for="role-field">
            <p>Role</p>
            <select name="role" id="role-field" required>
              <option value="" disabled selected>Select your role</option>
              <option value="user">User</option>
              <option value="event-organizer">Event Organizer</option>
            </select>
          </label>

          <label for="signup-submit-btn">
            <input
              style="
                background: linear-gradient(to bottom, #cc653c, #d87652);
                font-weight: 550;
                color: white;
                border: none;
                height: 40px;
                font-size: x-small;
              "
              type="submit"
              name="signup"
              value="Sign up"
              id="signup-submit-btn"
            />
          </label>

          <p style="font-size: x-small">
            Already have an account?
            <a
              style="
                color: rgb(210, 83, 37);
                text-decoration: none;
                padding-left: 5px;
              "
              href="#"
              id="switch-to-login"
              >Login</a
            >
          </p>
        </form>
      </div>
    </div>
    <script
      src="https://cdn.userway.org/widget.js"
      data-account="yHxBfPK57z"
      data-position="3"
    ></script>
  </body>
</html>
