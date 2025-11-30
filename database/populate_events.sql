-- ========================================
-- BONTEN Database Population Script
-- Creates manager account, populates events, tickets, bookings, RSVPs, reviews, and comments
-- ========================================



-- ========================================
-- INSERT MANAGER ACCOUNT
-- ========================================
INSERT INTO users (email, password, username, full_name, phone, profile_picture, user_type, created_at)
VALUES (
    'eldad.opare@bonten.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', -- password: 23May@2005
    'eldadopare',
    'Eldad Opare',
    '+233244567890',
    'eldad.jpg',
    'manager',
    NOW()
);

SET @manager_id = LAST_INSERT_ID();

-- ========================================
-- INSERT SAMPLE USERS (for testing history, reviews, comments)
-- ========================================
INSERT INTO users (email, password, username, full_name, phone, profile_picture, user_type, created_at) VALUES
('user1@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1', 'John Doe', '+233241111111', 'user.jpg', 'user', DATE_SUB(NOW(), INTERVAL 30 DAY)),
('user2@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2', 'Jane Smith', '+233242222222', 'user.jpg', 'user', DATE_SUB(NOW(), INTERVAL 25 DAY)),
('user3@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user3', 'Kwame Asante', '+233243333333', 'user.jpg', 'user', DATE_SUB(NOW(), INTERVAL 20 DAY)),
('user4@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user4', 'Ama Mensah', '+233244444444', 'user.jpg', 'user', DATE_SUB(NOW(), INTERVAL 15 DAY)),
('user5@example.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user5', 'Kofi Boateng', '+233245555555', 'user.jpg', 'user', DATE_SUB(NOW(), INTERVAL 10 DAY));

SET @user1_id = (SELECT user_id FROM users WHERE email = 'user1@example.com');
SET @user2_id = (SELECT user_id FROM users WHERE email = 'user2@example.com');
SET @user3_id = (SELECT user_id FROM users WHERE email = 'user3@example.com');
SET @user4_id = (SELECT user_id FROM users WHERE email = 'user4@example.com');
SET @user5_id = (SELECT user_id FROM users WHERE email = 'user5@example.com');

-- ========================================
-- HOT EVENTS (Most Popular - High ticket sales)
-- ========================================

-- 1. Afro Nation
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Afro Nation',
    'Africa\'s biggest beach festival celebrating Afrobeats, dancehall, hip-hop, and R&B. A three-day cultural extravaganza uniting the African diaspora through music, dance, food, and fashion with world-class performances from top African and international artists.',
    '2025-12-27',
    '18:00:00',
    'Labadi Beach',
    'Accra',
    'in-person',
    15000,
    'active',
    'a.png',
    NOW()
);
SET @afro_nation_id = LAST_INSERT_ID();

-- 2. Rapperholic
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Rapperholic',
    'Sarkodie\'s flagship annual hip-hop concert featuring Ghana\'s biggest rap and music stars. Experience an electrifying night of performances, surprise guests, and unforgettable moments celebrating African hip-hop culture.',
    '2025-12-26',
    '19:00:00',
    'Grand Arena',
    'Accra',
    'in-person',
    12000,
    'active',
    'rapperholic.jpeg',
    NOW()
);
SET @rapperholic_id = LAST_INSERT_ID();

-- 3. Detty December
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Festival'),
    'Detty December',
    'Ghana\'s biggest holiday celebration featuring music, parties, and cultural festivities throughout December. A month-long extravaganza of concerts, beach parties, and cultural events celebrating African diaspora homecoming.',
    '2025-12-01',
    '18:00:00',
    'Various Locations',
    'Accra',
    'hybrid',
    50000,
    'active',
    'detty.webp',
    NOW()
);
SET @detty_id = LAST_INSERT_ID();

-- ========================================
-- TRENDING EVENTS (Concerts)
-- ========================================

-- 4. Sankrofi
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Sankrofi',
    'An intimate live music experience featuring Ghana\'s beloved highlife band Sankrofi. Experience authentic Ghanaian highlife rhythms and contemporary sounds in a vibrant atmosphere celebrating local musical heritage.',
    '2025-12-15',
    '19:00:00',
    'Alliance Française',
    'Accra',
    'in-person',
    2000,
    'active',
    'sank.jpg',
    NOW()
);
SET @sankrofi_id = LAST_INSERT_ID();

