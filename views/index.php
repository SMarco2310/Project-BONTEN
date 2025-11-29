<?php
session_start();

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

        $email = $_POST['email'];
        $password = $_POST['password'];

        $stmt = $conn->prepare("SELECT user_id, email, password, full_name, user_type, profile_picture FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows > 0) {

            $user = $result->fetch_assoc();

            if (password_verify($password, $user['password'])) {
                $_SESSION['user_id'] = $user['user_id'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['full_name'] = $user['full_name'];
                $_SESSION['user_type'] = $user['user_type'];
                $_SESSION['profile_picture'] = $user['profile_picture'];


                if ($user['user_type'] === 'manager') {
                    header("Location: manager_dashboard.php");
                } else {
                    header("Location: user_homepage.php");
                }
                exit();
            } else {
                $error = 'Invalid email or password';
            }
        } else {
            $error = 'Invalid email or password';
        }

        $stmt->close();
    }


    if (isset($_POST['signup'])) {
        $first_name = trim($_POST['first_name']);
        $last_name = trim($_POST['last_name']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        $phone = trim($_POST['phone']);
        $role = $_POST['role'];

        // Validate required fields
        if (empty($first_name) || empty($last_name) || empty($email) || empty($password) || empty($phone) || empty($role)) {
            $error = 'All fields are required';
        }

        else if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = 'Invalid email format';
        } else if (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters long';
        }

        else {
            $password = password_hash($password, PASSWORD_DEFAULT);

            $full_name = $first_name . ' ' . $last_name;
            $user_type = ($role === 'event-organizer') ? 'manager' : 'user';
            $username = $first_name . $last_name;

            $stmt = $conn->prepare("INSERT INTO users (email, password, username, full_name, phone, user_type) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("ssssss", $email, $password, $username, $full_name, $phone, $user_type);

            if ($stmt->execute()) {
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['email'] = $email;

                $_SESSION['full_name'] = $full_name;
                $_SESSION['user_type'] = $user_type;
                $_SESSION['profile_picture'] = 'user.jpg';

                if ($user_type === 'manager') {
                    header("Location: manager_dashboard.php");
                }
                else {
                    header("Location: user_homepage.php");
                }
                exit();
            } else {
                $error = 'Email already exists';
            }

            $stmt->close();
        }
    }

    $db->close();
}
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
    <script src="../public/js/script.js" defer async></script>

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
        <p style="color: red; font-size: small;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form id="login-form" name="login-form" method="POST">
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
          onsubmit="return validatePassword()"
        >
          <label for="first-name-field">
            <p>First Name</p>
            <input
              type="text"
              name="first_name"
              id="first-name-field"
              placeholder="Enter your first name"
            />
          </label>

          <label for="last-name-field">
            <p>Last Name</p>
            <input
              type="text"
              name="last_name"
              id="last-name-field"
              placeholder="Enter your last name"
            />
          </label>

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
