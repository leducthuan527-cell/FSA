-- Remove media_file columns and add notifications table
ALTER TABLE posts DROP COLUMN IF EXISTS media_file;
ALTER TABLE comments DROP COLUMN IF EXISTS media_file;

-- Create notifications table
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('post_approved', 'post_rejected', 'comment_approved', 'comment_rejected', 'account_limited', 'account_banned', 'account_restored') NOT NULL,
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- Add banner column to users table
ALTER TABLE users ADD COLUMN banner VARCHAR(255) DEFAULT 'default-banner.jpg';