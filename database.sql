-- Swahilipot Digital Suggestion Box - Database Schema
-- Compatible with standard suggestion box table structure

CREATE DATABASE IF NOT EXISTS suggestion_box
    CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE suggestion_box;

-- Feedback / suggestions table
CREATE TABLE IF NOT EXISTS suggestions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    category ENUM('Suggestion', 'Complaint', 'Compliment', 'Recommendation') NOT NULL,
    message TEXT NOT NULL,
    status ENUM('Pending', 'In Progress', 'Resolved') NOT NULL DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_category (category),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Admin users table
CREATE TABLE IF NOT EXISTS admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- After importing, visit setup.php once to create the admin account securely,
-- OR insert an admin manually using a bcrypt hash from:
--   echo password_hash('your_password', PASSWORD_DEFAULT);
