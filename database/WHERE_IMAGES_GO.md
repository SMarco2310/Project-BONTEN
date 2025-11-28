# WHERE IMAGES ARE STORED

## Physical Storage Location

Images are stored in your **file system** (on your computer/server), NOT in the database.

```
Your Project Folder Structure:
Project-BONTEN/
├── public/
│   └── assets/
│       ├── events/              ← EVENT IMAGES GO HERE
│       │   ├── event_1701234567_1234.jpg
│       │   ├── event_1701234890_5678.png
│       │   └── event_1701235123_9012.webp
│       │
│       ├── profiles/            ← PROFILE PICTURES GO HERE
│       │   ├── user_1701234567_1234.jpg
│       │   └── user_1701234890_5678.png
│       │
│       └── ashchella.jpg        ← Your existing static images
```

## What Goes in the Database

The database stores only the **PATH** to the image:

```
Database 'events' table:
┌──────────┬──────────────┬─────────────────────────────────────────┐
│ event_id │ name         │ image_path                              │
├──────────┼──────────────┼─────────────────────────────────────────┤
│ 1        │ Ashchella    │ /assets/events/event_1701234567_1234.jpg│
│ 2        │ Y2K Party    │ /assets/events/event_1701234890_5678.png│
└──────────┴──────────────┴─────────────────────────────────────────┘
```

## Complete Flow

### 1. User Uploads Image
```html
<form action="create_event.php" method="POST" enctype="multipart/form-data">
    <input type="file" name="event_image">
    <button>Upload</button>
</form>
```

### 2. PHP Saves File to Disk
```php
// Uploaded file goes to: /public/assets/events/
$upload_path = '../public/assets/events/event_1701234567_1234.jpg';
move_uploaded_file($temp_file, $upload_path);
```

### 3. PHP Saves Path to Database
```php
// Save this path in database
$image_path = '/assets/events/event_1701234567_1234.jpg';

$sql = "INSERT INTO events (name, image_path) VALUES (?, ?)";
$stmt->bind_param('ss', $event_name, $image_path);
$stmt->execute();
```

### 4. Display Image in HTML/PHP
```php
// Get from database
$sql = "SELECT name, image_path FROM events WHERE event_id = 1";
$result = $conn->query($sql);
$event = $result->fetch_assoc();

// Display image
echo '<img src="' . $event['image_path'] . '">';
// Outputs: <img src="/assets/events/event_1701234567_1234.jpg">
```

## Summary

| What | Where It's Stored |
|------|-------------------|
| **Actual image file** | `/public/assets/events/event_123.jpg` (file system) |
| **Path to image** | Database column `image_path` |
| **How to display** | `<img src="/assets/events/event_123.jpg">` |

The database is FAST for paths (just text).
The file system is GOOD for actual images.
Together = Perfect! ✅