-- 5. Phoenix
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Phoenix',
    'A dynamic music concert bringing together emerging and established artists for an unforgettable night of live performances. Experience diverse musical genres in Kumasi\'s vibrant entertainment scene.',
    '2025-12-20',
    '20:00:00',
    'Kumasi City Mall',
    'Kumasi',
    'in-person',
    5000,
    'active',
    'phoenix.avif',
    NOW()
);
SET @phoenix_id = LAST_INSERT_ID();

-- 6. Band Out!
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Band Out!',
    'A celebration of live band music featuring talented musicians performing across multiple genres. Get ready for an energetic night of authentic instrumentation, powerful vocals, and unforgettable melodies.',
    '2025-12-18',
    '19:30:00',
    'The Gardens',
    'Accra',
    'in-person',
    1500,
    'active',
    'bO.jpg',
    NOW()
);
SET @bandout_id = LAST_INSERT_ID();

-- 7. Osibisa
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Osibisa',
    'Legendary Afro-rock pioneers Osibisa bring their signature fusion of African rhythms, rock, and jazz to an intimate jazz bar setting. Experience the iconic sounds that defined a generation in this exclusive performance.',
    '2025-12-22',
    '21:00:00',
    'Jazz Bar & Grill',
    'Accra',
    'in-person',
    500,
    'active',
    'osibisa.avif',
    NOW()
);
SET @osibisa_id = LAST_INSERT_ID();

-- ========================================
-- EVENTS AROUND YOU
-- ========================================

-- 8. TGMA (Telecel Ghana Music Awards)
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Other'),
    'TGMA',
    'Ghana\'s premier music awards ceremony celebrating excellence in the music industry. Witness electrifying performances, prestigious awards, and memorable moments honoring the nation\'s finest musical talents.',
    '2026-06-15',
    '19:00:00',
    'AICC',
    'Accra',
    'in-person',
    8000,
    'active',
    'tgma.jpg',
    NOW()
);
SET @tgma_id = LAST_INSERT_ID();

-- 9. Waakye Festival
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Food & Drinks'),
    'Waakye Festival',
    'A culinary celebration dedicated to Ghana\'s beloved waakye dish. Taste variations from different regions, watch cooking demonstrations, and enjoy live music while celebrating this iconic Ghanaian street food.',
    '2025-12-14',
    '10:00:00',
    'Adenta Town Park',
    'Adenta',
    'in-person',
    3000,
    'active',
    'waakye.jpg',
    NOW()
);
SET @waakye_id = LAST_INSERT_ID();

-- 10. Ghana World Cup Qualifiers
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Sports'),
    'Ghana World Cup Qualifiers',
    'Support the Black Stars as they battle for World Cup qualification! Experience the electric atmosphere as Ghana\'s national football team fights for glory in this crucial qualifying match.',
    '2026-03-28',
    '18:00:00',
    'Accra Sports Stadium',
    'Accra',
    'in-person',
    40000,
    'active',
    'gh.jpg',
    NOW()
);
SET @worldcup_id = LAST_INSERT_ID();

-- 11. NSMQ (National Science & Maths Quiz)
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Other'),
    'NSMQ',
    'Ghana\'s most prestigious academic competition where brilliant senior high school students compete in science and mathematics. Witness intense intellectual battles and celebrate academic excellence.',
    '2025-11-25',
    '14:00:00',
    'AICC',
    'Accra',
    'in-person',
    5000,
    'active',
    'nsmq.jpg',
    NOW()
);
SET @nsmq_id = LAST_INSERT_ID();

-- 12. Beehive Festival
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Festival'),
    'Beehive Festival',
    'An immersive music and arts festival featuring multiple stages, art installations, and diverse performances. Experience the buzz of creativity with emerging and established artists in an intimate outdoor setting.',
    '2025-12-16',
    '15:00:00',
    'Bloombar Gardens',
    'Accra',
    'in-person',
    2500,
    'active',
    'beehive.webp',
    NOW()
);
SET @beehive_id = LAST_INSERT_ID();

