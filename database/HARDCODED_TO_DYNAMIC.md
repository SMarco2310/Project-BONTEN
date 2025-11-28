# REPLACING HARDCODED DATA WITH DATABASE QUERIES

This shows EXACTLY how to replace every hardcoded piece of data with database queries.

---

## 1. USER HOMEPAGE - Event Cards

### Currently Hardcoded (explore.js lines 1-58)
```javascript
const events = [
    {
        id: 'ashchella',
        name: 'Ashchella',
        tag: 'ASC Week',
        imageUrl: '/assets/ashchella.JPG',
        description: 'Ashchella Description'
    },
    // ... more events
];
```

### Replace With Database Query
```php
<?php
require_once '../config/Database.php';
$db = new Database();
$conn = $db->connect();

// Get all active events with category
$sql = "SELECT
            e.event_id,
            e.name,
            e.description,
            e.event_date,
            e.location,
            e.city,
            e.image_path,
            c.name as category
        FROM events e
        LEFT JOIN categories c ON e.category_id = c.category_id
        WHERE e.status = 'active'
        ORDER BY e.event_date ASC";

$result = $conn->query($sql);
?>

<!-- Display events -->
<?php while ($event = $result->fetch_assoc()): ?>
<div class="event-card">
    <img src="<?php echo htmlspecialchars($event['image_path']); ?>" alt="Event">
    <span class="event-badge"><?php echo htmlspecialchars($event['category']); ?></span>
    <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
    <p class="event-location"><?php echo htmlspecialchars($event['location']); ?></p>
</div>
<?php endwhile; ?>
```

✅ **Now events come from database instead of hardcoded array!**

---

## 2. MANAGER DASHBOARD - Statistics

### Currently Hardcoded (manager_dashboard.js)
```javascript
// Mock data
ticketsSold: 2847,
totalRevenue: 142350,
averageRating: 4.7
```

### Replace With Database Queries
```php
<?php
session_start();
$manager_id = $_SESSION['user_id'];

// Total tickets sold
$sql = "SELECT SUM(t.sold) as total_sold
        FROM tickets t
        JOIN events e ON t.event_id = e.event_id
        WHERE e.manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$total_sold = $stmt->get_result()->fetch_assoc()['total_sold'] ?? 0;

// Total revenue
$sql = "SELECT SUM(t.price * t.sold) as revenue
        FROM tickets t
        JOIN events e ON t.event_id = e.event_id
        WHERE e.manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$revenue = $stmt->get_result()->fetch_assoc()['revenue'] ?? 0;

// Average rating
$sql = "SELECT AVG(r.rating) as avg_rating
        FROM reviews r
        JOIN events e ON r.event_id = e.event_id
        WHERE e.manager_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$avg_rating = $stmt->get_result()->fetch_assoc()['avg_rating'] ?? 0;
?>

<div class="stat-card">
    <h3><?php echo number_format($total_sold); ?></h3>
    <p>Tickets Sold</p>
</div>
<div class="stat-card">
    <h3>GHC <?php echo number_format($revenue, 2); ?></h3>
    <p>Total Revenue</p>
</div>
<div class="stat-card">
    <h3><?php echo number_format($avg_rating, 1); ?></h3>
    <p>Average Rating</p>
</div>
```

✅ **Real-time statistics from database!**

---

## 3. MANAGER HISTORY - Events List

### Currently Hardcoded (manager_history.js lines 108-299)
```javascript
const allEvents = [
    {
        id: 'ashchella-2024',
        name: 'Ashchella 2024',
        category: 'ASC Week',
        ticketsSold: 850,
        totalTickets: 1000,
        revenue: 42500,
        status: 'active'
    },
    // ... more events
];
```

### Replace With Database Query
```php
<?php
$manager_id = $_SESSION['user_id'];

// Get all events by this manager
$sql = "SELECT
            e.*,
            c.name as category_name,
            SUM(t.sold) as tickets_sold,
            SUM(t.quantity) as total_tickets,
            SUM(t.price * t.sold) as revenue,
            AVG(r.rating) as avg_rating,
            COUNT(DISTINCT r.review_id) as review_count
        FROM events e
        LEFT JOIN categories c ON e.category_id = c.category_id
        LEFT JOIN tickets t ON e.event_id = t.event_id
        LEFT JOIN reviews r ON e.event_id = r.event_id
        WHERE e.manager_id = ?
        GROUP BY e.event_id
        ORDER BY e.event_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $manager_id);
$stmt->execute();
$events = $stmt->get_result();
?>

<!-- Display events -->
<?php while ($event = $events->fetch_assoc()): ?>
<tr>
    <td>
        <img src="<?php echo $event['image_path']; ?>" class="event-thumbnail">
        <?php echo htmlspecialchars($event['name']); ?>
    </td>
    <td><?php echo date('M d, Y', strtotime($event['event_date'])); ?></td>
    <td><?php echo $event['tickets_sold'] ?? 0; ?> / <?php echo $event['total_tickets'] ?? 0; ?></td>
    <td>GHC <?php echo number_format($event['revenue'] ?? 0, 2); ?></td>
    <td><span class="status-badge <?php echo $event['status']; ?>"><?php echo ucfirst($event['status']); ?></span></td>
</tr>
<?php endwhile; ?>
```

