-- IPO Pulse Database Schema (corrected for Hostinger/MySQL)

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    mobile VARCHAR(20) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    email_alerts TINYINT(1) NOT NULL DEFAULT 1,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS admin_users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL
);

CREATE TABLE IF NOT EXISTS news (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    content TEXT NOT NULL,
    published_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS ipos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    open_date DATE NOT NULL,
    close_date DATE NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    details TEXT,
    status ENUM('upcoming','live','closed') NOT NULL DEFAULT 'upcoming'
);

CREATE TABLE IF NOT EXISTS demat_accounts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    account_number VARCHAR(32) NOT NULL,
    depository VARCHAR(32) NOT NULL,
    holder_name VARCHAR(100),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS ipo_applications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    ipo_id INT NOT NULL,
    demat_account_id INT NOT NULL,
    applied_lots INT NOT NULL,
    application_date DATE NOT NULL,
    status ENUM('applied','allotted','rejected','listed') NOT NULL DEFAULT 'applied',
    listing_gain DECIMAL(10,2),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (ipo_id) REFERENCES ipos(id) ON DELETE CASCADE,
    FOREIGN KEY (demat_account_id) REFERENCES demat_accounts(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    message TEXT NOT NULL,
    is_read TINYINT(1) NOT NULL DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Sample admin user
INSERT INTO admin_users (username, password) VALUES
('admin', '$2y$10$QeQw1Qw1Qw1Qw1Qw1Qw1QeQw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw1Qw');

-- Sample news
INSERT INTO news (title, content) VALUES
('IPO Market Heats Up in August', 'Several new IPOs are launching this month, attracting strong investor interest.'),
('How to Analyze an IPO', 'Learn the key factors to consider before investing in an IPO.'),
('Recent IPO Performance', 'A look back at the performance of recent IPOs in the Indian market.');

-- Sample IPOs
INSERT INTO ipos (name, open_date, close_date, price, details, status) VALUES
('ABC Tech Ltd', '2025-08-10', '2025-08-14', 120.50, 'ABC Tech Ltd is a leading technology company launching its IPO.', 'closed'),
('XYZ Pharma', '2025-08-12', '2025-08-16', 95.00, 'XYZ Pharma is a pharmaceutical company with a strong R&D pipeline.', 'live'),
('FinServe Bank', '2025-08-15', '2025-08-19', 150.00, 'FinServe Bank is a new-age digital bank entering the market.', 'upcoming');