-- ========================================
-- EVENTS SUGGESTED FOR YOU
-- ========================================

-- 13. Ashchella
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Festival'),
    'Ashchella',
    'A vibrant celebration of music, art, fashion, and African culture at Ashesi University. This student-led festival showcases emerging talent, creative expression, and cultural pride through performances and exhibitions.',
    '2025-12-25',
    '17:00:00',
    'Ashesi University',
    'Berekuso',
    'in-person',
    3000,
    'active',
    'ashchella.JPG',
    NOW()
);
SET @ashchella_id = LAST_INSERT_ID();

-- 14. Y2K Neon
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Party'),
    'Y2K Neon',
    'A nostalgic throwback party celebrating early 2000s culture with neon vibes and retro music. Dress in your best Y2K fashion and dance to hits from the millennium era at this electric beach celebration.',
    '2025-12-28',
    '21:00:00',
    'Lemon Beach Resort',
    'Accra',
    'in-person',
    1200,
    'active',
    'y2k.JPG',
    NOW()
);
SET @y2k_id = LAST_INSERT_ID();

-- 15. Tidal Rave
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Festival'),
    'Tidal Rave',
    'Ghana\'s biggest beach festival featuring Afrobeats, hip-hop, and dancehall with multiple experience zones. Dance on the sand to top DJs and live performers in this epic seaside celebration.',
    '2025-12-20',
    '20:00:00',
    'Labadi Beach',
    'Accra',
    'in-person',
    10000,
    'active',
    'tidalrave.jpg',
    NOW()
);
SET @tidalrave_id = LAST_INSERT_ID();

-- 16. GFF (Ghana Food Festival)
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Food & Drinks'),
    'GFF',
    'Ghana\'s biggest food carnival celebrating culinary culture with dishes from all 16 regions. Experience authentic Ghanaian flavors, cooking demonstrations, and cultural performances in this gastronomic journey.',
    '2025-12-19',
    '11:00:00',
    'Accra Mall Forecourt',
    'Accra',
    'in-person',
    5000,
    'active',
    'gff.jpg',
    NOW()
);
SET @gff_id = LAST_INSERT_ID();

-- 17. iMullar
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'iMullar',
    'An exclusive music concert experience featuring top-tier performances and entertainment. Enjoy an intimate evening with exceptional artists delivering unforgettable live music in a sophisticated jazz club atmosphere.',
    '2025-12-17',
    '20:30:00',
    'Jazz Bar & Grill',
    'Accra',
    'in-person',
    600,
    'active',
    'imullar.jpg',
    NOW()
);
SET @imullar_id = LAST_INSERT_ID();

-- 18. Global Football Festival
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Sports'),
    'Global Football Festival',
    'A celebration of football culture featuring exhibition matches, skills competitions, and meet-and-greets with football legends. Experience the beautiful game through tournaments, workshops, and family-friendly activities.',
    '2025-12-01',
    '14:00:00',
    'El Wak Stadium',
    'Accra',
    'in-person',
    8000,
    'active',
    'global.jpg',
    NOW()
);
SET @global_id = LAST_INSERT_ID();

-- 19. Tanks & Bikinis
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Party'),
    'Tanks & Bikinis',
    'The ultimate beach party experience with fashion, music, and summer vibes. Show off your best beach attire while dancing to Afrobeats and international hits in this stylish seaside celebration.',
    '2025-12-15',
    '19:00:00',
    'Kokrobite Beach',
    'Accra',
    'in-person',
    3500,
    'active',
    't&b.jpg',
    NOW()
);
SET @tanks_id = LAST_INSERT_ID();

-- ========================================
-- PAST EVENTS (for history functionality)
-- ========================================

