-- ============================================================================
-- BONTEN Event Management System - Database Schema
-- ============================================================================
-- This schema supports all functionalities including:
-- - User authentication and profiles
-- - Event creation, management, and categorization
-- - Ticket sales and types
-- - Event reviews and ratings
-- - Comments on events
-- - User event history (RSVPs, attendance)
-- - Manager dashboard analytics
-- - Event bookmarks/favorites
-- - Event tags and categories
-- ============================================================================

-- Drop existing tables if they exist (for fresh installation)
DROP TABLE IF EXISTS comments;
DROP TABLE IF EXISTS reviews;
DROP TABLE IF EXISTS rsvps;
DROP TABLE IF EXISTS ticket_purchases;
DROP TABLE IF EXISTS tickets;
DROP TABLE IF EXISTS event_tags;
DROP TABLE IF EXISTS tags;
DROP TABLE IF EXISTS bookmarks;
DROP TABLE IF EXISTS events;
DROP TABLE IF EXISTS users;
DROP TABLE IF EXISTS categories;

-- ============================================================================
-- USERS TABLE
-- ============================================================================
-- Stores both regular users and event managers
CREATE TABLE users (
    user_id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(255) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    username VARCHAR(100) NOT NULL,
    full_name VARCHAR(255),
    phone VARCHAR(20),
    profile_picture VARCHAR(500),
    user_type ENUM('user', 'manager') DEFAULT 'user',
    bio TEXT,
    location VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    email_verified BOOLEAN DEFAULT FALSE,
    INDEX idx_email (email),
    INDEX idx_user_type (user_type),
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- CATEGORIES TABLE
-- ============================================================================
-- Event categories (Concert, Festival, Conference, Workshop, Sports, etc.)
CREATE TABLE categories (
    category_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL UNIQUE,
    slug VARCHAR(100) NOT NULL UNIQUE,
    description TEXT,
    icon VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- EVENTS TABLE
-- ============================================================================
-- Core events table with all event information
CREATE TABLE events (
    event_id INT AUTO_INCREMENT PRIMARY KEY,
    manager_id INT NOT NULL,
    category_id INT,
    name VARCHAR(255) NOT NULL,
    slug VARCHAR(255) NOT NULL,
    description TEXT NOT NULL,

    -- Date and Time
    start_date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_date DATE NOT NULL,
    end_time TIME NOT NULL,
    timezone VARCHAR(100) DEFAULT 'GMT',

    -- Location
    event_type ENUM('in-person', 'online', 'hybrid') NOT NULL,
    venue VARCHAR(255),
    address TEXT,
    city VARCHAR(100),
    region VARCHAR(100),

    -- Online event details
    platform VARCHAR(100),
    stream_url VARCHAR(500),

    -- Event settings
    capacity INT,
    visibility ENUM('public', 'private', 'unlisted') DEFAULT 'public',
    status ENUM('draft', 'active', 'completed', 'cancelled') DEFAULT 'draft',
    cancellation_reason TEXT,

    -- Ticket settings
    ticket_type ENUM('free', 'paid') DEFAULT 'free',
    free_ticket_quantity INT,
    require_approval BOOLEAN DEFAULT FALSE,
    collect_phone BOOLEAN DEFAULT FALSE,
    allow_refunds BOOLEAN DEFAULT FALSE,

    -- Media
    image_url VARCHAR(500),

    -- Timestamps
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    cancelled_at TIMESTAMP NULL,

    -- Foreign Keys
    FOREIGN KEY (manager_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE SET NULL,

    -- Indexes
    INDEX idx_manager (manager_id),
    INDEX idx_category (category_id),
    INDEX idx_status (status),
    INDEX idx_event_type (event_type),
    INDEX idx_start_date (start_date),
    INDEX idx_slug (slug),
    INDEX idx_visibility (visibility),
    INDEX idx_ticket_type (ticket_type)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TAGS TABLE
-- ============================================================================
-- Custom tags for events
CREATE TABLE tags (
    tag_id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    slug VARCHAR(50) NOT NULL UNIQUE,
    usage_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_slug (slug),
    INDEX idx_usage_count (usage_count)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- EVENT_TAGS TABLE
-- ============================================================================
-- Many-to-many relationship between events and tags
CREATE TABLE event_tags (
    event_tag_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    tag_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (tag_id) REFERENCES tags(tag_id) ON DELETE CASCADE,
    UNIQUE KEY unique_event_tag (event_id, tag_id),
    INDEX idx_event (event_id),
    INDEX idx_tag (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TICKETS TABLE
-- ============================================================================
-- Different ticket types for paid events
CREATE TABLE tickets (
    ticket_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    name VARCHAR(100) NOT NULL,
    description TEXT,
    price DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    quantity INT NOT NULL,
    sold INT DEFAULT 0,
    min_purchase INT DEFAULT 1,
    max_purchase INT DEFAULT 10,
    sales_start TIMESTAMP NULL,
    sales_end TIMESTAMP NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_active (is_active),
    CHECK (sold <= quantity),
    CHECK (price >= 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- TICKET_PURCHASES TABLE
-- ============================================================================
-- Records of ticket purchases
CREATE TABLE ticket_purchases (
    purchase_id INT AUTO_INCREMENT PRIMARY KEY,
    ticket_id INT NOT NULL,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    quantity INT NOT NULL DEFAULT 1,
    unit_price DECIMAL(10, 2) NOT NULL,
    total_amount DECIMAL(10, 2) NOT NULL,
    payment_status ENUM('pending', 'completed', 'failed', 'refunded') DEFAULT 'pending',
    payment_method VARCHAR(50),
    transaction_id VARCHAR(255),
    purchased_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    refunded_at TIMESTAMP NULL,
    FOREIGN KEY (ticket_id) REFERENCES tickets(ticket_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    INDEX idx_user (user_id),
    INDEX idx_event (event_id),
    INDEX idx_ticket (ticket_id),
    INDEX idx_payment_status (payment_status),
    INDEX idx_purchased_at (purchased_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- RSVPS TABLE
-- ============================================================================
-- User RSVPs for events (both free and paid)
CREATE TABLE rsvps (
    rsvp_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'cancelled', 'attended') DEFAULT 'pending',
    attended BOOLEAN DEFAULT FALSE,
    checked_in_at TIMESTAMP NULL,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    cancelled_at TIMESTAMP NULL,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_rsvp (event_id, user_id),
    INDEX idx_event (event_id),
    INDEX idx_user (user_id),
    INDEX idx_status (status),
    INDEX idx_attended (attended)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- REVIEWS TABLE
-- ============================================================================
-- User reviews for events (only after event completion)
CREATE TABLE reviews (
    review_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT NOT NULL,
    title VARCHAR(255),
    review_text TEXT NOT NULL,
    is_verified_attendee BOOLEAN DEFAULT FALSE,
    helpful_count INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    UNIQUE KEY unique_review (event_id, user_id),
    INDEX idx_event (event_id),
    INDEX idx_user (user_id),
    INDEX idx_rating (rating),
    INDEX idx_created_at (created_at),
    CHECK (rating >= 1 AND rating <= 5)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- COMMENTS TABLE
-- ============================================================================
-- Comments on events (similar to reviews but more casual)
CREATE TABLE comments (
    comment_id INT AUTO_INCREMENT PRIMARY KEY,
    event_id INT NOT NULL,
    user_id INT NOT NULL,
    rating INT,
    comment_text TEXT NOT NULL,
    parent_comment_id INT NULL,
    is_approved BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (parent_comment_id) REFERENCES comments(comment_id) ON DELETE CASCADE,
    INDEX idx_event (event_id),
    INDEX idx_user (user_id),
    INDEX idx_parent (parent_comment_id),
    INDEX idx_created_at (created_at),
    CHECK (rating IS NULL OR (rating >= 1 AND rating <= 5))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- BOOKMARKS TABLE
-- ============================================================================
-- User bookmarks/favorites for events
CREATE TABLE bookmarks (
    bookmark_id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    event_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(user_id) ON DELETE CASCADE,
    FOREIGN KEY (event_id) REFERENCES events(event_id) ON DELETE CASCADE,
    UNIQUE KEY unique_bookmark (user_id, event_id),
    INDEX idx_user (user_id),
    INDEX idx_event (event_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================================================
-- VIEWS FOR ANALYTICS AND REPORTING
-- ============================================================================

-- Event summary statistics view
CREATE OR REPLACE VIEW event_statistics AS
SELECT
    e.event_id,
    e.name,
    e.manager_id,
    e.status,
    COUNT(DISTINCT r.rsvp_id) as total_rsvps,
    SUM(CASE WHEN r.attended = TRUE THEN 1 ELSE 0 END) as total_checkins,
    COUNT(DISTINCT tp.purchase_id) as total_purchases,
    COALESCE(SUM(tp.total_amount), 0) as total_revenue,
    COALESCE(AVG(rev.rating), 0) as average_rating,
    COUNT(DISTINCT rev.review_id) as total_reviews,
    COUNT(DISTINCT c.comment_id) as total_comments,
    COUNT(DISTINCT b.bookmark_id) as total_bookmarks
FROM events e
LEFT JOIN rsvps r ON e.event_id = r.event_id
LEFT JOIN ticket_purchases tp ON e.event_id = tp.event_id AND tp.payment_status = 'completed'
LEFT JOIN reviews rev ON e.event_id = rev.event_id
LEFT JOIN comments c ON e.event_id = c.event_id
LEFT JOIN bookmarks b ON e.event_id = b.event_id
GROUP BY e.event_id;

-- Manager dashboard statistics view
CREATE OR REPLACE VIEW manager_dashboard_stats AS
SELECT
    u.user_id as manager_id,
    u.username as manager_name,
    COUNT(DISTINCT e.event_id) as total_events,
    SUM(CASE WHEN e.status = 'active' THEN 1 ELSE 0 END) as active_events,
    SUM(CASE WHEN e.status = 'completed' THEN 1 ELSE 0 END) as completed_events,
    SUM(CASE WHEN e.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled_events,
    COUNT(DISTINCT r.rsvp_id) as total_rsvps,
    COALESCE(SUM(tp.total_amount), 0) as total_revenue,
    COALESCE(AVG(rev.rating), 0) as average_rating
FROM users u
LEFT JOIN events e ON u.user_id = e.manager_id
LEFT JOIN rsvps r ON e.event_id = r.event_id
LEFT JOIN ticket_purchases tp ON e.event_id = tp.event_id AND tp.payment_status = 'completed'
LEFT JOIN reviews rev ON e.event_id = rev.event_id
WHERE u.user_type = 'manager'
GROUP BY u.user_id;

-- ============================================================================
-- TRIGGERS
-- ============================================================================

-- Update ticket sold count when purchase is made
DELIMITER $$
CREATE TRIGGER after_ticket_purchase_insert
AFTER INSERT ON ticket_purchases
FOR EACH ROW
BEGIN
    IF NEW.payment_status = 'completed' THEN
        UPDATE tickets
        SET sold = sold + NEW.quantity
        WHERE ticket_id = NEW.ticket_id;
    END IF;
END$$

-- Update ticket sold count when purchase is refunded
CREATE TRIGGER after_ticket_purchase_update
AFTER UPDATE ON ticket_purchases
FOR EACH ROW
BEGIN
    IF OLD.payment_status = 'completed' AND NEW.payment_status = 'refunded' THEN
        UPDATE tickets
        SET sold = sold - NEW.quantity
        WHERE ticket_id = NEW.ticket_id;
    END IF;

    IF OLD.payment_status != 'completed' AND NEW.payment_status = 'completed' THEN
        UPDATE tickets
        SET sold = sold + NEW.quantity
        WHERE ticket_id = NEW.ticket_id;
    END IF;
END$$

-- Update tag usage count when event_tag is created
CREATE TRIGGER after_event_tag_insert
AFTER INSERT ON event_tags
FOR EACH ROW
BEGIN
    UPDATE tags
    SET usage_count = usage_count + 1
    WHERE tag_id = NEW.tag_id;
END$$

-- Update tag usage count when event_tag is deleted
CREATE TRIGGER after_event_tag_delete
AFTER DELETE ON event_tags
FOR EACH ROW
BEGIN
    UPDATE tags
    SET usage_count = usage_count - 1
    WHERE tag_id = OLD.tag_id;
END$$

-- Set published_at timestamp when event becomes active
CREATE TRIGGER before_event_update
BEFORE UPDATE ON events
FOR EACH ROW
BEGIN
    IF OLD.status != 'active' AND NEW.status = 'active' AND NEW.published_at IS NULL THEN
        SET NEW.published_at = CURRENT_TIMESTAMP;
    END IF;

    IF OLD.status != 'cancelled' AND NEW.status = 'cancelled' AND NEW.cancelled_at IS NULL THEN
        SET NEW.cancelled_at = CURRENT_TIMESTAMP;
    END IF;
END$$

DELIMITER ;

-- ============================================================================
-- INDEXES FOR PERFORMANCE OPTIMIZATION
-- ============================================================================

-- Additional composite indexes for common queries
CREATE INDEX idx_events_manager_status ON events(manager_id, status);
CREATE INDEX idx_events_category_status ON events(category_id, status);
CREATE INDEX idx_events_date_status ON events(start_date, status);
CREATE INDEX idx_rsvps_user_status ON rsvps(user_id, status);
CREATE INDEX idx_purchases_user_event ON ticket_purchases(user_id, event_id);

-- Full-text search indexes
ALTER TABLE events ADD FULLTEXT INDEX ft_event_search (name, description);
ALTER TABLE users ADD FULLTEXT INDEX ft_user_search (username, full_name);

-- ============================================================================
-- END OF SCHEMA
-- ============================================================================
