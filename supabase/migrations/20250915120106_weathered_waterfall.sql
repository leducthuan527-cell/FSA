/*
  # Add new notification types

  1. Updates
    - Extend notification type enum to include new notification types
    - Add support for post/comment submission and deletion notifications

  2. Changes
    - Add post_submitted, comment_submitted, post_deleted, comment_deleted types
*/

-- Update the notifications table type enum to include new types
ALTER TABLE notifications MODIFY COLUMN type ENUM(
    'post_approved', 
    'post_rejected', 
    'comment_approved', 
    'comment_rejected', 
    'account_limited', 
    'account_banned', 
    'account_restored',
    'post_submitted',
    'comment_submitted', 
    'post_deleted',
    'comment_deleted'
) NOT NULL;