-- 20. Past Event 1 - Completed Concert
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Summer Music Fest 2024',
    'An amazing summer music festival that brought together top artists from across Africa. Featured incredible performances, food vendors, and an unforgettable atmosphere.',
    DATE_SUB(CURDATE(), INTERVAL 60 DAY),
    '18:00:00',
    'Independence Square',
    'Accra',
    'in-person',
    8000,
    'completed',
    'event.jpeg',
    DATE_SUB(NOW(), INTERVAL 90 DAY)
);
SET @past_event1_id = LAST_INSERT_ID();

-- 21. Past Event 2 - Completed Festival
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Festival'),
    'Ghana Culture Week 2024',
    'A week-long celebration of Ghanaian culture featuring traditional music, dance, food, and art exhibitions. Showcased the rich heritage of Ghana.',
    DATE_SUB(CURDATE(), INTERVAL 45 DAY),
    '10:00:00',
    'National Theatre',
    'Accra',
    'in-person',
    5000,
    'completed',
    'pastevents.jpeg',
    DATE_SUB(NOW(), INTERVAL 75 DAY)
);
SET @past_event2_id = LAST_INSERT_ID();

-- 22. Past Event 3 - Completed Sports Event
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Sports'),
    'Ghana Premier League Final',
    'The thrilling finale of the Ghana Premier League season. Witnessed an intense match between the top two teams with amazing goals and passionate fans.',
    DATE_SUB(CURDATE(), INTERVAL 30 DAY),
    '15:00:00',
    'Accra Sports Stadium',
    'Accra',
    'in-person',
    25000,
    'completed',
    'gh.jpg',
    DATE_SUB(NOW(), INTERVAL 60 DAY)
);
SET @past_event3_id = LAST_INSERT_ID();

-- ========================================
-- ADD TICKETS FOR ALL EVENTS
-- ========================================

-- Regular tickets for all events
INSERT INTO tickets (event_id, ticket_name, price, quantity, sold)
SELECT
    event_id,
    'Regular',
    CASE
        WHEN name LIKE '%World Cup%' THEN 50.00
        WHEN name LIKE '%TGMA%' THEN 150.00
        WHEN name LIKE '%Afro Nation%' OR name LIKE '%Detty December%' THEN 200.00
        WHEN name LIKE '%Rapperholic%' THEN 120.00
        WHEN name LIKE '%Tidal Rave%' THEN 80.00
        WHEN name LIKE '%Festival%' THEN 50.00
        WHEN name LIKE '%NSMQ%' THEN 0.00
        WHEN status = 'completed' THEN 40.00
        ELSE 30.00
    END,
    CASE
        WHEN name LIKE '%World Cup%' THEN 30000
        WHEN capacity > 10000 THEN FLOOR(capacity * 0.6)
        ELSE FLOOR(capacity * 0.7)
    END,
    CASE
        WHEN status = 'completed' THEN FLOOR(capacity * 0.6 * 0.8) -- 80% sold for past events
        ELSE FLOOR(RAND() * 100)
    END
FROM events;

-- VIP tickets for all events (except free events)
INSERT INTO tickets (event_id, ticket_name, price, quantity, sold)
SELECT
    event_id,
    'VIP',
    CASE
        WHEN name LIKE '%World Cup%' THEN 150.00
        WHEN name LIKE '%TGMA%' THEN 500.00
        WHEN name LIKE '%Afro Nation%' OR name LIKE '%Detty December%' THEN 600.00
        WHEN name LIKE '%Rapperholic%' THEN 350.00
        WHEN name LIKE '%Tidal Rave%' THEN 250.00
        WHEN name LIKE '%Festival%' THEN 150.00
        WHEN status = 'completed' THEN 120.00
        ELSE 100.00
    END,
    CASE
        WHEN name LIKE '%World Cup%' THEN 10000
        WHEN capacity > 10000 THEN FLOOR(capacity * 0.4)
        ELSE FLOOR(capacity * 0.3)
    END,
    CASE
        WHEN status = 'completed' THEN FLOOR(capacity * 0.3 * 0.75) -- 75% sold for past events
        ELSE FLOOR(RAND() * 50)
    END
FROM events
WHERE name NOT LIKE '%NSMQ%' AND name NOT LIKE '%Waakye%';

-- ========================================
-- CREATE RSVPs FOR USERS (Past and Upcoming Events)
-- ========================================

