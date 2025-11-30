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

$user_id = $_SESSION['user_id'];
$success_message = '';

$error_message = '';


$stmt = $conn->prepare("SELECT email, full_name, phone, profile_picture, username FROM users WHERE user_id = ?");

$stmt->bind_param("i", $user_id);

$stmt->execute();

$result = $stmt->get_result();

$user_data = $result->fetch_assoc();

$stmt->close();


if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_profile'])) {
    $first_name = trim($_POST['first_name'] ?? '');

    $last_name = trim($_POST['last_name'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $phone = trim($_POST['phone'] ?? '');

    $full_name = trim($first_name . ' ' . $last_name);


    $profile_picture = $user_data['profile_picture'] ?? 'user.jpg';

    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
        require_once '../config/security.php';
        $validation = validate_image_upload($_FILES['profile_picture']);

        if ($validation === true) {

            $upload_dir = '../public/assets/';
            $filename = generate_secure_filename($_FILES['profile_picture']['name']);
            $target_path = $upload_dir . $filename;

            if (move_uploaded_file($_FILES['profile_picture']['tmp_name'], $target_path)) {

                if ($profile_picture !== 'user.jpg' && file_exists($upload_dir . $profile_picture)) {

                    @unlink($upload_dir . $profile_picture);


                }
                $profile_picture = $filename;

            } else {

                $error_message = "Failed to upload profile picture.";

            }
        } else {

            $error_message = is_array($validation) ? implode(', ', $validation) : "Invalid image file.";

        }

    }

    $stmt = $conn->prepare("UPDATE users SET full_name = ?, email = ?, phone = ?, profile_picture = ? WHERE user_id = ?");

    $stmt->bind_param("ssssi", $full_name, $email, $phone, $profile_picture, $user_id);

    if ($stmt->execute()) {


        $_SESSION['full_name'] = $full_name;
        $_SESSION['email'] = $email;

        $_SESSION['profile_picture'] = $profile_picture;

        $success_message = "Profile updated successfully!";

        $user_data['full_name'] = $full_name;


        $user_data['email'] = $email;


        $user_data['phone'] = $phone;

        $user_data['profile_picture'] = $profile_picture;

    } else {


        $error_message = "Failed to update profile.";


    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_password'])) {

    $current_password = $_POST['current_password'];


    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    $stmt = $conn->prepare("SELECT password FROM users WHERE user_id = ?");


    $stmt->bind_param("i", $user_id);
    $stmt->execute();

    $result = $stmt->get_result();

    $user = $result->fetch_assoc();
    $stmt->close();

    if (!password_verify($current_password, $user['password'])) {

        $error_message = "Current password is incorrect.";
    } elseif (strlen($new_password) < 8) {

        $error_message = "Password must be at least 8 characters long.";

    } elseif ($new_password !== $confirm_password) {
        $error_message = "New passwords do not match.";

    } else {

        $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("UPDATE users SET password = ? WHERE user_id = ?");
        $stmt->bind_param("si", $hashed_password, $user_id);

        if ($stmt->execute()) {

            $success_message = "Password updated successfully!";
        } else {

            $error_message = "Failed to update password.";
        }
        $stmt->close();
    }


}



if (!isset($user_data)) {
    $stmt = $conn->prepare("SELECT email, full_name, phone, profile_picture, username FROM users WHERE user_id = ?");

    $stmt->bind_param("i", $user_id);

    $stmt->execute();


    $result = $stmt->get_result();
    $user_data = $result->fetch_assoc();


    $stmt->close();

}

$db->close();

$full_name = $user_data['full_name'];

$email = $user_data['email'];
$phone = $user_data['phone'] ?? '';

$profile_picture = $user_data['profile_picture'] ?? 'user.jpg';



$name_parts = explode(' ', $full_name, 2);

$first_name = $name_parts[0];


$last_name = $name_parts[1] ?? '';

?>


<!DOCTYPE html>
<html lang="en">

<head>


    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manager Settings - BONTEN</title>

    <link rel="stylesheet" href="../public/css/style.css" />


    <link rel="stylesheet" href="../public/css/manager_settings.css">


    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script src="../public/js/logout_handler.js" defer></script>
    <script src="../public/js/manager_settings.js" defer></script>

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


                <a href="./manager_history.php" class="nav-item">History</a>
                <a href="./create_event.php" class="nav-item">Create Event</a>

            </nav>

            <div class="lower-menu">
                <a href="./manager_settings.php" class="nav-item active">Settings</a>
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


                    <h4 class="username" id="headerName"><?php echo htmlspecialchars($full_name); ?></h4>

                </div>

            </a>

            <h2 class="page-title">Settings</h2>
        </div>

        <main class="main-content">
            <div class="settings-container">

                <?php if ($success_message): ?>


                <div class="alert alert-success" style="background: #28a745; color: white; padding: 15px; margin-bottom: 20px; border-radius: 8px;">

                    <?php echo htmlspecialchars($success_message); ?>

                </div>

                <?php endif; ?>

                <?php if ($error_message): ?>
                <div class="alert alert-error" style="background: #dc3545; color: white; padding: 15px; margin-bottom: 20px; border-radius: 8px;">


                    <?php echo htmlspecialchars($error_message); ?>
                </div>
                <?php endif; ?>


                <nav class="settings-nav">
                    <button class="settings-nav-item active" data-section="profile">

                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">

                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>

                            <circle cx="12" cy="7" r="4"></circle>

                        </svg>


                        Profile


                    </button>
                    <button class="settings-nav-item" data-section="payment">

                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">


                            <rect x="1" y="4" width="22" height="16" rx="2" ry="2"></rect>

                            <line x1="1" y1="10" x2="23" y2="10"></line>
                        </svg>

                        Payment Details

                    </button>

                    <button class="settings-nav-item" data-section="security">

                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                        </svg>

                        Security

                    </button>

                </nav>


                <div class="settings-content">

                    <section class="settings-section active" id="profile-section">

                        <div class="section-header">

                            <h2>Profile Information</h2>

                            <p>Update your personal details and profile picture</p>


                        </div>

                        <form id="profileForm" class="settings-form">

                            <div class="avatar-upload">


                                <div class="avatar-preview" id="avatarPreview">
                                    <img src="../public/assets/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile" id="profileAvatar">

                                </div>

                                <div class="avatar-actions">
                                    <button type="button" class="btn-secondary" id="changeAvatarBtn">Change Photo</button>

                                    <button type="button" class="btn-text" id="removeAvatarBtn">Remove</button>