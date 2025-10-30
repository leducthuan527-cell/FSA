/*
  # Create Moderation Logs Table

  1. New Tables
    - `moderation_logs`
      - `id` (uuid, primary key) - Unique identifier for each moderation log
      - `user_id` (integer) - ID of the user who submitted the content
      - `content_type` (text) - Type of content being moderated (comment, post, etc.)
      - `content_id` (integer, nullable) - ID of the content in the database (null if rejected)
      - `original_text` (text) - The original text that was moderated
      - `is_flagged` (boolean) - Whether the content was flagged as inappropriate
      - `flagged_categories` (jsonb) - OpenAI moderation categories that were flagged
      - `moderation_response` (jsonb) - Full OpenAI moderation API response
      - `action_taken` (text) - Action taken (approved, rejected)
      - `created_at` (timestamptz) - When the moderation check occurred

  2. Security
    - Enable RLS on `moderation_logs` table
    - Add policy for admins to view all moderation logs
    - Users cannot view moderation logs (admin-only feature)

  3. Indexes
    - Index on `user_id` for filtering logs by user
    - Index on `is_flagged` for filtering flagged content
    - Index on `created_at` for time-based queries
*/

CREATE TABLE IF NOT EXISTS moderation_logs (
  id uuid PRIMARY KEY DEFAULT gen_random_uuid(),
  user_id integer NOT NULL,
  content_type text NOT NULL,
  content_id integer,
  original_text text NOT NULL,
  is_flagged boolean NOT NULL DEFAULT false,
  flagged_categories jsonb,
  moderation_response jsonb,
  action_taken text NOT NULL,
  created_at timestamptz DEFAULT now()
);

ALTER TABLE moderation_logs ENABLE ROW LEVEL SECURITY;

CREATE POLICY "Only admins can view moderation logs"
  ON moderation_logs
  FOR SELECT
  TO authenticated
  USING (false);

CREATE INDEX IF NOT EXISTS idx_moderation_logs_user_id ON moderation_logs(user_id);
CREATE INDEX IF NOT EXISTS idx_moderation_logs_is_flagged ON moderation_logs(is_flagged);
CREATE INDEX IF NOT EXISTS idx_moderation_logs_created_at ON moderation_logs(created_at DESC);