-- User 1: RSVPs to past events (for history)
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@past_event1_id, @user1_id, 1, DATE_SUB(NOW(), INTERVAL 65 DAY)),
(@past_event2_id, @user1_id, 1, DATE_SUB(NOW(), INTERVAL 50 DAY)),
(@past_event3_id, @user1_id, 1, DATE_SUB(NOW(), INTERVAL 35 DAY));

-- User 1: RSVPs to upcoming events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@afro_nation_id, @user1_id, 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(@rapperholic_id, @user1_id, 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(@sankrofi_id, @user1_id, 0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- User 2: RSVPs to past events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@past_event1_id, @user2_id, 1, DATE_SUB(NOW(), INTERVAL 63 DAY)),
(@past_event3_id, @user2_id, 1, DATE_SUB(NOW(), INTERVAL 32 DAY));

-- User 2: RSVPs to upcoming events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@detty_id, @user2_id, 0, DATE_SUB(NOW(), INTERVAL 7 DAY)),
(@tidalrave_id, @user2_id, 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(@ashchella_id, @user2_id, 0, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- User 3: RSVPs to past events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@past_event2_id, @user3_id, 1, DATE_SUB(NOW(), INTERVAL 48 DAY)),
(@past_event3_id, @user3_id, 1, DATE_SUB(NOW(), INTERVAL 33 DAY));

-- User 3: RSVPs to upcoming events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@phoenix_id, @user3_id, 0, DATE_SUB(NOW(), INTERVAL 6 DAY)),
(@beehive_id, @user3_id, 0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- User 4: RSVPs to past events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@past_event1_id, @user4_id, 1, DATE_SUB(NOW(), INTERVAL 61 DAY)),
(@past_event2_id, @user4_id, 1, DATE_SUB(NOW(), INTERVAL 46 DAY));

-- User 4: RSVPs to upcoming events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@gff_id, @user4_id, 0, DATE_SUB(NOW(), INTERVAL 5 DAY)),
(@y2k_id, @user4_id, 0, DATE_SUB(NOW(), INTERVAL 3 DAY)),
(@imullar_id, @user4_id, 0, DATE_SUB(NOW(), INTERVAL 1 DAY));

-- User 5: RSVPs to past events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@past_event1_id, @user5_id, 1, DATE_SUB(NOW(), INTERVAL 62 DAY)),
(@past_event2_id, @user5_id, 1, DATE_SUB(NOW(), INTERVAL 47 DAY)),
(@past_event3_id, @user5_id, 1, DATE_SUB(NOW(), INTERVAL 31 DAY));

-- User 5: RSVPs to upcoming events
INSERT INTO rsvps (event_id, user_id, attended, created_at) VALUES
(@worldcup_id, @user5_id, 0, DATE_SUB(NOW(), INTERVAL 8 DAY)),
(@tgma_id, @user5_id, 0, DATE_SUB(NOW(), INTERVAL 4 DAY)),
(@bandout_id, @user5_id, 0, DATE_SUB(NOW(), INTERVAL 2 DAY));

-- ========================================
-- ADD REVIEWS FOR PAST EVENTS
-- ========================================

-- Reviews for Past Event 1
INSERT INTO reviews (event_id, user_id, rating, review_text, created_at) VALUES
(@past_event1_id, @user1_id, 5, 'Amazing event! The performances were incredible and the atmosphere was electric. Definitely coming back next year!', DATE_SUB(NOW(), INTERVAL 58 DAY)),
(@past_event1_id, @user2_id, 4, 'Great music festival. The sound quality was excellent and the lineup was impressive. Only downside was the long queues for food.', DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@past_event1_id, @user4_id, 5, 'Best summer festival I\'ve been to! The organization was top-notch and the artists delivered amazing performances.', DATE_SUB(NOW(), INTERVAL 55 DAY)),
(@past_event1_id, @user5_id, 4, 'Really enjoyed the event. Great vibes and good music. Would recommend to anyone looking for a fun time.', DATE_SUB(NOW(), INTERVAL 54 DAY));

