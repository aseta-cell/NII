CREATE DATABASE IF NOT EXISTS journal
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE journal;

-- ==========================
-- Users
-- ==========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    affiliation VARCHAR(255),
    author_id VARCHAR(50),
    research_interests TEXT,
    country VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- ==========================
-- Articles
-- ==========================
CREATE TABLE articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(300) NOT NULL,
    abstract TEXT,
    keywords TEXT,
    journal VARCHAR(200),
    status VARCHAR(50) DEFAULT 'Submitted',
    pdf_file VARCHAR(255),
    submitted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);

-- ==========================
-- Reviews
-- ==========================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    article_id INT NOT NULL,
    reviewer_name VARCHAR(150),
    decision VARCHAR(50),
    comments TEXT,
    review_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(article_id)
    REFERENCES articles(id)
    ON DELETE CASCADE
);

-- ==========================
-- Payments
-- ==========================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    amount DECIMAL(10,2),
    currency VARCHAR(10) DEFAULT 'USD',
    payment_status VARCHAR(50) DEFAULT 'Pending',
    payment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY(user_id)
    REFERENCES users(id)
    ON DELETE CASCADE
);