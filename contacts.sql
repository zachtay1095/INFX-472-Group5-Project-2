CREATE DATABASE IF NOT EXISTS `addressbook`
    DEFAULT CHARACTER SET utf8mb4
    DEFAULT COLLATE utf8mb4_unicode_ci;

-- Select the addressbook database
USE `addressbook`;

-- Drop existing table if needed (optional safety for re-runs)
-- DROP TABLE IF EXISTS `contacts`;

-- Create the contacts table
CREATE TABLE IF NOT EXISTS `contacts` (
    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `first_name` VARCHAR(50) NOT NULL,
    `last_name` VARCHAR(50) NOT NULL,
    `email` VARCHAR(100) DEFAULT NULL,
    `phone` VARCHAR(20) DEFAULT NULL,
    `address_1` VARCHAR(100) DEFAULT NULL,
    `address_2` VARCHAR(100) DEFAULT NULL,
    `city` VARCHAR(50) DEFAULT NULL,
    `state` VARCHAR(50) DEFAULT NULL,
    `zip_code` VARCHAR(20) DEFAULT NULL,
    `contact_group` ENUM('Family', 'Friends', 'Business') NOT NULL DEFAULT 'Friends',
    `notes` TEXT DEFAULT NULL,
    `date_added` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample test records (5 total contacts covering Family, Friends, and Business)
INSERT INTO `contacts` (
    `first_name`,
    `last_name`,
    `email`,
    `phone`,
    `address_1`,
    `address_2`,
    `city`,
    `state`,
    `zip_code`,
    `contact_group`,
    `notes`
) VALUES 
(
    'Jane',
    'Doe',
    'jane.doe@example.com',
    '(337) 555-0199',
    '104 University Circle',
    'Apt 4B',
    'Lafayette',
    'LA',
    '70504',
    'Friends',
    'Met at university orientation. Prefers text messages.'
),
(
    'Robert',
    'Chen',
    'robert.chen@familymail.org',
    '(225) 555-0142',
    '742 Highland Road',
    NULL,
    'Baton Rouge',
    'LA',
    '70808',
    'Family',
    'Cousin on mother\'s side. Best time to call is weekend mornings.'
),
(
    'Leeroy',
    'Jenkins',
    'ljenkins@acmesolutions.com',
    '(504) 555-0188',
    '500 Poydras Street',
    'Suite 1200',
    'New Orleans',
    'LA',
    '70130',
    'Business',
    'Senior Project Manager at Acme Digital Solutions. Primary point of contact for quarterly deliverables.'
),
(
    'Marcus',
    'Vance',
    'm.vance@vancetech.io',
    '(512) 555-0173',
    '1200 Congress Avenue',
    'Floor 3',
    'Austin',
    'TX',
    '78701',
    'Business',
    'Database infrastructure consultant. Met at regional web conference; available for backend architecture reviews.'
),
(
    'Elena',
    'Rodriguez',
    'elena.rodriguez@example.com',
    '(337) 555-0125',
    '320 E St Mary Boulevard',
    NULL,
    'Lafayette',
    'LA',
    '70503',
    'Friends',
    'Classmate from INFX 472. Coordinates the weekly study group and review sessions.'
);