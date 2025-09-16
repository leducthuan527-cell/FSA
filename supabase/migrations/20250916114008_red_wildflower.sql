/*
  # Add profile fields to users table

  1. New Columns
    - `banner` (varchar) - User profile banner image
    - `description` (text) - User profile description with BBCode support

  2. Changes
    - Add banner column with default value
    - Add description column with default value
    - Update existing users to have default values
*/

-- Add banner column to users table
ALTER TABLE users ADD COLUMN IF NOT EXISTS banner VARCHAR(255) DEFAULT 'default-banner.jpg';

-- Add description column to users table  
ALTER TABLE users ADD COLUMN IF NOT EXISTS description TEXT DEFAULT 'Nothing here~';

-- Update existing users to have default values if they don't already
UPDATE users SET banner = 'default-banner.jpg' WHERE banner IS NULL OR banner = '';
UPDATE users SET description = 'Nothing here~' WHERE description IS NULL OR description = '';