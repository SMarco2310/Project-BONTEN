-- ========================================
-- BONTEN Database Population Script
-- Creates manager account and populates events
-- ========================================

-- Insert Manager Account (Eldad Opare)
-- Note: Password needs to be hashed using PHP password_hash('23May@2005', PASSWORD_DEFAULT)
INSERT INTO users (email, password, username, full_name, phone, profile_picture, user_type, created_at)
VALUES (
    'eldad.opare@bonten.com',
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',
    'eldadopare',
    'Eldad Opare',
    '+233244567890',
    'user.jpg',
    'manager',
    NOW()
);

-- Get the manager_id
SET @manager_id = LAST_INSERT_ID();

-- ========================================
-- TRENDING EVENTS (Concerts)
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
    'East Legon',
    'in-person',
    15000,
    'active',
    'a.png',
    NOW()
);

-- 2. Sankrofi
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Sankrofi',
    'An intimate live music experience featuring Ghana\'s beloved highlife band Sankrofi. Experience authentic Ghanaian highlife rhythms and contemporary sounds in a vibrant atmosphere celebrating local musical heritage.',
    '2025-12-15',
    '19:00:00',
    'Alliance Française',
    'Ashaley Botwe',
    'in-person',
    2000,
    'active',
    'sank.jpg',
    NOW()
);

-- 3. Phoenix
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

-- 4. Band Out!
INSERT INTO events (manager_id, category_id, name, description, event_date, event_time, location, city, event_type, capacity, status, image_path, created_at)
VALUES (
    @manager_id,
    (SELECT category_id FROM categories WHERE name = 'Concert'),
    'Band Out!',
    'A celebration of live band music featuring talented musicians performing across multiple genres. Get ready for an energetic night of authentic instrumentation, powerful vocals, and unforgettable melodies.',
    '2025-12-18',
    '19:30:00',
    'The Gardens',
    'Oyarifa',
    'in-person',
    1500,
    'active',
    'bO.jpg',
    NOW()
);

-- 5. Osibisa
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

-- ========================================
-- EVENTS AROUND YOU
-- ========================================

-- 6. TGMA (Telecel Ghana Music Awards)
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

-- 7. Waakye Festival
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
    'waakye.JPG',
    NOW()
);

-- 8. Ghana World Cup Qualifiers
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

-- 9. NSMQ (National Science & Maths Quiz)
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

-- 10. Beehive Festival
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

-- ========================================
-- EVENTS SUGGESTED FOR YOU
-- ========================================

-- 11. Ashchella
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
    'ashchella.jpg',
    NOW()
);

-- 12. Y2K Neon
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

-- 13. Tidal Rave
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

-- 14. GFF (Ghana Food Festival)
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

-- 15. iMullar
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

-- ========================================
-- HOMEPAGE FEATURED EVENTS
-- ========================================

-- 16. Detty December
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

-- 17. Rapperholic
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

-- ========================================
-- ADD SAMPLE TICKETS FOR EACH EVENT
-- ========================================

-- Add Regular tickets for all events
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
        ELSE 30.00
    END,
    CASE
        WHEN name LIKE '%World Cup%' THEN 30000
        WHEN capacity > 10000 THEN FLOOR(capacity * 0.6)
        ELSE FLOOR(capacity * 0.7)
    END,
    FLOOR(RAND() * 100)
FROM events;

-- Add VIP tickets for all events (except free events)
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
        ELSE 100.00
    END,
    CASE
        WHEN name LIKE '%World Cup%' THEN 10000
        WHEN capacity > 10000 THEN FLOOR(capacity * 0.4)
        ELSE FLOOR(capacity * 0.3)
    END,
    FLOOR(RAND() * 50)
FROM events
WHERE name NOT LIKE '%NSMQ%' AND name NOT LIKE '%Waakye%';
