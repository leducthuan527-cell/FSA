# Personal Blog System

A comprehensive PHP-based personal blog system with user management, content moderation, and admin panel.

## Features

### User Features
- **User Registration & Authentication**: Secure sign-up and sign-in system
- **Profile Management**: Users can update their profiles, avatars, and personal information
- **Post Creation**: Users can create and submit posts for admin approval
- **Commenting System**: Users can comment on published posts
- **Content Reporting**: Users can report inappropriate posts or comments
- **Personal Statistics**: View post count, comment count, and profile information

### Admin Features
- **Admin Dashboard**: Comprehensive overview of site statistics
- **Content Moderation**: Approve/reject posts and comments before publication
- **Report Management**: Review and handle user reports
- **User Management**: Limit or ban users who violate guidelines
- **Real-time Updates**: Auto-refresh functionality for admin panel

### Security Features
- **Account Limitations**: Limited users cannot post or comment
- **Content Hiding**: Reported content is immediately hidden from reporter's view
- **Profile Restrictions**: Limited/banned user profiles are inaccessible to others
- **Input Sanitization**: All user inputs are properly sanitized
- **Password Hashing**: Secure password storage using PHP's password_hash()

## Installation

1. **Database Setup**:
   ```sql
   -- Import the database schema
   mysql -u username -p database_name < database/schema.sql
   ```

2. **Configuration**:
   - Update database credentials in `config/database.php`
   - Ensure the `assets/images/avatars/` directory is writable
   - Place a default avatar image in the avatars directory

3. **Web Server**:
   - Upload all files to your web server
   - Ensure PHP 7.4+ and MySQL 5.7+ are installed
   - Enable PHP extensions: PDO, PDO_MySQL, GD (for image handling)

4. **Default Admin Account**:
   - Username: `admin`
   - Email: `admin@blog.com`
   - Password: `admin123`
   - **Change these credentials immediately after installation!**

## File Structure

```
├── admin/                  # Admin panel
│   └── index.php          # Admin dashboard
├── assets/                # Static assets
│   ├── css/              # Stylesheets
│   ├── js/               # JavaScript files
│   └── images/           # Images and avatars
├── auth/                 # Authentication
│   ├── login.php         # Login page
│   ├── register.php      # Registration page
│   └── logout.php        # Logout handler
├── classes/              # PHP classes
│   ├── User.php          # User management
│   ├── Post.php          # Post management
│   ├── Comment.php       # Comment management
│   └── Report.php        # Report management
├── config/               # Configuration
│   ├── database.php      # Database connection
│   └── init.php          # Initialization and helpers
├── database/             # Database schema
│   └── schema.sql        # Database structure
├── includes/             # Reusable components
│   ├── header.php        # Site header
│   └── footer.php        # Site footer
├── index.php             # Homepage
├── post.php              # Individual post view
├── profile.php           # User profile page
├── create-post.php       # Post creation form
├── edit-profile.php      # Profile editing form
└── report.php            # Report handling
```

## Usage

### For Users
1. **Registration**: Create an account using the registration form
2. **Creating Posts**: Write posts that will be submitted for admin approval
3. **Commenting**: Comment on published posts (comments require approval)
4. **Reporting**: Report inappropriate content using the report button
5. **Profile Management**: Update your profile information and avatar

### For Admins
1. **Access Admin Panel**: Navigate to `/admin/` after logging in as admin
2. **Moderate Content**: Review and approve/reject pending posts and comments
3. **Handle Reports**: Review user reports and take appropriate action
4. **Manage Users**: Limit or ban users who violate community guidelines
5. **Monitor Statistics**: View site activity and user engagement metrics

## Security Considerations

- Change default admin credentials immediately
- Regularly update PHP and database software
- Implement HTTPS for production use
- Consider adding CAPTCHA for registration/login forms
- Set up regular database backups
- Monitor error logs for suspicious activity

## Customization

### Styling
- Modify `assets/css/style.css` for general styling
- Update `assets/css/admin.css` for admin panel styling
- Colors, fonts, and layouts can be easily customized

### Functionality
- Extend classes in the `classes/` directory for additional features
- Add new pages following the existing structure
- Modify database schema as needed for new features

## Browser Support

- Chrome 60+
- Firefox 55+
- Safari 12+
- Edge 79+

## License

This project is open source and available under the MIT License.

## Support

For issues and questions, please review the code comments and documentation. The system is designed to be self-explanatory with clear naming conventions and comprehensive error handling.