-- Reviews for Past Event 2
INSERT INTO reviews (event_id, user_id, rating, review_text, created_at) VALUES
(@past_event2_id, @user1_id, 5, 'Beautiful celebration of Ghanaian culture! Learned so much and enjoyed every moment. The traditional performances were breathtaking.', DATE_SUB(NOW(), INTERVAL 43 DAY)),
(@past_event2_id, @user3_id, 4, 'Wonderful cultural experience. The food was authentic and the performances were engaging. Great for families!', DATE_SUB(NOW(), INTERVAL 42 DAY)),
(@past_event2_id, @user4_id, 5, 'Absolutely loved it! The cultural displays were informative and the atmosphere was welcoming. Can\'t wait for next year!', DATE_SUB(NOW(), INTERVAL 41 DAY)),
(@past_event2_id, @user5_id, 4, 'Great event showcasing our rich culture. The organizers did a fantastic job. Highly recommend!', DATE_SUB(NOW(), INTERVAL 40 DAY));

-- Reviews for Past Event 3
INSERT INTO reviews (event_id, user_id, rating, review_text, created_at) VALUES
(@past_event3_id, @user1_id, 5, 'Incredible match! The energy in the stadium was unmatched. The Black Stars played brilliantly and the fans were amazing.', DATE_SUB(NOW(), INTERVAL 28 DAY)),
(@past_event3_id, @user2_id, 4, 'Great football match. The atmosphere was electric and the game was intense. Worth every cedi!', DATE_SUB(NOW(), INTERVAL 27 DAY)),
(@past_event3_id, @user3_id, 5, 'Best football experience ever! The stadium was packed and the energy was through the roof. Unforgettable!', DATE_SUB(NOW(), INTERVAL 26 DAY)),
(@past_event3_id, @user5_id, 5, 'Amazing game! The Black Stars showed their class. The whole experience was fantastic from start to finish.', DATE_SUB(NOW(), INTERVAL 25 DAY));

-- ========================================
-- ADD COMMENTS FOR PAST EVENTS
-- ========================================

-- Comments for Past Event 1
INSERT INTO comments (event_id, user_id, comment_text, created_at) VALUES
(@past_event1_id, @user1_id, 'Who else is still replaying that performance in their head? 🔥', DATE_SUB(NOW(), INTERVAL 59 DAY)),
(@past_event1_id, @user2_id, 'The sound system was incredible! Best I\'ve heard at a festival.', DATE_SUB(NOW(), INTERVAL 57 DAY)),
(@past_event1_id, @user4_id, 'Already counting down to next year! This was epic!', DATE_SUB(NOW(), INTERVAL 56 DAY)),
(@past_event1_id, @user5_id, 'The energy was unmatched! Great event overall.', DATE_SUB(NOW(), INTERVAL 55 DAY));

-- Comments for Past Event 2
INSERT INTO comments (event_id, user_id, comment_text, created_at) VALUES
(@past_event2_id, @user1_id, 'Such a beautiful celebration of our culture. Proud to be Ghanaian! 🇬🇭', DATE_SUB(NOW(), INTERVAL 44 DAY)),
(@past_event2_id, @user3_id, 'The traditional dance performances were mesmerizing!', DATE_SUB(NOW(), INTERVAL 43 DAY)),
(@past_event2_id, @user4_id, 'Brought my kids and they loved it! Educational and fun.', DATE_SUB(NOW(), INTERVAL 42 DAY)),
(@past_event2_id, @user5_id, 'Great initiative to preserve and showcase our heritage.', DATE_SUB(NOW(), INTERVAL 41 DAY));

-- Comments for Past Event 3
INSERT INTO comments (event_id, user_id, comment_text, created_at) VALUES
(@past_event3_id, @user1_id, 'That goal in the 89th minute! Still gives me chills! ⚽', DATE_SUB(NOW(), INTERVAL 29 DAY)),
(@past_event3_id, @user2_id, 'The stadium was rocking! Best atmosphere I\'ve experienced.', DATE_SUB(NOW(), INTERVAL 28 DAY)),
(@past_event3_id, @user3_id, 'Black Stars forever! What a performance!', DATE_SUB(NOW(), INTERVAL 27 DAY)),
(@past_event3_id, @user5_id, 'Unforgettable night! The team made us proud!', DATE_SUB(NOW(), INTERVAL 26 DAY));

