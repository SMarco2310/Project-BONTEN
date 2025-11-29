# Quick Start Guide - Database Setup

## Step 1: Create Tables in phpMyAdmin

1. Open phpMyAdmin
2. Select database: `webtech_2025A_marc_sossou`
3. Click "SQL" tab
4. Copy ALL the code from `create_tables.sql`
5. Paste and click "Go"
6. Done! All 8 tables created.

---

## Step 2: Test Connection

Visit: `http://localhost/test_connection.php`

You should see:
- ✅ Connection Successful
- List of 8 tables
- 9 categories loaded

---

## Step 3: Use Database in PHP

### Connect (3 lines)
```php
<?php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();
// Now use $conn for queries!
?>
```

### Example: Get All Events
```php
<?php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Get active events
$sql = "SELECT * FROM events WHERE status = 'active' ORDER BY event_date DESC";
$result = $conn->query($sql);

// Display events
while ($event = $result->fetch_assoc()) {
    echo "<h3>" . htmlspecialchars($event['name']) . "</h3>";
    echo "<img src='" . htmlspecialchars($event['image_path']) . "'>";
    echo "<p>" . htmlspecialchars($event['description']) . "</p>";
}
?>
```

### Example: Insert New Event
```php
<?php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Prepare data
$manager_id = 1;
$name = "My Event";
$description = "Event description";
$event_date = "2024-12-31";
$event_time = "18:00:00";
$location = "Accra";

// Insert event
$sql = "INSERT INTO events (manager_id, name, description, event_date, event_time, location, status)
        VALUES (?, ?, ?, ?, ?, ?, 'active')";

$stmt = $conn->prepare($sql);
$stmt->bind_param('isssss', $manager_id, $name, $description, $event_date, $event_time, $location);

if ($stmt->execute()) {
    echo "Event created! ID: " . $conn->insert_id;
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>
```

### Example: User Login
```php
<?php
session_start();
require_once '../config/Database.php';

$email = $_POST['email'];
$password = $_POST['password'];

$db = new Database();
$conn = $db->connect();

// Get user
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Check password
    if (password_verify($password, $user['password'])) {
        // Login successful
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];

        echo "Welcome " . $user['username'];
    } else {
        echo "Wrong password";
    }
} else {
    echo "User not found";
}

$stmt->close();
?>
```

---

## Ready for PHP Conversion!

Your database is set up and ready. Now you can convert HTML pages to PHP! 🚀
