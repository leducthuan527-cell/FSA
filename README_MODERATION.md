# AI Content Moderation System

This project includes an AI-powered content moderation system that automatically detects and blocks inappropriate comments before they are saved to the database.

## Features

- **Automatic Content Detection**: Uses OpenAI's Moderation API (`omni-moderation-latest`) to classify content
- **Real-time Blocking**: Prevents inappropriate content from being posted
- **Comprehensive Logging**: Stores all moderation results in Supabase for admin review
- **Admin Dashboard**: View flagged content, categories, and moderation statistics
- **User Feedback**: Clear error messages when content is flagged

## Architecture

### Backend (PHP)
- **`classes/ContentModerator.php`**: Core moderation logic
  - Calls OpenAI Moderation API
  - Logs results to Supabase
  - Returns moderation decisions

- **`api/moderate-content.php`**: REST endpoint for content moderation
  - Accepts POST requests with text content
  - Returns JSON response with moderation result

- **`classes/ModerationLog.php`**: Database access for moderation logs
  - Retrieves logs from Supabase
  - Provides statistics and filtering

### Frontend (JavaScript)
- **`assets/js/content-moderation.js`**: Client-side moderation handler
  - Sends content to backend API before submission
  - Shows loading states
  - Displays error/success messages

### Database (Supabase)
- **`moderation_logs`** table stores:
  - Original text
  - Flagged categories
  - Full API response
  - User ID and content type
  - Action taken (approved/rejected)

## Setup

### 1. OpenAI API Key
Add your OpenAI API key to the environment:

```bash
export OPENAI_API_KEY="sk-your-api-key-here"
```

Or add it to your server configuration (Apache/Nginx).

### 2. Supabase Configuration
The system uses the existing Supabase connection configured in `.env`:
- `VITE_SUPABASE_URL`
- `VITE_SUPABASE_ANON_KEY`

### 3. Database Migration
The `moderation_logs` table is created automatically via Supabase migration.

## Usage

### Comment Submission Flow

1. User writes a comment and clicks "Post Comment"
2. JavaScript intercepts the form submission
3. Content is sent to `api/moderate-content.php`
4. PHP calls OpenAI Moderation API
5. Result is logged to Supabase
6. If flagged: User sees error message, comment is not posted
7. If safe: Form submits normally, comment is saved

### Admin Review

Admins can view moderation logs at:
- **Dashboard**: Overall moderation statistics
- **AI Moderation Tab**: Detailed list of flagged content with categories and scores

## API Response Format

### Success (Content Approved)
```json
{
  "success": true,
  "is_flagged": false,
  "action_taken": "approved",
  "message": "Content approved"
}
```

### Flagged Content
```json
{
  "success": true,
  "is_flagged": true,
  "flagged_categories": [
    {
      "category": "hate",
      "score": 0.89
    }
  ],
  "action_taken": "rejected",
  "message": "Your comment may contain inappropriate language and cannot be posted."
}
```

## Moderation Categories

OpenAI's moderation API checks for:
- **hate**: Hateful content
- **harassment**: Harassment or bullying
- **self-harm**: Self-harm content
- **sexual**: Sexual content
- **violence**: Violent content
- And subcategories for each

## Customization

### Modify Error Messages
Edit the message in `classes/ContentModerator.php`:

```php
'message' => $is_flagged
    ? 'Your custom error message here'
    : 'Content approved'
```

### Change Moderation Model
Edit the model in `classes/ContentModerator.php`:

```php
$data = [
    'input' => $text,
    'model' => 'omni-moderation-latest' // or 'text-moderation-stable'
];
```

### Adjust Thresholds
The OpenAI API returns category scores. To implement custom thresholds, modify the flagging logic in `ContentModerator.php`.

## Testing

To test the moderation system:

1. Try posting a clean comment: "This is a great post!"
   - Should be approved and posted

2. Try posting inappropriate content
   - Should be blocked with error message

3. Check admin panel → AI Moderation
   - View logged attempts with categories

## Security Notes

- API keys are stored server-side only
- Moderation logs are admin-only (RLS enabled)
- All content is checked before database insertion
- Original text is stored for audit purposes

## File Structure

```
project/
├── api/
│   └── moderate-content.php          # API endpoint
├── classes/
│   ├── ContentModerator.php          # Core moderation logic
│   └── ModerationLog.php             # Database access
├── assets/
│   ├── js/
│   │   └── content-moderation.js     # Frontend handler
│   └── css/
│       └── style.css                 # Moderation UI styles
├── admin/
│   └── index.php                     # Admin dashboard (includes logs)
└── post.php                          # Comment form integration
```

## Troubleshooting

### Moderation not working
- Check that `OPENAI_API_KEY` is set
- Verify Supabase connection in `.env`
- Check browser console for JavaScript errors

### Content not being blocked
- Verify `content-moderation.js` is loaded
- Check form has correct IDs (`comment-form`, `submit-comment-btn`)
- Test API endpoint directly via curl

### Logs not appearing
- Verify Supabase connection
- Check RLS policies (logs are admin-only)
- Ensure `ModerationLog.php` is included in admin panel