✅ **All manager events from database with real stats!**

---

## 4. EVENT DETAILS PAGE - Reviews & Comments

### Currently Hardcoded (comments.js, event.js)
```javascript
// Hardcoded event data
const events = [
    {
        id: 'ashchella',
        name: 'Ashchella',
        description: 'Ashchella Description'
    }
];
```

### Replace With Database Query
```php
<?php
$event_id = $_GET['id'];

// Get event details
$sql = "SELECT
            e.*,
            c.name as category_name,
            u.username as manager_name,
            AVG(r.rating) as avg_rating,
            COUNT(DISTINCT r.review_id) as review_count
        FROM events e
        LEFT JOIN categories c ON e.category_id = c.category_id
        LEFT JOIN users u ON e.manager_id = u.user_id
        LEFT JOIN reviews r ON e.event_id = r.event_id
        WHERE e.event_id = ?
        GROUP BY e.event_id";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$event = $stmt->get_result()->fetch_assoc();

// Get reviews
$sql = "SELECT
            r.*,
            u.username,
            u.full_name
        FROM reviews r
        JOIN users u ON r.user_id = u.user_id
        WHERE r.event_id = ?
        ORDER BY r.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$reviews = $stmt->get_result();

// Get comments
$sql = "SELECT
            c.*,
            u.username
        FROM comments c
        JOIN users u ON c.user_id = u.user_id
        WHERE c.event_id = ?
        ORDER BY c.created_at DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$comments = $stmt->get_result();
?>

<!-- Event Details -->
<div class="event-header">
    <img src="<?php echo $event['image_path']; ?>" alt="Event">
    <h1><?php echo htmlspecialchars($event['name']); ?></h1>
    <p><?php echo htmlspecialchars($event['description']); ?></p>
    <div class="rating">
        <span>⭐ <?php echo number_format($event['avg_rating'], 1); ?></span>
        <span>(<?php echo $event['review_count']; ?> reviews)</span>
    </div>
</div>

<!-- Reviews -->
<div class="reviews-section">
    <?php while ($review = $reviews->fetch_assoc()): ?>
    <div class="review">
        <strong><?php echo htmlspecialchars($review['username']); ?></strong>
        <div class="stars">
            <?php for ($i = 0; $i < $review['rating']; $i++): ?>⭐<?php endfor; ?>
        </div>
        <p><?php echo htmlspecialchars($review['review_text']); ?></p>
        <small><?php echo date('M d, Y', strtotime($review['created_at'])); ?></small>
    </div>
    <?php endwhile; ?>
</div>

<!-- Comments -->
<div class="comments-section">
    <?php while ($comment = $comments->fetch_assoc()): ?>
    <div class="comment">
        <strong><?php echo htmlspecialchars($comment['username']); ?>:</strong>
        <p><?php echo htmlspecialchars($comment['comment_text']); ?></p>
        <small><?php echo date('M d, Y H:i', strtotime($comment['created_at'])); ?></small>
    </div>
    <?php endwhile; ?>
</div>
```

✅ **Event details, reviews, and comments all from database!**

---

## 5. TICKET INFORMATION

### Currently Hardcoded (manager_history.js)
```javascript
ticketTypes: [
    { name: 'Early Bird', sold: 200, total: 200, price: 40 },
    { name: 'Regular', sold: 500, total: 600, price: 50 },
    { name: 'VIP', sold: 150, total: 200, price: 80 }
]
```

### Replace With Database Query
```php
<?php
$event_id = $_GET['event_id'];

// Get tickets for this event
$sql = "SELECT * FROM tickets WHERE event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $event_id);
$stmt->execute();
$tickets = $stmt->get_result();
?>

<div class="tickets-list">
    <?php while ($ticket = $tickets->fetch_assoc()): ?>
    <div class="ticket-type">
        <h4><?php echo htmlspecialchars($ticket['ticket_name']); ?></h4>
        <p>GHC <?php echo number_format($ticket['price'], 2); ?></p>
        <p><?php echo $ticket['sold']; ?> / <?php echo $ticket['quantity']; ?> sold</p>
        <div class="progress-bar">
            <div style="width: <?php echo ($ticket['sold']/$ticket['quantity']*100); ?>%"></div>
        </div>
    </div>
    <?php endwhile; ?>
</div>
```

✅ **Ticket types and sales from database!**

---

## 6. USER PROFILE & SESSION

### Currently Hardcoded (manager_dashboard.html)
```html
<div class="user_info">
    <h4 class="username">Jerome Adedze</h4>
</div>
```

