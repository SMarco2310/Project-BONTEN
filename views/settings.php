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
                    <!-- Settings Navigation -->
                    <div class="settings-tabs">
                        <button class="settings-tab active" data-section="profile">Profile</button>
                        <button class="settings-tab" data-section="security">Security</button>
                    </div>

                    <!-- Profile Section -->
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
                                            <input type="file" id="profile-upload" accept="image/*" style="display: none;">
                                        </div>
                                    </div>
                                    <div class="profile-details">
                                        <h2 class="profile-name"><?php echo htmlspecialchars($full_name); ?></h2>
                                        <p class="profile-email"><?php echo htmlspecialchars($email); ?></p>
                                    </div>
                                </div>
                                <button type="button" class="edit-button" id="main-edit-btn" data-translate="edit">Edit</button>
                            </div>

                            <!-- Left Column - Personal Info -->
                            <div class="settings-grid">
                                <div class="settings-left">
                                    <div class="form-group">
                                        <label for="fullname" data-translate="fullName">Full Name</label>
                                        <input type="text" name="fullname" id="fullname" value="<?php echo htmlspecialchars($full_name); ?>" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="gender" data-translate="gender">Gender</label>
                                        <input type="text" name="gender" id="gender" placeholder="Male" readonly>
                                    </div>

                                    <div class="form-group">
                                        <label for="country" data-translate="country">Country</label>
                                        <select name="country" id="country" disabled>
                                            <option value="GH" selected>Ghana</option>
                                            <option value="US">United States</option>
                                            <option value="GB">United Kingdom</option>
                                            <option value="CA">Canada</option>
                                            <option value="NG">Nigeria</option>
                                            <option value="KE">Kenya</option>
                                            <option value="ZA">South Africa</option>
                                            <option value="FR">France</option>
                                            <option value="DE">Germany</option>
                                            <option value="ES">Spain</option>
                                        </select>
                                    </div>

                                    <div class="form-group">
                                        <label for="timezone" data-translate="timeZone">Time Zone</label>
                                        <select name="timezone" id="timezone" disabled>
                                            <option value="" disabled selected>GMT</option>
                                            <option value="GMT">GMT</option>
                                            <option value="EST">EST</option>
                                            <option value="PST">PST</option>
                                            <option value="CST">CST</option>

                                        </select>
                                    </div>
                                </div>

                                <!-- Right Column - Contact Info -->
                                <div class="settings-right">

                                    <div class="contact-section">

                                        <h3 class="contact-title" data-translate="myEmailAddress">My email Address</h3>
                                        <div class="contact-item">
                                            <div class="contact-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M4 4H20C21.1 4 22 4.9 22 6V18C22 19.1 21.1 20 20 20H4C2.9 20 2 19.1 2 18V6C2 4.9 2.9 4 4 4Z" stroke="#C05F47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                    <path d="M22 6L12 13L2 6" stroke="#C05F47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>

                                                </svg>
                                            </div>
                                            <div class="contact-details">
                                                <input type="email" name="email" class="contact-value-input" id="email-input" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                                <p class="contact-time">1 month ago</p>
                                            </div>
                                        </div>
                                        <button type="button" class="add-contact-button" data-translate="addEmailAddress">+Add Email Address</button>
                                    </div>

                                    <div class="contact-section">

                                        <h3 class="contact-title" data-translate="phoneNumber">Phone Number</h3>
                                        <div class="contact-item">
                                            <div class="contact-icon">
                                                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M22 16.92V19.92C22 20.92 21.1 21.92 20 21.92C10.6 21.42 2.03999 12.86 1.53999 3.45996C1.48999 2.35996 2.48999 1.45996 3.48999 1.45996H6.48999C7.48999 1.45996 8.38999 2.25996 8.48999 3.25996C8.67999 5.25996 9.07999 7.21996 9.68999 9.08996C9.98999 9.98996 9.68999 10.99 8.98999 11.59L7.18999 13.09C8.87999 16.57 11.42 19.12 14.9 20.81L16.4 19.01C17 18.31 18.01 18.01 18.91 18.31C20.78 18.92 22.74 19.32 24.74 19.51C25.73 19.62 26.53 20.52 26.53 21.52Z" stroke="#C05F47" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/>
                                                </svg>
                                            </div>
                                            <div class="contact-details">
                                                <input type="tel" name="phone" class="contact-value-input" id="phone-input" value="<?php echo htmlspecialchars($phone); ?>" readonly>
                                                <p class="contact-time">1 month ago</p>
                                            </div>
                                        </div>
                                        <button type="button" class="add-contact-button" data-translate="addPhoneNumber">+Add Phone Number</button>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="update_profile" value="1">
                            <button type="submit" id="save-profile-btn" style="display: none;">Save Profile</button>
                        </form>
                    </div>

                    <!-- Security Section -->
                    <div class="settings-panel" id="security-panel">
                        <h2 class="panel-title">Security Settings</h2>
                        <p class="panel-subtitle">Manage your account security</p>

                        <form method="POST" action="settings.php">

                            <div class="security-item">
                                <h4>Change Password</h4>
                                <div class="form-group">
                                    <label for="currentPassword">Current Password</label>
                                    <input type="password" name="current_password" id="currentPassword" placeholder="Enter current password">
                                </div>
                                <div class="form-group">
                                    <label for="newPassword">New Password</label>

                                    <input type="password" name="new_password" id="newPassword" placeholder="Enter new password">
                                </div>
                                <div class="form-group">
                                    <label for="confirmPassword">Confirm New Password</label>
                                    <input type="password" name="confirm_password" id="confirmPassword" placeholder="Confirm new password">
                                </div>
                                <input type="hidden" name="update_password" value="1">
                                <button type="submit" class="secondary-btn" id="updatePasswordBtn">Update Password</button>

                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>

    </div>

    <script>
        // Main edit functionality for all fields
        const mainEditBtn = document.getElementById('main-edit-btn');
        const saveProfileBtn = document.getElementById('save-profile-btn');
        let isEditing = false;

        // Get all form fields
        const fullnameInput = document.getElementById('fullname');
        const genderInput = document.getElementById('gender');
        const countrySelect = document.getElementById('country');
        const timezoneSelect = document.getElementById('timezone');
        const emailInput = document.getElementById('email-input');
        const phoneInput = document.getElementById('phone-input');

        mainEditBtn.addEventListener('click', () => {
            if (!isEditing) {
                // Enable editing mode
                isEditing = true;
                mainEditBtn.textContent = 'Save';
                mainEditBtn.style.backgroundColor = '#28a745';

                // Enable all inputs
                fullnameInput.removeAttribute('readonly');
                genderInput.removeAttribute('readonly');
                countrySelect.removeAttribute('disabled');
                timezoneSelect.removeAttribute('disabled');
                emailInput.removeAttribute('readonly');
                phoneInput.removeAttribute('readonly');

                // Add visual feedback
                fullnameInput.style.borderColor = 'var(--secondary-color)';
                genderInput.style.borderColor = 'var(--secondary-color)';
                countrySelect.style.borderColor = 'var(--secondary-color)';
                timezoneSelect.style.borderColor = 'var(--secondary-color)';
                emailInput.style.borderColor = 'var(--secondary-color)';

                phoneInput.style.borderColor = 'var(--secondary-color)';
            } else {
                // Trigger form submission
                saveProfileBtn.click();
            }

        });

        // Tab switching functionality
        const settingsTabs = document.querySelectorAll('.settings-tab');

        const settingsPanels = document.querySelectorAll('.settings-panel');

        settingsTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const section = tab.dataset.section;

                // Update tabs
                settingsTabs.forEach(t => t.classList.remove('active'));
                tab.classList.add('active');

                // Update panels
                settingsPanels.forEach(panel => panel.classList.remove('active'));
                const targetPanel = document.getElementById(`${section}-panel`);
                if (targetPanel) targetPanel.classList.add('active');
            });
        });

        // Profile picture upload functionality
        const profileUpload = document.getElementById('profile-upload');
        const profileAvatarImg = document.getElementById('profile-avatar-img');

        profileUpload?.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (file && file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (event) => {
                    profileAvatarImg.src = event.target.result;

                    const topnavProfilePics = document.querySelectorAll('.topnav .profile_picture');

                    topnavProfilePics.forEach(img => {
                        img.src = event.target.result;
                    });
                    localStorage.setItem('userProfilePicture', event.target.result);
                };
                reader.readAsDataURL(file);
            }

        });

        window.addEventListener('load', () => {
            const savedProfilePic = localStorage.getItem('userProfilePicture');
            if (savedProfilePic) {
                profileAvatarImg.src = savedProfilePic;
                const topnavProfilePics = document.querySelectorAll('.topnav .profile_picture');
                topnavProfilePics.forEach(img => {

                    img.src = savedProfilePic;
                });
            }
        });
    </script>

<script src="https://cdn.userway.org/widget.js" data-account="yHxBfPK57z" data-position="3"></script>
</body>
</html>