-- ========================================
-- ADD SAMPLE BOOKINGS (for payment testing)
-- ========================================

-- Get ticket IDs for bookings
SET @past_event1_regular_ticket = (SELECT ticket_id FROM tickets WHERE event_id = @past_event1_id AND ticket_name = 'Regular' LIMIT 1);
SET @past_event1_vip_ticket = (SELECT ticket_id FROM tickets WHERE event_id = @past_event1_id AND ticket_name = 'VIP' LIMIT 1);
SET @past_event2_regular_ticket = (SELECT ticket_id FROM tickets WHERE event_id = @past_event2_id AND ticket_name = 'Regular' LIMIT 1);
SET @past_event3_regular_ticket = (SELECT ticket_id FROM tickets WHERE event_id = @past_event3_id AND ticket_name = 'Regular' LIMIT 1);

-- Sample successful bookings for past events
INSERT INTO bookings (reference, email, event_id, ticket_type, quantity, amount, status, created_at) VALUES
('BONTEN_REF_001', 'user1@example.com', @past_event1_id, 'Regular', 2, 80.00, 'successful', DATE_SUB(NOW(), INTERVAL 65 DAY)),
('BONTEN_REF_002', 'user1@example.com', @past_event1_id, 'VIP', 1, 120.00, 'successful', DATE_SUB(NOW(), INTERVAL 64 DAY)),
('BONTEN_REF_003', 'user2@example.com', @past_event1_id, 'Regular', 3, 120.00, 'successful', DATE_SUB(NOW(), INTERVAL 63 DAY)),
('BONTEN_REF_004', 'user1@example.com', @past_event2_id, 'Regular', 2, 100.00, 'successful', DATE_SUB(NOW(), INTERVAL 50 DAY)),
('BONTEN_REF_005', 'user3@example.com', @past_event2_id, 'Regular', 1, 50.00, 'successful', DATE_SUB(NOW(), INTERVAL 48 DAY)),
('BONTEN_REF_006', 'user1@example.com', @past_event3_id, 'Regular', 4, 200.00, 'successful', DATE_SUB(NOW(), INTERVAL 35 DAY)),
('BONTEN_REF_007', 'user2@example.com', @past_event3_id, 'Regular', 2, 100.00, 'successful', DATE_SUB(NOW(), INTERVAL 32 DAY)),
('BONTEN_REF_008', 'user3@example.com', @past_event3_id, 'Regular', 1, 50.00, 'successful', DATE_SUB(NOW(), INTERVAL 33 DAY)),
('BONTEN_REF_009', 'user5@example.com', @past_event3_id, 'Regular', 3, 150.00, 'successful', DATE_SUB(NOW(), INTERVAL 31 DAY));

-- Sample pending bookings for upcoming events
INSERT INTO bookings (reference, email, event_id, ticket_type, quantity, amount, status, created_at) VALUES
('BONTEN_REF_010', 'user1@example.com', @afro_nation_id, 'Regular', 2, 400.00, 'pending', DATE_SUB(NOW(), INTERVAL 5 DAY)),
('BONTEN_REF_011', 'user1@example.com', @rapperholic_id, 'VIP', 1, 350.00, 'pending', DATE_SUB(NOW(), INTERVAL 3 DAY)),
('BONTEN_REF_012', 'user2@example.com', @tidalrave_id, 'Regular', 2, 160.00, 'pending', DATE_SUB(NOW(), INTERVAL 4 DAY));

-- ========================================
-- SUMMARY
-- ========================================
-- This script creates:
-- - 1 Manager account
-- - 5 Sample users
-- - 22 Events (3 Hot, 4 Trending, 5 Around You, 5 Suggested, 3 Past, 2 Additional)
-- - Tickets for all events (Regular and VIP)
-- - RSVPs for users (past and upcoming events)
-- - Reviews for past events
-- - Comments for past events
-- - Sample bookings (successful and pending)
