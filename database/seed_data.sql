-- ============================================================================
-- BONTEN Event Management System - Sample Data Seed
-- ============================================================================
-- This file contains sample data for testing and development purposes.
-- Run this AFTER executing schema.sql to populate the database with test data.
-- ============================================================================

-- Clear existing data (optional - be careful in production!)
-- TRUNCATE TABLE bookmarks;
-- TRUNCATE TABLE comments;
-- TRUNCATE TABLE reviews;
-- TRUNCATE TABLE rsvps;
-- TRUNCATE TABLE ticket_purchases;
-- TRUNCATE TABLE tickets;
-- TRUNCATE TABLE event_tags;
-- TRUNCATE TABLE tags;
-- TRUNCATE TABLE events;
-- DELETE FROM users WHERE user_id > 1;

-- ============================================================================
-- SAMPLE USERS
-- ============================================================================

-- Regular users (password for all: password123)
INSERT INTO users (email, password_hash, username, full_name, phone, user_type, bio, location, is_active, email_verified) VALUES
('john.doe@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'johndoe', 'John Doe', '+233244123456', 'user', 'Music lover and event enthusiast', 'Accra, Ghana', 1, 1),
('jane.smith@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'janesmith', 'Jane Smith', '+233244234567', 'user', 'Love attending concerts and festivals', 'Kumasi, Ghana', 1, 1),
('kofi.mensah@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kofimensah', 'Kofi Mensah', '+233244345678', 'user', 'Tech enthusiast and conference goer', 'Accra, Ghana', 1, 1),
('ama.serwaa@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'amaserwaa', 'Ama Serwaa', '+233244456789', 'user', 'Foodie and culture lover', 'Cape Coast, Ghana', 1, 1),
('kwame.asante@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'kwameasante', 'Kwame Asante', '+233244567890', 'user', 'Sports fan and event organizer', 'Takoradi, Ghana', 1, 1);

-- Event managers (password: manager123)
INSERT INTO users (email, password_hash, username, full_name, phone, user_type, bio, location, is_active, email_verified) VALUES
('jerome.adedze@bonten.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'jeromeadedze', 'Jerome Adedze', '+233244678901', 'manager', 'Professional event manager with 5+ years experience', 'Accra, Ghana', 1, 1),
('sarah.johnson@bonten.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sarahjohnson', 'Sarah Johnson', '+233244789012', 'manager', 'Music events specialist', 'Accra, Ghana', 1, 1),
('david.osei@bonten.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'davidosei', 'David Osei', '+233244890123', 'manager', 'Corporate events and conferences', 'Kumasi, Ghana', 1, 1);

-- ============================================================================
-- SAMPLE TAGS
-- ============================================================================

INSERT INTO tags (name, slug, usage_count) VALUES
('Music', 'music', 5),
('Live', 'live', 4),
('Weekend', 'weekend', 3),
('Family Friendly', 'family-friendly', 2),
('Outdoor', 'outdoor', 3),
('Indoor', 'indoor', 2),
('VIP Available', 'vip-available', 4),
('Food & Drinks', 'food-drinks', 2),
('Networking', 'networking', 1),
('Educational', 'educational', 1);

-- ============================================================================
-- SAMPLE EVENTS
-- ============================================================================

-- Active Events (Future events)
INSERT INTO events (manager_id, category_id, name, slug, description, start_date, start_time, end_date, end_time, timezone, event_type, venue, address, city, region, capacity, visibility, status, ticket_type, image_url, published_at) VALUES
(6, 1, 'Ashchella 2024', 'ashchella-2024', 'The biggest music festival of the year featuring top local and international artists. Experience an unforgettable weekend of music, culture, and community at Ashesi University campus. With multiple stages, food vendors, and exciting activities, Ashchella promises to be the highlight of your year!',
'2024-12-25', '14:00:00', '2024-12-26', '02:00:00', 'GMT', 'in-person', 'Ashesi University', '1 University Avenue', 'Berekuso', 'Eastern Region', 1000, 'public', 'active', 'paid', '/assets/ashchella.jpg', NOW()),

(6, 9, 'Y2K Neon Party', 'y2k-neon-party-2024', 'Step back into the early 2000s with this spectacular Y2K themed party! Dress in your best neon outfits and dance the night away to throwback hits. Professional DJs, themed cocktails, and amazing vibes guaranteed.',
'2024-12-28', '20:00:00', '2024-12-29', '04:00:00', 'GMT', 'in-person', 'Republic Bar & Grill', 'Ridge Area', 'Accra', 'Greater Accra', 500, 'public', 'active', 'paid', '/assets/y2k.JPG', NOW()),

(7, 1, 'New Year Bash 2025', 'new-year-bash-2025', 'Ring in the new year with the most spectacular celebration in Accra! Live performances, fireworks, premium open bar, and an unforgettable countdown to 2025. Multiple entertainment zones and celebrity appearances confirmed.',
'2024-12-31', '21:00:00', '2025-01-01', '05:00:00', 'GMT', 'hybrid', 'Labadi Beach Hotel', 'Labadi Beach Road', 'Accra', 'Greater Accra', 2000, 'public', 'active', 'paid', '/assets/detty.webp', NOW());

-- Completed Events (Past events)
INSERT INTO events (manager_id, category_id, name, slug, description, start_date, start_time, end_date, end_time, timezone, event_type, venue, address, city, region, capacity, visibility, status, ticket_type, image_url, published_at) VALUES
(7, 1, 'Tidal Rave 2023', 'tidal-rave-2023', 'The ultimate beach rave experience with world-class DJs and stunning ocean views. Dancing under the stars with the waves as your backdrop - an experience you will never forget.',
'2023-12-20', '18:00:00', '2023-12-21', '04:00:00', 'GMT', 'in-person', 'Labadi Beach', 'Labadi Beach Road', 'Accra', 'Greater Accra', 1500, 'public', 'completed', 'paid', '/assets/tidalrave.jpg', '2023-11-15 10:00:00'),

(6, 5, 'Global Football Festival', 'global-football-festival-2023', 'A family-friendly football festival featuring youth tournaments, celebrity matches, skill challenges, and football clinics. Fun for the whole family with food stalls, entertainment, and prizes to be won!',
'2023-12-01', '08:00:00', '2023-12-01', '18:00:00', 'GMT', 'in-person', 'Accra Sports Stadium', 'Stadium Road', 'Accra', 'Greater Accra', 2500, 'public', 'completed', 'paid', '/assets/gff.jpg', '2023-10-20 09:00:00'),

(7, 1, 'Rapperholic 2023', 'rapperholic-2023', 'Sarkodie live in concert! The legendary Rapperholic concert series returns with special guest performances, surprise appearances, and an incredible production that will blow your mind. The biggest hip-hop event of the year!',
'2023-11-28', '19:00:00', '2023-11-29', '01:00:00', 'GMT', 'in-person', 'Accra International Conference Centre', 'Osu Ringway', 'Accra', 'Greater Accra', 3000, 'public', 'completed', 'paid', '/assets/rapperholic.jpeg', '2023-10-01 08:00:00'),

(6, 1, 'iMullar Experience', 'imullar-experience-2023', 'An intimate evening with rising star iMullar featuring acoustic performances, storytelling, and fan interaction. Limited seating for an exclusive experience with one of Ghana''s most promising artists.',
'2023-11-20', '19:00:00', '2023-11-20', '23:00:00', 'GMT', 'in-person', '+233 Jazz Bar & Grill', 'Osu Oxford Street', 'Accra', 'Greater Accra', 500, 'public', 'completed', 'paid', '/assets/imullar.jpg', '2023-10-15 12:00:00');

-- Cancelled Event
INSERT INTO events (manager_id, category_id, name, slug, description, start_date, start_time, end_date, end_time, timezone, event_type, venue, address, city, region, capacity, visibility, status, cancellation_reason, ticket_type, image_url, published_at, cancelled_at) VALUES
(7, 9, 'Summer Splash', 'summer-splash-2023', 'The ultimate pool party with live DJs, water games, and refreshing cocktails. Beat the heat with non-stop entertainment and fun in the sun!',
'2023-08-15', '12:00:00', '2023-08-15', '20:00:00', 'GMT', 'in-person', 'Aqua Safari Resort', 'Tema Road', 'Accra', 'Greater Accra', 300, 'public', 'cancelled', 'Weather conditions - Heavy rainfall forecasted for the event day. Safety of attendees is our priority.', 'paid', '/assets/t&b.jpg', '2023-07-01 10:00:00', '2023-08-10 15:30:00');

-- Draft Events
INSERT INTO events (manager_id, category_id, name, slug, description, start_date, start_time, end_date, end_time, timezone, event_type, venue, city, visibility, status, ticket_type) VALUES
(6, 1, 'Valentine Special', 'valentine-special-2025', 'A romantic evening celebrating love with live music, fine dining, and special performances. Perfect for couples looking for an unforgettable Valentine''s Day experience.',
'2025-02-14', '19:00:00', '2025-02-15', '00:00:00', 'GMT', 'in-person', 'TBD', 'Accra', 'public', 'draft', 'paid'),

(7, 2, 'Easter Festival 2025', 'easter-festival-2025', 'A celebration of culture, music, and community featuring local and international artists. Food, crafts, and family-friendly activities for everyone.',
'2025-04-20', '10:00:00', '2025-04-20', '22:00:00', 'GMT', 'in-person', 'TBD', 'Accra', 'public', 'draft', 'paid');

-- ============================================================================
-- EVENT TAGS
-- ============================================================================

-- Ashchella tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(1, 1), (1, 2), (1, 3), (1, 5), (1, 7);

-- Y2K Neon Party tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(2, 1), (2, 2), (2, 7);

-- New Year Bash tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(3, 1), (3, 2), (3, 7), (3, 8);

-- Tidal Rave tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(4, 1), (4, 2), (4, 5), (4, 7);

-- Global Football Festival tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(5, 4), (5, 5), (5, 8);

-- Rapperholic tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(6, 1), (6, 2), (6, 7);

-- iMullar Experience tags
INSERT INTO event_tags (event_id, tag_id) VALUES
(7, 1), (7, 2), (7, 6);

-- ============================================================================
-- TICKETS
-- ============================================================================

-- Ashchella 2024 Tickets
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(1, 'Early Bird', 'Limited early bird special pricing', 40.00, 200, 200),
(1, 'Regular', 'Standard admission ticket', 50.00, 600, 500),
(1, 'VIP', 'VIP access with exclusive perks', 80.00, 200, 150);

-- Y2K Neon Party Tickets
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(2, 'General Admission', 'Standard entry', 50.00, 400, 250),
(2, 'VIP Table', 'Reserved VIP table for 4', 100.00, 100, 70);

-- New Year Bash 2025 Tickets
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(3, 'Standard', 'Standard admission with open bar', 60.00, 1500, 800),
(3, 'Premium', 'Premium seating area with bottle service', 120.00, 400, 300),
(3, 'VVIP', 'Exclusive VVIP lounge access', 250.00, 100, 100);

-- Tidal Rave 2023 Tickets (Completed)
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(4, 'Early Bird', 'Early bird pricing', 60.00, 500, 500),
(4, 'Regular', 'Standard admission', 75.00, 800, 800),
(4, 'VIP', 'VIP beach cabana access', 120.00, 200, 200);

-- Global Football Festival Tickets (Completed)
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(5, 'Adult', 'Adult admission', 40.00, 1800, 1500),
(5, 'Child', 'Child admission (under 12)', 20.00, 500, 500),
(5, 'Family Pack', 'Family of 4 admission', 100.00, 200, 200);

-- Rapperholic 2023 Tickets (Completed)
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(6, 'Regular', 'Standard admission', 60.00, 2000, 2000),
(6, 'VIP', 'VIP section with meet & greet', 100.00, 800, 800),
(6, 'VVIP', 'Exclusive backstage access', 200.00, 200, 200);

-- iMullar Experience Tickets (Completed)
INSERT INTO tickets (event_id, name, description, price, quantity, sold) VALUES
(7, 'Standard', 'General seating', 80.00, 400, 350),
(7, 'VIP Table', 'Reserved table for 4', 200.00, 100, 100);

-- ============================================================================
-- RSVPs
-- ============================================================================

-- Active event RSVPs
INSERT INTO rsvps (event_id, user_id, status, attended) VALUES
(1, 2, 'approved', 0),
(1, 3, 'approved', 0),
(1, 4, 'approved', 0),
(2, 2, 'approved', 0),
(2, 5, 'approved', 0),
(3, 2, 'approved', 0),
(3, 3, 'approved', 0),
(3, 4, 'approved', 0),
(3, 5, 'approved', 0);

-- Past event RSVPs (with attendance)
INSERT INTO rsvps (event_id, user_id, status, attended, checked_in_at) VALUES
(4, 2, 'approved', 1, '2023-12-20 18:15:00'),
(4, 3, 'approved', 1, '2023-12-20 18:30:00'),
(4, 4, 'approved', 1, '2023-12-20 19:00:00'),
(5, 2, 'approved', 1, '2023-12-01 08:30:00'),
(5, 3, 'approved', 1, '2023-12-01 09:00:00'),
(5, 5, 'approved', 1, '2023-12-01 08:45:00'),
(6, 2, 'approved', 1, '2023-11-28 19:15:00'),
(6, 3, 'approved', 1, '2023-11-28 19:30:00'),
(6, 4, 'approved', 1, '2023-11-28 19:45:00'),
(6, 5, 'approved', 1, '2023-11-28 20:00:00'),
(7, 2, 'approved', 1, '2023-11-20 19:00:00'),
(7, 4, 'approved', 1, '2023-11-20 19:15:00');

-- ============================================================================
-- REVIEWS
-- ============================================================================

-- Reviews for completed events
INSERT INTO reviews (event_id, user_id, rating, title, review_text, is_verified_attendee, created_at) VALUES
(4, 2, 5, 'Amazing beach party!', 'Best beach party I have ever attended! The music was incredible, the vibes were unmatched, and the location was perfect. Will definitely be back next year!', 1, '2023-12-22 10:30:00'),
(4, 3, 4, 'Great vibes!', 'Great music, great people, great atmosphere. Only downside was the long lines at the bar, but overall an excellent experience.', 1, '2023-12-22 15:45:00'),
(4, 4, 5, 'Unforgettable night', 'Dancing under the stars by the beach - what more could you ask for? The organizers did an amazing job. Can not wait for the next one!', 1, '2023-12-23 09:00:00'),

(5, 2, 4, 'Good family event', 'My kids had a blast! Lots of activities and the football clinics were well organized. Food lines were a bit long but overall great value.', 1, '2023-12-02 14:20:00'),
(5, 3, 5, 'Perfect family day out', 'Excellent event for families! My children loved the skill challenges and we all enjoyed the celebrity match. Well organized and fun for all ages.', 1, '2023-12-02 18:30:00'),
(5, 5, 4, 'Great for kids', 'The kids zone was fantastic and my son won a football in the raffle! Only criticism is that parking was a nightmare.', 1, '2023-12-03 10:15:00'),

(6, 2, 5, 'Sarkodie is a legend!', 'Rapperholic never disappoints! Sarkodie put on an incredible show. The energy in the crowd was electric. Best concert of the year!', 1, '2023-11-29 11:00:00'),
(6, 3, 5, 'Best concert ever!', 'I have been to many concerts but this was on another level. The production, the performances, the special guests - everything was perfect!', 1, '2023-11-29 14:30:00'),
(6, 4, 5, 'Worth every cedi', 'Expensive but absolutely worth it. The VIP experience was amazing and getting to meet Sarkodie was a dream come true!', 1, '2023-11-30 09:45:00'),
(6, 5, 4, 'Great show', 'Fantastic performances all night long. Only issue was the venue got quite crowded, but the show itself was phenomenal.', 1, '2023-11-30 16:20:00'),

(7, 2, 5, 'Intimate and special', 'Such a unique experience getting to see iMullar in this intimate setting. The acoustic performances were beautiful.', 1, '2023-11-21 10:00:00'),
(7, 4, 5, 'Loved every minute', 'The storytelling between songs made this so much more than just a concert. iMullar is incredibly talented. Highly recommend!', 1, '2023-11-21 15:30:00');

-- ============================================================================
-- COMMENTS
-- ============================================================================

-- Comments on events (can be before or after the event)
INSERT INTO comments (event_id, user_id, rating, comment_text, created_at) VALUES
(1, 2, 5, 'So excited for Ashchella! Already got my VIP tickets!', '2024-11-15 12:00:00'),
(1, 3, NULL, 'Who else is going? Let me know if you want to meet up!', '2024-11-20 14:30:00'),
(1, 4, 5, 'The lineup looks incredible this year. Can not wait!', '2024-11-25 09:15:00'),

(2, 2, NULL, 'Y2K themed party? Count me in! Time to dig out my old outfits!', '2024-11-10 16:45:00'),
(2, 5, 4, 'This is going to be so nostalgic! Anyone remember frosted tips?', '2024-11-18 11:20:00'),

(3, 3, 5, 'Best way to start 2025! Already got my tickets!', '2024-11-05 10:30:00'),
(3, 4, NULL, 'The fireworks display is supposed to be amazing. So hyped!', '2024-11-28 13:00:00'),

(4, 5, 5, 'This was the best event I have ever been to! Hope they do it again!', '2023-12-21 08:00:00');

-- ============================================================================
-- BOOKMARKS
-- ============================================================================

-- Users bookmarking events
INSERT INTO bookmarks (user_id, event_id, created_at) VALUES
(2, 1, NOW()),
(2, 2, NOW()),
(2, 3, NOW()),
(3, 1, NOW()),
(3, 3, NOW()),
(4, 1, NOW()),
(4, 2, NOW()),
(4, 3, NOW()),
(5, 2, NOW()),
(5, 3, NOW());

-- ============================================================================
-- TICKET PURCHASES (for sample transaction data)
-- ============================================================================

-- Sample purchases for active events
INSERT INTO ticket_purchases (ticket_id, user_id, event_id, quantity, unit_price, total_amount, payment_status, payment_method, transaction_id, purchased_at) VALUES
-- Ashchella purchases
(1, 2, 1, 2, 40.00, 80.00, 'completed', 'mobile_money', 'TXN001', '2024-11-01 10:30:00'),
(2, 3, 1, 1, 50.00, 50.00, 'completed', 'credit_card', 'TXN002', '2024-11-05 14:20:00'),
(3, 4, 1, 2, 80.00, 160.00, 'completed', 'mobile_money', 'TXN003', '2024-11-10 09:15:00'),

-- Y2K Neon Party purchases
(4, 2, 2, 1, 50.00, 50.00, 'completed', 'mobile_money', 'TXN004', '2024-11-15 16:45:00'),
(5, 5, 2, 1, 100.00, 100.00, 'completed', 'credit_card', 'TXN005', '2024-11-18 11:30:00'),

-- New Year Bash purchases
(6, 2, 3, 2, 60.00, 120.00, 'completed', 'mobile_money', 'TXN006', '2024-11-20 13:00:00'),
(7, 3, 3, 1, 120.00, 120.00, 'completed', 'credit_card', 'TXN007', '2024-11-22 10:45:00'),
(8, 4, 3, 1, 250.00, 250.00, 'completed', 'mobile_money', 'TXN008', '2024-11-25 15:20:00');

-- ============================================================================
-- END OF SEED DATA
-- ============================================================================

-- Display summary
SELECT 'Sample data inserted successfully!' as status;
SELECT COUNT(*) as total_users FROM users;
SELECT COUNT(*) as total_events FROM events;
SELECT COUNT(*) as total_tickets FROM tickets;
SELECT COUNT(*) as total_reviews FROM reviews;
SELECT COUNT(*) as total_comments FROM comments;
