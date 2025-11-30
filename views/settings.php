<?php

require_once '../config/security.php';
require_once '../config/image_helpers.php';
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
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);

        
        $stmt = $conn->prepare("SELECT profile_picture FROM users WHERE user_id = ?");
        
        $stmt->bind_param("i", $user_id);
        
        $stmt->execute();
        
        $result = $stmt->get_result();
        
        $current_user = $result->fetch_assoc();
        
        $profile_picture = $current_user['profile_picture'] ?? 'user.jpg';
        
        $stmt->close();

        $upload_error = false;

        
        if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] === UPLOAD_ERR_OK) {
            require_once '../src/Controllers/CloudinaryService.php';
            require_once '../config/security.php';
            $validation = validate_image_upload($_FILES['profile_picture']);

            if ($validation === true) {
                try {
                    $cloudinary = new CloudinaryService();
                    $profile_picture = $cloudinary->upload($_FILES['profile_picture']['tmp_name']);
                } catch (Exception $e) {
                    $error_message = "Failed to upload profile picture: " . $e->getMessage();
                    $upload_error = true;
                }
            } else {
                $error_message = is_array($validation) ? implode(', ', $validation) : "Invalid image file.";
                $upload_error = true;
            }
        }

        
        if (!$upload_error) {
            $stmt = $conn->prepare("UPDATE users SET full_name = ?, phone = ?, email = ?, profile_picture = ? WHERE user_id = ?");
            $stmt->bind_param("ssssi", $fullname, $phone, $email, $profile_picture, $user_id);

            if ($stmt->execute()) {
                $_SESSION['full_name'] = $fullname;
                $_SESSION['email'] = $email;
                $_SESSION['profile_picture'] = $profile_picture;
                $success_message = "Profile updated successfully!";
            } else {
                $error_message = "Failed to update profile: " . $stmt->error;
            }
            $stmt->close();
        }
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
        <a href="./settings.php" class="user_section" style="cursor: pointer; text-decoration: none">
            <img src="<?php echo htmlspecialchars(get_profile_picture_path($profile_picture)); ?>" alt="Profile Picture" class="profile_picture" />
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
                    <form method="POST" action="settings.php" enctype="multipart/form-data">
                        <div class="profile-header">
                            <div class="profile-left">
                                <div class="profile-avatar">
                                    <img src="<?php echo htmlspecialchars(get_profile_picture_path($profile_picture)); ?>" alt="Profile Avatar" id="profile-avatar-img">
                                    <div class="avatar-overlay">
                                        <label for="profile-upload" class="upload-label">
                                            <span>Change Photo</span>
                                        </label>
                                        <input type="file" id="profile-upload" name="profile_picture" accept="image/*" style="display: none;">
                                    </div>
                                </div>
                            </div>
                            <div class="profile-right">
                                <h2><?php echo htmlspecialchars($full_name); ?></h2>
                                <p class="username-display">@<?php echo htmlspecialchars($username); ?></p>
                            </div>
                        </div>

                        <div class="form-section">
                            <h3>Personal Information</h3>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="fullname">Full Name</label>
                                    <input type="text" id="fullname" name="fullname" value="<?php echo htmlspecialchars($full_name); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="email">Email</label>
                                    <input type="email" id="email" name="email" value="<?php echo htmlspecialchars($email); ?>" required>
                                </div>
                                <div class="form-group">
                                    <label for="phone">Phone Number</label>
                                    <input type="tel" id="phone" name="phone" value="<?php echo htmlspecialchars($phone); ?>">
                                </div>
                            </div>
                            <button type="submit" name="update_profile" class="btn-primary">Save Changes</button>
                        </div>
                    </form>
                </div>

                <div class="security-section settings-panel" id="security-panel">
                    <form method="POST" action="settings.php">
                        <div class="form-section">
                            <h3>Change Password</h3>
                            <div class="form-group">
                                <label for="current_password">Current Password</label>
                                <input type="password" id="current_password" name="current_password" required>
                            </div>
                            <div class="form-group">
                                <label for="new_password">New Password</label>
                                <input type="password" id="new_password" name="new_password" required>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password">Confirm New Password</label>
                                <input type="password" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="update_password" class="btn-primary">Update Password</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const tabs = document.querySelectorAll('.settings-tab');
    const panels = document.querySelectorAll('.settings-panel');

    tabs.forEach(tab => {
        tab.addEventListener('click', function() {
            const section = this.getAttribute('data-section');

            tabs.forEach(t => t.classList.remove('active'));
            panels.forEach(p => p.classList.remove('active'));

            this.classList.add('active');
            document.getElementById(section + '-panel').classList.add('active');
        });
    });

    const profileUpload = document.getElementById('profile-upload');
    const profileAvatar = document.getElementById('profile-avatar-img');

    if (profileUpload) {
        profileUpload.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    profileAvatar.src = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });
    }
});
</script>

</body>
</html>
