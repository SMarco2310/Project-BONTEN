# Image Handling Guide - Beginner Friendly

## How Images Work in This Project

### Simple Approach: Store File Path, Not the Image Itself

**DON'T:** Store images directly in database (too slow!)
**DO:** Store the **path to the image** in database

---

## Folder Structure

```
Project-BONTEN/
├── public/
│   └── assets/
│       ├── events/          ← NEW: Store uploaded event images here
│       │   ├── event1.jpg
│       │   ├── event2.png
│       │   └── ...
│       ├── profiles/        ← NEW: Store profile pictures here
│       │   ├── user1.jpg
│       │   └── user2.png
│       ├── ashchella.jpg    ← Your existing images
│       ├── y2k.JPG
│       └── ...
```

---

## Step 1: Create Upload Folders

Create these folders manually or with this PHP:

```php
<?php
// Run this once to create folders
$folders = [
    '../public/assets/events',
    '../public/assets/profiles'
];

foreach ($folders as $folder) {
    if (!file_exists($folder)) {
        mkdir($folder, 0777, true);
        echo "Created: $folder<br>";
    }
}
?>
```

---

## Step 2: HTML Upload Form

```html
<form action="upload_event.php" method="POST" enctype="multipart/form-data">
    <input type="text" name="event_name" placeholder="Event Name" required>

    <label>Event Image:</label>
    <input type="file" name="event_image" accept="image/*" required>

    <button type="submit">Create Event</button>
</form>
```

**Important:** `enctype="multipart/form-data"` is required for file uploads!

---

## Step 3: PHP Upload Handler

```php
<?php
session_start();
require_once '../config/Database.php';

// Check if manager is logged in
if ($_SESSION['user_type'] != 'manager') {
    die("Only managers can create events!");
}

$manager_id = $_SESSION['user_id'];
$event_name = $_POST['event_name'];

// Handle image upload
$image_path = '';

if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {

    // Get file info
    $file_name = $_FILES['event_image']['name'];
    $file_tmp = $_FILES['event_image']['tmp_name'];
    $file_size = $_FILES['event_image']['size'];
    $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

    // Allowed extensions
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

    // Validate file
    if (in_array($file_ext, $allowed)) {

        // Max 5MB
        if ($file_size <= 5000000) {

            // Create unique filename
            $new_filename = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;

            // Upload path
            $upload_path = '../public/assets/events/' . $new_filename;

            // Move uploaded file
            if (move_uploaded_file($file_tmp, $upload_path)) {

                // Save path for database (relative to public folder)
                $image_path = '/assets/events/' . $new_filename;

                echo "Image uploaded successfully!<br>";
            } else {
                die("Failed to upload image!");
            }
        } else {
            die("Image too large! Max 5MB.");
        }
    } else {
        die("Invalid file type! Allowed: jpg, jpeg, png, gif, webp");
    }
} else {
    die("No image uploaded!");
}

// Save to database
$db = new Database();
$conn = $db->connect();

$sql = "INSERT INTO events (manager_id, name, image_path, status) VALUES (?, ?, ?, 'active')";
$stmt = $conn->prepare($sql);
$stmt->bind_param('iss', $manager_id, $event_name, $image_path);

if ($stmt->execute()) {
    echo "Event created! Image path: $image_path";
} else {
    echo "Database error: " . $stmt->error;
}

$stmt->close();
?>
```

---

## Step 4: Display Images

### In PHP
```php
<?php
$sql = "SELECT * FROM events";
$result = $conn->query($sql);

while ($event = $result->fetch_assoc()) {
    echo '<div class="event-card">';
    echo '  <img src="' . htmlspecialchars($event['image_path']) . '" alt="Event">';
    echo '  <h3>' . htmlspecialchars($event['name']) . '</h3>';
    echo '</div>';
}
?>
```

### In HTML (if you already have the path)
```html
<img src="/assets/events/event_123456.jpg" alt="Event Image">
```

---

## Complete Example: Create Event with Image

### Form (create_event.html or PHP)
```html
<form action="create_event.php" method="POST" enctype="multipart/form-data">
    <!-- Basic Info -->
    <input type="text" name="event_name" placeholder="Event Name" required>
    <textarea name="description" placeholder="Description" required></textarea>

    <!-- Date & Time -->
    <input type="date" name="event_date" required>
    <input type="time" name="event_time" required>

    <!-- Location -->
    <input type="text" name="location" placeholder="Venue">
    <input type="text" name="city" placeholder="City">

    <!-- Image Upload -->
    <label>Event Image:</label>
    <input type="file" name="event_image" accept="image/*" required>

    <!-- Category -->
    <select name="category_id">
        <option value="1">Concert</option>
        <option value="2">Festival</option>
        <option value="3">Conference</option>
    </select>

    <button type="submit">Create Event</button>
</form>
```

### Handler (create_event.php)
```php
<?php
session_start();
require_once '../config/Database.php';

// Check manager login
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] != 'manager') {
    header('Location: login.php');
    exit;
}

$manager_id = $_SESSION['user_id'];

// Get form data
$event_name = $_POST['event_name'];
$description = $_POST['description'];
$event_date = $_POST['event_date'];
$event_time = $_POST['event_time'];
$location = $_POST['location'];
$city = $_POST['city'];
$category_id = $_POST['category_id'];

// Handle image upload
$image_path = '';

if (isset($_FILES['event_image']) && $_FILES['event_image']['error'] == 0) {
    $file_tmp = $_FILES['event_image']['tmp_name'];
    $file_ext = strtolower(pathinfo($_FILES['event_image']['name'], PATHINFO_EXTENSION));

    // Validate
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
    if (!in_array($file_ext, $allowed)) {
        die("Invalid file type!");
    }

    if ($_FILES['event_image']['size'] > 5000000) {
        die("File too large! Max 5MB");
    }

    // Generate unique filename
    $new_filename = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
    $upload_path = '../public/assets/events/' . $new_filename;

    // Upload
    if (move_uploaded_file($file_tmp, $upload_path)) {
        $image_path = '/assets/events/' . $new_filename;
    } else {
        die("Upload failed!");
    }
}

// Insert into database
$db = new Database();
$conn = $db->connect();

$sql = "INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, image_path, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'active')";

$stmt = $conn->prepare($sql);
$stmt->bind_param('iisssssss', $manager_id, $category_id, $event_name, $description, $event_date, $event_time, $location, $city, $image_path);

if ($stmt->execute()) {
    echo "Event created successfully!";
    // Redirect to dashboard
    header('Location: manager_dashboard.php');
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>
```

---

## Important Security Tips

### 1. Always Validate File Type
```php
$allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
if (!in_array($file_ext, $allowed)) {
    die("Invalid file!");
}
```

### 2. Check File Size
```php
if ($_FILES['event_image']['size'] > 5000000) { // 5MB
    die("File too large!");
}
```

### 3. Use Unique Filenames
```php
// DON'T use original filename (can be hacked)
$new_filename = 'event_' . time() . '_' . rand(1000, 9999) . '.' . $file_ext;
```

### 4. Escape Output When Displaying
```php
echo '<img src="' . htmlspecialchars($image_path) . '">';
```

---

## Quick Summary

1. **Create folders:** `/public/assets/events/` and `/public/assets/profiles/`
2. **Upload images there** using `move_uploaded_file()`
3. **Store path in database:** Like `/assets/events/event_123.jpg`
4. **Display with:** `<img src="/assets/events/event_123.jpg">`

That's it! Simple and works perfectly!
