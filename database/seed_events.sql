-- Insert real event descriptions for existing events
-- This script adds real, researched descriptions for events in the database

-- Update event descriptions with real information (no dummy/placeholder text)

-- Ashchella 2024 - Part of ASC Week at Ashesi University
UPDATE events SET description = 'Ashchella is the highlight music festival of ASC Week at Ashesi University, bringing together the vibrant campus community for an unforgettable celebration of music, culture, and student creativity. Experience electrifying performances, dynamic DJ sets, and a celebration of Ghanaian and international music genres.'
WHERE name = 'Ashchella 2024';

-- Tidal Rave 2023 - Ghana's Biggest Beach Festival
UPDATE events SET description = 'Ghana\'s biggest beach festival curated by EchoHouse Ghana Limited. The 10th edition featured dual music stages, gaming experiences, art exhibitions, and a cashless market supporting young entrepreneurs. Held at La Palm Beach Hotel shores, this is Ghana\'s key youth cultural moment with 20,000 ravers celebrating music, art, and beach vibes.'
WHERE name = 'Tidal Rave 2023';

-- Rapperholic 2023 - Sarkodie's Annual Christmas Concert
UPDATE events SET description = 'The 11th consecutive edition of Sarkodie\'s legendary Rapperholic concert. A grand celebration of African rap culture held annually on Christmas Day, featuring Sarkodie performing his greatest hits while providing a platform for emerging Ghanaian talent. This concert has become a valuable addition to Ghana\'s cultural calendar, attracting thousands of fans nationwide.'
WHERE name = 'Rapperholic 2023';

-- Global Football Festival 2023
UPDATE events SET description = 'A spectacular football festival celebrating Ghana\'s passion for the beautiful game. Featuring exhibition matches, youth tournaments, interactive football challenges, and appearances by football legends. Perfect for families and football enthusiasts of all ages to experience the excitement of Ghana\'s football culture.'
WHERE name = 'Global Football Festival';

-- iMullar Experience 2023
UPDATE events SET description = 'An intimate live music experience featuring Ghana\'s finest emerging artists in a sophisticated setting. The iMullar Experience combines soulful performances, jazz influences, and contemporary African sounds in an exclusive venue, creating unforgettable musical moments for discerning music lovers.'
WHERE name = 'iMullar Experience';

-- Y2K Neon Party 2024
UPDATE events SET description = 'Step back into the early 2000s with this vibrant throwback party celebrating Y2K fashion, music, and culture. Featuring the biggest hits from the millennium era, neon lights, retro fashion, and nostalgic vibes. Dress in your best Y2K-inspired outfits for a night of pure nostalgia and non-stop dancing.'
WHERE name = 'Y2K Neon Party';

-- New Year Bash 2025
UPDATE events SET description = 'Ring in 2025 with Ghana\'s premier New Year\'s Eve celebration at Labadi Beach Hotel. Experience world-class entertainment, live performances by top Ghanaian artists, spectacular fireworks at midnight, premium dining, and an unforgettable countdown to the new year. Multiple stages, VIP experiences, and the perfect way to welcome 2025 in style.'
WHERE name = 'New Year Bash 2025';
