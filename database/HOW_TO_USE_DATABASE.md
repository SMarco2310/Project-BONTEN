# How to Use Database.php - Beginner Guide

## Why Use the Database Class?

The `Database.php` class makes your life easier! Instead of writing the same connection code everywhere, you just use this class.

---

## Basic Usage Examples

### 1. Connect to Database (Simple)

```php
<?php
require_once '../config/Database.php';

// Create database object
$db = new Database();

// Connect
$conn = $db->connect();

// Now you can use $conn for queries!
?>
```

---

### 2. Insert Data (User Registration Example)

```php
<?php
require_once '../config/Database.php';

// Get form data
$email = $_POST['email'];
$password = $_POST['password'];
$username = $_POST['username'];

// IMPORTANT: Hash password with salt (PHP does this automatically!)
$hashed_password = password_hash($password, PASSWORD_DEFAULT);

// Connect to database
$db = new Database();
$conn = $db->connect();

// Prepare SQL query (prevents SQL injection!)
$sql = "INSERT INTO users (email, password, username, user_type) VALUES (?, ?, ?, 'user')";
$stmt = $conn->prepare($sql);

// Bind parameters ('sss' means 3 strings)
$stmt->bind_param('sss', $email, $hashed_password, $username);

// Execute
if ($stmt->execute()) {
    echo "Registration successful!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>
```

---

### 3. Check Login (Password Verification)

```php
<?php
require_once '../config/Database.php';

$email = $_POST['email'];
$password = $_POST['password'];

// Connect
$db = new Database();
$conn = $db->connect();

// Get user from database
$sql = "SELECT user_id, password, username, user_type FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();

$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $user = $result->fetch_assoc();

    // Verify password (compares hashed password)
    if (password_verify($password, $user['password'])) {
        // Login successful!
        session_start();
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['username'] = $user['username'];
        $_SESSION['user_type'] = $user['user_type'];

        echo "Login successful!";
    } else {
        echo "Wrong password!";
    }
} else {
    echo "User not found!";
}

$stmt->close();
?>
```

---

### 4. Get All Events

```php
<?php
require_once '../config/Database.php';

$db = new Database();
$conn = $db->connect();

// Simple query
$sql = "SELECT * FROM events WHERE status = 'active' ORDER BY event_date DESC";
$result = $conn->query($sql);

// Loop through results
while ($event = $result->fetch_assoc()) {
    echo "<h3>" . $event['name'] . "</h3>";
    echo "<p>" . $event['description'] . "</p>";
    echo "<img src='" . $event['image_path'] . "' alt='Event'>";
}
?>
```

---

### 5. Create New Event (Manager)

```php
<?php
session_start();
require_once '../config/Database.php';

// Make sure user is a manager
if ($_SESSION['user_type'] != 'manager') {
    die("Only managers can create events!");
}

$manager_id = $_SESSION['user_id'];
$name = $_POST['event_name'];
$description = $_POST['description'];
$event_date = $_POST['event_date'];
$event_time = $_POST['event_time'];
$location = $_POST['location'];
$city = $_POST['city'];
$image_path = $_POST['image_path']; // We'll handle upload separately

$db = new Database();
$conn = $db->connect();

$sql = "INSERT INTO events (manager_id, name, description, event_date, event_time, location, city, image_path, status)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active')";

$stmt = $conn->prepare($sql);
$stmt->bind_param('isssssss', $manager_id, $name, $description, $event_date, $event_time, $location, $city, $image_path);

if ($stmt->execute()) {
    echo "Event created successfully!";
} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();
?>
```

---

### 6. Get Manager Dashboard Stats

```php
<?php
session_start();
require_once '../config/Database.php';

$manager_id = $_SESSION['user_id'];

$db = new Database();
$conn = $db->connect();

// Total events
$sql = "SELECT COUNT(*) as total FROM events WHERE manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$result = $stmt->get_result();
$total_events = $result->fetch_assoc()['total'];

// Total tickets sold
$sql = "SELECT SUM(t.sold) as total_sold
        FROM tickets t
        JOIN events e ON t.event_id = e.event_id
        WHERE e.manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$result = $stmt->get_result();
$total_sold = $result->fetch_assoc()['total_sold'] ?? 0;

// Total revenue
$sql = "SELECT SUM(t.price * t.sold) as revenue
        FROM tickets t
        JOIN events e ON t.event_id = e.event_id
        WHERE e.manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$result = $stmt->get_result();
$revenue = $result->fetch_assoc()['revenue'] ?? 0;

// Display stats
echo "Total Events: $total_events<br>";
echo "Tickets Sold: $total_sold<br>";
echo "Revenue: GHC $revenue<br>";

$stmt->close();
?>
```

---

## Password Hashing Explained

PHP's `password_hash()` and `password_verify()` automatically handle salting!

### When Registering:
```php
$password = "user123";
$hashed = password_hash($password, PASSWORD_DEFAULT);
// Result: $2y$10$abcdefg... (60 characters, includes salt!)
```

### When Logging In:
```php
$entered_password = "user123";
$stored_hash = "$2y$10$abcdefg..."; // From database

if (password_verify($entered_password, $stored_hash)) {
    echo "Correct password!";
}
```

**Why this is secure:**
- `PASSWORD_DEFAULT` uses **bcrypt** algorithm
- Automatically generates a **random salt**
- Recommended by W3Schools and all security experts
- Hash is different every time (because of random salt)

---

## Important Notes

### Prepared Statements (Prevents SQL Injection)

**BAD (Dangerous!):**
```php
$sql = "SELECT * FROM users WHERE email = '$email'";
// Can be hacked with SQL injection!
```

**GOOD (Safe):**
```php
$sql = "SELECT * FROM users WHERE email = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $email);
$stmt->execute();
// Safe from SQL injection!
```

### Parameter Types for bind_param()

- `'s'` = string
- `'i'` = integer
- `'d'` = double/float
- `'b'` = blob

**Examples:**
```php
$stmt->bind_param('s', $email);           // One string
$stmt->bind_param('ss', $email, $name);   // Two strings
$stmt->bind_param('si', $name, $age);     // String and integer
$stmt->bind_param('ssi', $name, $email, $id); // Two strings, one int
```

---

## That's It!

You don't need to understand all the complex functions in `Database.php`. Just use these simple examples above!
