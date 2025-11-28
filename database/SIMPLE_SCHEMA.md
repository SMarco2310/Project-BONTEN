# SIMPLIFIED DATABASE SCHEMA FOR PHPMYADMIN
## Easy Manual Table Creation Guide

Create these tables one by one in phpMyAdmin. Copy the SQL for each table.

---

## TABLE 1: users
**Purpose:** Store all users (both regular users and managers)

```sql
CREATE TABLE users (
    user_id INT PRIMARY KEY AUTO_INCREMENT,
    email VARCHAR(255) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(20),
    user_type ENUM('user', 'manager') DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

**Columns Explained:**
- `user_id` - Unique ID for each user
- `email` - User's email (for login)
- `password` - Hashed password (we'll use `password_hash()` in PHP)
- `username` - Display name
- `full_name` - Real name
- `phone` - Phone number
- `user_type` - Either 'user' or 'manager'
- `created_at` - When account was created

---

## TABLE 2: categories
**Purpose:** Event categories (Concert, Festival, etc.)

```sql
CREATE TABLE categories (
    category_id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(100) NOT NULL UNIQUE
);
```

**Pre-fill with data:**
```sql
INSERT INTO categories (name) VALUES
('Concert'), ('Festival'), ('Conference'), ('Workshop'),
('Sports'), ('Fashion'), ('Food & Drinks'), ('Party'), ('Other');
```

---

## TABLE 3: events
**Purpose:** Store all events created by managers

```sql
CREATE TABLE events (
    event_id INT PRIMARY KEY AUTO_INCREMENT,
    manager_id INT NOT NULL,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,
    event_date DATE NOT NULL,
    event_time TIME NOT NULL,
    location VARCHAR(255),
    city VARCHAR(100),
    event_type ENUM('in-person', 'online', 'hybrid') DEFAULT 'in-person',
    capacity INT,
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'active',
    image_path VARCHAR(500),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (manager_id) REFERENCES users(user_id),
    FOREIGN KEY (category_id) REFERENCES categories(category_id)
);
```

**Columns Explained:**
- `event_id` - Unique ID for event
- `manager_id` - Which manager created this event
- `category_id` - What category (Concert, Festival, etc.)
- `name` - Event name
- `description` - Event details
- `event_date` - When the event happens
- `event_time` - What time
- `location` - Venue name
- `city` - Which city
- `event_type` - In-person/Online/Hybrid
- `capacity` - Max attendees
- `status` - Active, Completed, Cancelled, or Draft
- `image_path` - Where image is stored (e.g., `/assets/ashchella.jpg`)

---

## TABLE 4: tickets
**Purpose:** Different ticket types for paid events

```sql
CREATE TABLE tickets (
    ticket_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    ticket_name VARCHAR(100) NOT NULL,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL,
    sold INT DEFAULT 0,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);
```

**Columns Explained:**
- `ticket_id` - Unique ID
- `event_id` - Which event this ticket is for
- `ticket_name` - "Early Bird", "VIP", etc.
- `price` - How much in GHC
- `quantity` - Total available
- `sold` - How many sold so far

---

## TABLE 5: rsvps
**Purpose:** Track who registered for which events

```sql
CREATE TABLE rsvps (
    rsvp_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    attended BOOLEAN DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Columns Explained:**
- `rsvp_id` - Unique ID
- `event_id` - Which event
- `user_id` - Which user registered
- `attended` - Did they show up? (0=No, 1=Yes)
- `created_at` - When they registered

---

## TABLE 6: reviews
**Purpose:** User reviews after events complete

```sql
CREATE TABLE reviews (
    review_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL CHECK (rating >= 1 AND rating <= 5),
    review_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Columns Explained:**
- `review_id` - Unique ID
- `event_id` - Which event
- `user_id` - Who wrote the review
- `rating` - 1 to 5 stars
- `review_text` - Their review
- `created_at` - When posted

---

## TABLE 7: comments
**Purpose:** Comments on events (before or after)

```sql
CREATE TABLE comments (
    comment_id INT PRIMARY KEY AUTO_INCREMENT,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    comment_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE
);
```

**Columns Explained:**
- `comment_id` - Unique ID
- `event_id` - Which event
- `user_id` - Who commented
- `comment_text` - The comment
- `created_at` - When posted

---

## TABLE 8: bookmarks
**Purpose:** Events users saved/bookmarked

```sql
CREATE TABLE bookmarks (
    bookmark_id INT PRIMARY KEY AUTO_INCREMENT,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE
);
```

**Columns Explained:**
- `bookmark_id` - Unique ID
- `user_id` - Who bookmarked
- `event_id` - Which event
- `created_at` - When bookmarked

---

## MANAGER DASHBOARD STATS

For manager graphs and analytics, use these **simple queries**:

### Total Events by Manager
```sql
SELECT COUNT(*) as total_events
FROM events
WHERE manager_id = 1;
```

### Total Tickets Sold
```sql
SELECT SUM(sold) as total_sold
FROM tickets t
JOIN events e ON t.event_id = e.event_id
WHERE e.manager_id = 1;
```

### Total Revenue
```sql
SELECT SUM(t.price * t.sold) as total_revenue
FROM tickets t
JOIN events e ON t.event_id = e.event_id
WHERE e.manager_id = 1;
```

### Average Rating
```sql
SELECT AVG(rating) as avg_rating
FROM reviews r
JOIN events e ON r.event_id = e.event_id
WHERE e.manager_id = 1;
```

### Events by Status
```sql
SELECT status, COUNT(*) as count
FROM events
WHERE manager_id = 1
GROUP BY status;
```

---

## THAT'S IT! Only 8 Simple Tables

**vs the complex schema:**
- ❌ Removed: tags, event_tags, ticket_purchases, complex triggers
- ✅ Kept: Essential data only
- ✅ Easy to understand and create manually
