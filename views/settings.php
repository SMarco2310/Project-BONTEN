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

$success_message = '';

$error_message = '';


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if (isset($_POST['update_profile'])) {


        $fullname = trim($_POST['fullname']);

        $gender = trim($_POST['gender']);

        $country = trim($_POST['country']);
        $timezone = trim($_POST['timezone']);
        $email = trim($_POST['email']);


        $phone = trim($_POST['phone']);

        $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, email = ? WHERE user_id = ?");

        $stmt->bind_param("sssi", $fullname, $phone, $email, $user_id);

        if ($stmt->execute()) {

            $_SESSION['full_name'] = $fullname;
            $_SESSION['email'] = $email;

            $success_message = "Profile updated successfully!";
        } else {

            $error_message = "Failed to update profile.";
        }

        $stmt->close();
    }

    if (isset($_POST['update_password'])) {

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


}


$stmt = $conn->prepare("SELECT email, full_name, phone, profile_picture, username FROM users WHERE user_id = ?");


$stmt->bind_param("i", $user_id);

$stmt->execute();


$result = $stmt->get_result();
$user = $result->fetch_assoc();

$stmt->close();

$db->close();

$full_name = $user['full_name'];

$email = $user['email'];

$phone = $user['phone'] ?? '';


$profile_picture = $user['profile_picture'] ?? 'user.jpg';
$username = $user['username'];

?>


<!DOCTYPE html>
<html lang="en">


<head>
    <meta charset="UTF-8">


    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Settings</title>


    <link rel="stylesheet" href="../public/css/style.css">

    <link rel="stylesheet" href="../public/css/event.css">

    <link rel="stylesheet" href="../public/css/settings.css">

    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">

    <script src="../public/js/language.js"></script>

    <script src="../public/js/logout_handler.js" defer></script>
    <script src="../public/js/profile_loader.js"></script>
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

            <a href="./explore.php" class="nav-item" data-translate="explore">Explore</a>


            <a href="./history.php" class="nav-item" data-translate="history">History</a>


            </nav>

            <div class="lower-menu">
                <a href="./settings.php" class="nav-item active" data-translate="settings">Settings</a>


                <a href="./logout.php" class="logout" data-translate="logout">Logout</a>

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

            <div class="settings-container">
                <h1 class="settings-title" data-translate="settingsTitle">Settings</h1>


                <?php if ($success_message): ?>

                <div class="alert alert-success" style="background: #28a745; color: white; padding: 10px; margin-bottom: 20px; border-radius: 5px;">

                    <?php echo htmlspecialchars($success_message); ?>
                </div>

                <?php endif; ?>

                <?php if ($error_message): ?>

                <div class="alert alert-error" style="background: #dc3545; color: white; padding: 10px; margin-bottom: 20px; border-radius: 5px;">

                    <?php echo htmlspecialchars($error_message); ?>
                </div>

                <?php endif; ?>

                <div class="settings-content">

                    <div class="settings-tabs">

                        <button class="settings-tab active" data-section="profile">Profile</button>

                        <button class="settings-tab" data-section="security">Security</button>

                    </div>


                    <div class="profile-section settings-panel active" id="profile-panel">

                        <form method="POST" action="settings.php">

                            <div class="profile-header">


                                <div class="profile-left">


                                    <div class="profile-avatar">


                                        <img src="../public/assets/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Avatar" id="profile-avatar-img">
                                        <div class="avatar-overlay">


                                            <label for="profile-upload" class="upload-label">


                                                <span>Change Photo</span>


                                            </label>