### Replace With Session Data
```php
<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Get user info
$user_id = $_SESSION['user_id'];
$sql = "SELECT * FROM users WHERE user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
?>

<div class="user_info">
    <h4 class="username"><?php echo htmlspecialchars($user['username']); ?></h4>
    <p><?php echo htmlspecialchars($user['email']); ?></p>
</div>
```

✅ **User info from database session!**

---

## 7. USER EVENT HISTORY

### Currently Hardcoded (history.js)
```html
<!-- Static event cards -->
<div class="history-card" data-event-id="ashchella">
    <img src="../public/assets/ashchella.JPG" alt="Ashchella">
    <span class="event-name">Ashchella 2024</span>
</div>
```

### Replace With Database Query
```php
<?php
$user_id = $_SESSION['user_id'];

// Get user's RSVP'd events
$sql = "SELECT
            e.*,
            c.name as category_name,
            r.created_at as rsvp_date,
            r.attended
        FROM rsvps r
        JOIN events e ON r.event_id = e.event_id
        LEFT JOIN categories c ON e.category_id = c.category_id
        WHERE r.user_id = ?
        ORDER BY e.event_date DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $user_id);
$stmt->execute();
$user_events = $stmt->get_result();
?>

<?php while ($event = $user_events->fetch_assoc()): ?>
<div class="history-card" data-event-id="<?php echo $event['event_id']; ?>">
    <img src="<?php echo $event['image_path']; ?>" alt="Event">
    <div class="event-info">
        <span class="event-badge"><?php echo $event['category_name']; ?></span>
        <h3 class="event-name"><?php echo htmlspecialchars($event['name']); ?></h3>
        <p><?php echo date('M d, Y', strtotime($event['event_date'])); ?></p>
        <?php if ($event['attended']): ?>
            <span class="attended-badge">✓ Attended</span>
        <?php endif; ?>
    </div>
</div>
<?php endwhile; ?>
```

✅ **User event history from database!**

---

## 8. BOOKMARKS

### Currently in localStorage
```javascript
// bookmarks stored in browser
localStorage.setItem('bookmarks', JSON.stringify(bookmarkedEvents));
```

### Replace With Database
```php
<?php
// Check if user bookmarked this event
$sql = "SELECT * FROM bookmarks WHERE user_id = ? AND event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $event_id);
$stmt->execute();
$is_bookmarked = $stmt->get_result()->num_rows > 0;
?>

<button class="bookmark-btn <?php echo $is_bookmarked ? 'bookmarked' : ''; ?>"
        onclick="toggleBookmark(<?php echo $event_id; ?>)">
    <?php echo $is_bookmarked ? '🔖' : '⬜'; ?>
</button>

<!-- AJAX to toggle bookmark -->
<script>
function toggleBookmark(eventId) {
    fetch('toggle_bookmark.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({event_id: eventId})
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            location.reload();
        }
    });
}
</script>
```

```php
<!-- toggle_bookmark.php -->
<?php
session_start();
require_once '../config/Database.php';

$data = json_decode(file_get_contents('php://input'), true);
$user_id = $_SESSION['user_id'];
$event_id = $data['event_id'];

$db = new Database();
$conn = $db->connect();

// Check if exists
$sql = "SELECT * FROM bookmarks WHERE user_id = ? AND event_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('ii', $user_id, $event_id);
$stmt->execute();

if ($stmt->get_result()->num_rows > 0) {
    // Remove bookmark
    $sql = "DELETE FROM bookmarks WHERE user_id = ? AND event_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $event_id);
    $stmt->execute();
} else {
    // Add bookmark
    $sql = "INSERT INTO bookmarks (user_id, event_id) VALUES (?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('ii', $user_id, $event_id);
    $stmt->execute();
}

echo json_encode(['success' => true]);
?>
```

✅ **Bookmarks saved in database across devices!**

---

## SUMMARY: ALL HARDCODED DATA → DATABASE

| Hardcoded Data | Database Table | SQL Query |
|----------------|----------------|-----------|
| Event list | `events` | `SELECT * FROM events WHERE status='active'` |
| User info | `users` | `SELECT * FROM users WHERE user_id=?` |
| Tickets | `tickets` | `SELECT * FROM tickets WHERE event_id=?` |
| Reviews | `reviews` | `SELECT * FROM reviews WHERE event_id=?` |
| Comments | `comments` | `SELECT * FROM comments WHERE event_id=?` |
| RSVPs | `rsvps` | `SELECT * FROM rsvps WHERE user_id=?` |
| Bookmarks | `bookmarks` | `SELECT * FROM bookmarks WHERE user_id=?` |
| Stats | JOIN queries | Multiple aggregation queries |

## ✅ YES, THE DATABASE SUPPORTS EVERYTHING!

Every single hardcoded piece of data can be replaced with database queries.
The 8 tables cover ALL your app's functionality!
