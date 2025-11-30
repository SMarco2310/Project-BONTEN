
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

$manager_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT full_name, email, profile_picture FROM users WHERE user_id = ?");

$stmt->bind_param("i", $manager_id);

$stmt->execute();
$result = $stmt->get_result();
$manager = $result->fetch_assoc();


$stmt->close();

$full_name = $manager['full_name'] ?? 'Manager';

$profile_picture = $manager['profile_picture'] ?? 'user.jpg';


$categories = [];

$result = $conn->query("SELECT category_id, name FROM categories ORDER BY name ASC");

while ($row = $result->fetch_assoc()) {
    $categories[] = $row;


}


$db->close();


$success_message = $_SESSION['success_message'] ?? '';

$error_message = $_SESSION['error_message'] ?? '';

unset($_SESSION['success_message']);


unset($_SESSION['error_message']);
?>
<!DOCTYPE html>

<html lang="en">
<head>


    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">


    <title>Create Event - Bonten</title>


    <link rel="stylesheet" href="../public/css/style.css" />

    <link rel="stylesheet" href="../public/css/create_event.css">

    <link rel="icon" href="../public/assets/bonten.png" type="image/x-icon">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600&display=swap" rel="stylesheet">
    <script>

        const managerId = <?php echo $manager_id; ?>;

        const managerEmail = "<?php echo htmlspecialchars($manager['email']); ?>";

    </script>

    <script src="../public/js/logout_handler.js" defer></script>
    <script src="../public/js/create_event.js" defer></script>


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

                <a href="./create_event.php" class="nav-item active">Create Event</a>


            </nav>

            <div class="lower-menu">


                <a href="./manager_settings.php" class="nav-item">Settings</a>

                <a href="./logout.php" class="logout">Logout</a>

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

                />

                <div class="user_info">

                    <h4 class="username" id="managerName"><?php echo htmlspecialchars($full_name); ?></h4>

                </div>
            </a>

        </div>

        <main class="main-content">


            <div class="page-header">

                <div class="header-text">

                    <h1>Create <span class="italic">New Event</span></h1>

                    <p>Fill in the details to create your event</p>

                </div>

            </div>

            <?php if ($success_message): ?>
            <div class="alert alert-success"><?php echo htmlspecialchars($success_message); ?></div>

            <?php endif; ?>

            <?php if ($error_message): ?>
            <div class="alert alert-error"><?php echo htmlspecialchars($error_message); ?></div>

            <?php endif; ?>


            <div class="progress-container">

                <div class="progress-steps">
                    <div class="step active" data-step="1">

                        <div class="step-number">1</div>
                        <span class="step-label">Basic Info</span>


                    </div>
                    <div class="step-line"></div>

                    <div class="step" data-step="2">

                        <div class="step-number">2</div>

                        <span class="step-label">Date & Location</span>


                    </div>
                    <div class="step-line"></div>

                    <div class="step" data-step="3">


                        <div class="step-number">3</div>

                        <span class="step-label">Tickets</span>
                    </div>

                    <div class="step-line"></div>

                    <div class="step" data-step="4">

                        <div class="step-number">4</div>
                        <span class="step-label">Review</span>


                    </div>


                </div>

                <div class="progress-bar">


                    <div class="progress-fill" id="progressFill"></div>

                </div>

            </div>


            <form id="createEventForm" class="event-form" method="POST" action="../src/Controllers/handle_create_event.php" enctype="multipart/form-data">


                <div class="form-step active" data-step="1">

                    <div class="step-content">


                        <h2 class="step-title">Basic Information</h2>

                        <p class="step-description">Let's start with the essentials about your event</p>

                        <div class="form-grid">
                            <div class="form-group full-width">

                                <label for="eventName">Event Name <span class="required">*</span></label>
                                <input type="text" id="eventName" name="eventName" placeholder="Enter your event name" required maxlength="100">

                                <span class="char-count"><span id="nameCount">0</span>/100</span>


                            </div>

                            <div class="form-group">


                                <label for="eventCategory">Category <span class="required">*</span></label>

                                <select id="eventCategory" name="eventCategory" required>


                                    <option value="" disabled selected>Select category</option>

                                    <?php foreach ($categories as $category): ?>

                                    <option value="<?php echo $category['category_id']; ?>"><?php echo htmlspecialchars($category['name']); ?></option>
                                    <?php endforeach; ?>

                                </select>


                            </div>

                            <div class="form-group">


                                <label for="eventStatus">Visibility <span class="required">*</span></label>

                                <select id="eventStatus" name="eventStatus" required>
                                    <option value="active">Public - Anyone can find</option>

                                    <option value="draft">Draft - Save for later</option>

                                </select>

                            </div>

                            <div class="form-group full-width">
                                <label for="eventDescription">Description <span class="required">*</span></label>

                                <textarea id="eventDescription" name="eventDescription" rows="5" placeholder="Describe your event, what attendees can expect, highlights, etc." required maxlength="2000"></textarea>
                                <span class="char-count"><span id="descCount">0</span>/2000</span>
                            </div>

                            <div class="form-group full-width">
                                <label>Event Image <span class="required">*</span></label>

                                <div class="image-upload-container">

                                    <div class="image-preview" id="imagePreview">

                                        <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">

                                            <rect x="3" y="3" width="18" height="18" rx="2" ry="2"></rect>

                                            <circle cx="8.5" cy="8.5" r="1.5"></circle>


                                            <polyline points="21 15 16 10 5 21"></polyline>

                                        </svg>
                                        <p>Click to upload or drag and drop</p>

                                        <span>PNG, JPG up to 5MB</span>

                                    </div>