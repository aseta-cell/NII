-- ===========================================
-- Journal Management System — database schema
--
-- Import into an EMPTY database, e.g.:
--   mysql -u journal_user -p journal < database.sql
-- (or phpMyAdmin → select the database → Import)
-- ===========================================

SET NAMES utf8mb4;

-- ==========================
-- Users (authors, reviewers, editors)
-- ==========================
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    fullname VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    affiliation VARCHAR(255),
    orcid VARCHAR(50),
    research_interests TEXT,
    country VARCHAR(100),
    role ENUM('author', 'reviewer', 'editor') NOT NULL DEFAULT 'author',
    free_access TINYINT(1) NOT NULL DEFAULT 0,  -- special (family) account: no fees
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================
-- Submitted manuscripts / published articles
--
-- status flow:
--   Submitted → Under Review → Accepted / Revision / Rejected
--   Accepted → Payment Pending → Paid → Published
-- ==========================
CREATE TABLE submissions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(300) NOT NULL,
    abstract TEXT,
    keywords TEXT,
    journal VARCHAR(200),
    filename VARCHAR(255),
    status VARCHAR(50) NOT NULL DEFAULT 'Submitted',
    reviewer_id INT NULL,
    doi VARCHAR(150),
    views INT NOT NULL DEFAULT 0,
    downloads INT NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (status),
    INDEX (journal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================
-- Peer reviews
-- ==========================
CREATE TABLE reviews (
    id INT AUTO_INCREMENT PRIMARY KEY,
    submission_id INT NOT NULL,
    reviewer_id INT NULL,
    reviewer_name VARCHAR(150),
    status VARCHAR(50) NOT NULL,
    comments TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE,
    FOREIGN KEY (reviewer_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================
-- Article processing charge payments
--
-- status: Pending → Awaiting Confirmation → Paid (or Rejected)
-- plan 'free' = fee waived for a special-access account (amount 0)
-- ==========================
CREATE TABLE payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    submission_id INT NOT NULL,
    plan VARCHAR(50) NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'USD',
    method VARCHAR(50) NOT NULL DEFAULT 'Bank transfer',
    status VARCHAR(50) NOT NULL DEFAULT 'Pending',
    receipt_file VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    paid_at DATETIME NULL,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (submission_id) REFERENCES submissions(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================
-- News / videos / announcements shown on the About page
-- ==========================
CREATE TABLE editorial_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    type VARCHAR(50) NOT NULL,
    title VARCHAR(300) NOT NULL,
    description TEXT,
    content TEXT,
    media_url VARCHAR(500),
    thumbnail_url VARCHAR(500),
    status VARCHAR(20) NOT NULL DEFAULT 'Draft',
    author_id INT NOT NULL,
    published_at DATETIME NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================
-- Journal pages: comments, likes, follows, views
-- ==========================
CREATE TABLE journal_comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal VARCHAR(200) NOT NULL,
    user_id INT NOT NULL,
    comment TEXT NOT NULL,
    status VARCHAR(20) NOT NULL DEFAULT 'Published',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX (journal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journal_likes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal VARCHAR(200) NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (journal, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journal_follows (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal VARCHAR(200) NOT NULL,
    user_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY (journal, user_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE journal_views (
    id INT AUTO_INCREMENT PRIMARY KEY,
    journal VARCHAR(200) NOT NULL,
    user_id INT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX (journal)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
