-- ==========================================================
-- Database & Table Schema: Suchinta's Guardian Angel
-- ==========================================================

-- 1. Create database if it does not already exist
CREATE DATABASE IF NOT EXISTS `guardian_angel_db`
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE `guardian_angel_db`;

-- 2. Create advices table
CREATE TABLE IF NOT EXISTS `advices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Seed initial uplifting and caring advice pieces
INSERT INTO `advices` (`content`) VALUES
    ('Remember to take a sip of water and unclench your jaw right now. Yes, I see you! Stay hydrated, champion.'),
    ('Take a deep breath and give yourself some credit. You are handling so much right now and doing it with real grace.'),
    ('Don\'t let a tiny bump spoil your entire day. Step back, stretch, and tackle things just one small piece at a time.'),
    ('It is 100% okay to hit pause and recharge. Even legendary superheroes take naps, and you deserve your cozy downtime.'),
    ('Trust your gut and your abilities today. You\'ve got sharp brains, a kind heart, and the grit to figure anything out.'),
    ('Quick reminder: perfection is wildly overrated. Being real, kind, and patient with yourself is what truly counts.'),
    ('If today feels a little heavy, remember that tough days only make the upcoming victories sweeter. You\'ve got this!'),
    ('Make time to eat something delicious and nourishing today. Good food fuels good vibes, and your energy is precious.'),
    ('Look at how far you\'ve already come! Never look back to doubt your worth—only to celebrate your progress.'),
    ('Sending you an extra boost of guardian angel positive energy today. Go out there and just be your awesome self!');

-- 4. Create pro_advices table (Longer paragraphs, 30% appearance chance)
CREATE TABLE IF NOT EXISTS `pro_advices` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `content` TEXT NOT NULL,
    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

INSERT INTO `pro_advices` (`content`) VALUES
    ('Dearest Suchinta, whenever you feel tired or weighed down by the world, remember that you are capable of extraordinary things. You do not have to carry every worry at once. Take a slow deep breath, relax your shoulders, and trust that every step you take is leading you to something beautiful. Your smile brings light to everyone around you, and you are deeply loved, cherished, and protected every single day.');

