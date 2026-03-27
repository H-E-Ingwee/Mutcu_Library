# 📚 MUTCU E-Library

A fully-featured digital library platform built with **PHP** and **MySQL**, offering a modern, user-friendly interface for managing, browsing, and sharing books and articles. The platform includes comprehensive admin management tools, user authentication, bookmarking features, and activity tracking.

---

## 🌟 Features

### User Features
- **User Authentication** - Secure registration, login, and logout with password hashing
- **Book & Article Browsing** - Browse a vast collection of books and academic articles
- **Advanced Search & Filter** - Filter by category, author, and search terms
- **Bookmarking System** - Save books and articles to personal reading list
- **Reading Statistics** - Track reading goals and personal reading history
- **User Profile** - Manage personal information and viewing preferences
- **Download Tracking** - Download books and track download history
- **Event Logging** - System tracks user activities (views, downloads, bookmarks)

### Admin Features
- **Dashboard & Analytics** - View comprehensive statistics and charts
  - Total books, articles, and users
  - Category distribution analysis
  - Weekly interaction metrics
  - Recent user activities
- **Book Management** - Add, edit, and delete books with cover images
- **Article Management** - Add and manage academic articles with links
- **User Management** - View all users, manage roles, and user statistics
- **Content Moderation** - Monitor and control platform content
- **Event Tracking** - View detailed user activity logs
- **Super Admin Setup** - Automatic creation of admin accounts on first setup

### Security Features
- **CSRF Protection** - Cross-Site Request Forgery token validation on all forms
- **Password Hashing** - Secure password storage using PHP's PASSWORD_DEFAULT
- **Session Management** - Secure session handling and user authentication
- **Super Admin Accounts** - Pre-configured admin accounts with role protection
- **Database Validation** - Automatic table creation and schema validation

---

## 🛠️ Technology Stack

| Component | Technology |
|-----------|-----------|
| **Backend** | PHP 7.4+ |
| **Database** | MySQL / MariaDB |
| **Frontend** | HTML5, CSS3, Tailwind CSS |
| **UI Components** | Bootstrap 5.3.2 |
| **Icons** | Bootstrap Icons |
| **JavaScript** | Vanilla JavaScript |
| **Server** | Apache (XAMPP) |
| **APIs** | OpenAI API (configured in config.php) |

---

## 📋 Prerequisites

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Apache Web Server (or XAMPP)
- Browser with JavaScript enabled
- Internet connection (for CDN resources)

---

## 🚀 Installation & Setup

### Step 1: Clone or Download the Project
```bash
# Clone the repository or extract the project files
git clone <repository-url>
# Or place files in: C:\xampp\htdocs\Mutcu_Library
```

### Step 2: Start XAMPP Services
1. Open XAMPP Control Panel
2. Start **Apache** and **MySQL** services
3. Verify they are running (green indicators)

### Step 3: Database Configuration
The database is **automatically created** on first load, but you can manually set it up:

1. Open [http://localhost/phpmyadmin](http://localhost/phpmyadmin)
2. The system will auto-create:
   - Database: `mutcu_library`
   - Required tables with proper structure
   - Super admin accounts

### Step 4: Configure API Keys (Optional)
Edit `config.php` and add your OpenAI API key:
```php
define('OPENAI_API_KEY', 'your-api-key-here');
```

### Step 5: Access the Application
Navigate to: **http://localhost/Mutcu_Library**

---

## 🔐 Pre-configured Admin Accounts

The system automatically creates two super admin accounts:

| Name | Email | Password | Role |
|------|-------|----------|------|
| User| I*****@gmail.com | ********** | Admin |
| User| M*****@gmail.com | ************| Admin |

> ⚠️ **Important**: Change these passwords immediately after first login!

---

## 📁 Project Structure

```
Mutcu_Library/
├── 📄 index.php              # Landing page (redirects to home)
├── 📄 home.php               # Main homepage with featured content
├── 📄 login.php              # User login page
├── 📄 register.php           # User registration page
├── 📄 admin.php              # Admin dashboard
├── 📄 profile.php            # User profile management
├── 📄 library.php            # Complete books/articles library
├── 📄 articles.php           # Articles browsing page
├── 📄 article.php            # Individual article view
├── 📄 actions.php            # Handles AJAX/form actions
├── 📄 download.php           # Download handler
├── 📄 db.php                 # Database connection & setup
├── 📄 functions.php          # Global helper functions
├── 📄 config.php             # Configuration (API keys)
├── 📄 policy.php             # Privacy/Terms policy page
├── 📄 README.md              # This file
│
├── 📁 partials/              # Reusable HTML components
│   ├── header.php            # Navigation header
│   └── footer.php            # Footer component
│
├── 📁 assets/                # Static resources
│   ├── css/
│   │   └── style.css         # Custom CSS styles
│   └── js/
│       └── app.js            # JavaScript utilities
│
├── 📁 uploads/               # User-uploaded files
│   └── covers/               # Book cover images
│
└── 📁 data/                  # Data files
    └── mutcu.db              # SQLite database (if used)
```

---

## 🗄️ Database Schema

### users
```sql
id               INT PRIMARY KEY AUTO_INCREMENT
name             VARCHAR(255) NOT NULL
email            VARCHAR(255) UNIQUE NOT NULL
password         VARCHAR(255) NOT NULL (hashed)
role             VARCHAR(50) DEFAULT 'member'
reading_goal     INT DEFAULT 0
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### books
```sql
id               INT PRIMARY KEY AUTO_INCREMENT
title            VARCHAR(255) NOT NULL
author           VARCHAR(255) NOT NULL
category         VARCHAR(100) NOT NULL
description      TEXT
cover            VARCHAR(500)
drive_link       VARCHAR(500) NOT NULL
added_by         INT (user who added book)
download_count   INT DEFAULT 0
view_count       INT DEFAULT 0
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### articles
```sql
id               INT PRIMARY KEY AUTO_INCREMENT
title            VARCHAR(255) NOT NULL
author           VARCHAR(255) NOT NULL
abstract         TEXT
link             VARCHAR(500) NOT NULL
date             VARCHAR(50)
read_time        VARCHAR(50)
added_by         INT
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### user_bookmarks
```sql
id               INT PRIMARY KEY AUTO_INCREMENT
user_id          INT NOT NULL
book_id          INT NOT NULL
status           VARCHAR(20) DEFAULT 'to_read'
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
UNIQUE KEY       (user_id, book_id)
```

### events
```sql
id               INT PRIMARY KEY AUTO_INCREMENT
user_id          INT (nullable for system events)
event_type       VARCHAR(50) ('view', 'download', 'bookmark', etc.)
target_type      VARCHAR(50) ('book', 'article')
target_id        INT
created_at       TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

---

## 🎯 Core Functions Reference

### Authentication Functions
| Function | Purpose |
|----------|---------|
| `currentUser()` | Get current logged-in user object |
| `isAdmin()` | Check if current user is admin |
| `csrf_token()` | Get CSRF token for form validation |
| `verify_csrf()` | Validate CSRF token on form submission |

### Content Functions
| Function | Purpose |
|----------|---------|
| `getBooks($limit)` | Fetch all or limited books |
| `getArticles($limit)` | Fetch all or limited articles |
| `getUsers()` | Get all users with activity stats |
| `processBooks(&$books)` | Validate and generate book covers |
| `getSymbolicCover($category, $title)` | Generate placeholder cover image |

### User Functions
| Function | Purpose |
|----------|---------|
| `getUserBookmarks($userId)` | Get user's saved books |
| `getUserReadingHistory($userId)` | Get user's reading history |
| `logEvent($userId, $eventType, $targetType, $targetId)` | Log user activity |

### Analytics Functions
| Function | Purpose |
|----------|---------|
| `getStats()` | Get overall platform statistics |
| `getCategoryDistribution()` | Get books by category |
| `getWeeklyInteractions()` | Get weekly activity data |

---

## 🎨 Styling & UI

### Design System
- **Color Scheme**:
  - Primary: `#060B26` (Dark Blue)
  - Secondary: `#0B133A`
  - Accent: `#FF9800` (Orange)
  - Background: `#F4F6FB` (Light)
  
- **Typography**:
  - Headings: Montserrat (600-800 weight)
  - Body: Lato (400-700 weight)
  
- **Framework**: Tailwind CSS + Bootstrap 5.3.2
- **Responsive**: Mobile-first design, fully responsive

### Key UI Components
- Navigation header with user menu
- Dynamic search and filter bars
- Card-based book/article displays
- Modal dialogs for actions
- Toast notifications for feedback
- Responsive admin dashboard
- Activity feed and charts

---

## 📖 User Workflows

### 1. New User Registration
1. Click "Register" on homepage
2. Fill in name, email, password
3. System creates user account with 'member' role
4. User redirected to login

### 2. Book Discovery
1. Browse homepage for featured books
2. Visit "Library" section for complete collection
3. Use search/filter by category
4. Click book to view details
5. Download or bookmark book

### 3. Reading Management
1. View "My Reading List" in profile
2. Track reading goals and progress
3. View reading history/statistics
4. Organize bookmarks (to_read, reading, finished)

### 4. Admin Management
1. Login with admin account
2. Access admin dashboard
3. View analytics and statistics
4. Manage books, articles, and users
5. Monitor user activities

---

## 🔄 Key Actions (actions.php)

The `actions.php` file handles all dynamic operations including:
- Book/article CRUD operations
- User management
- Bookmark operations
- Reading list updates
- Profile modifications
- Admin functions

Submit forms with `POST` method to initiate actions with CSRF validation.

---

## 📊 Admin Dashboard Features

### Statistics Cards
- Total Books
- Total Articles
- Total Users
- Total Downloads

### Analytics Charts
- **Category Distribution** - Books by category (pie/bar chart)
- **Weekly Interactions** - Weekly user activity graph
- **Event Breakdown** - Events by type and target

### Management Sections
- **Recent Events** - Last 10 user activities with details
- **User List** - All users with registration date and last activity
- **Books Management** - Add/edit/delete books
- **Articles Management** - Add/edit/delete articles

---

## 🔒 Security Considerations

### Implemented Security Measures
✅ Password hashing with PASSWORD_DEFAULT  
✅ CSRF token validation on all forms  
✅ Secure session management  
✅ SQL injection prevention (parameterized queries)  
✅ User role-based access control  
✅ Super admin role protection  
✅ XSS protection through escaping  

### Best Practices
- Always verify CSRF tokens in forms
- Use prepared statements for database queries
- Never store sensitive data in plain text
- Regularly update passwords for admin accounts
- Monitor user activities through event logs
- Keep PHP, MySQL, and dependencies updated

---

## 🎓 User Roles

| Role | Permissions |
|------|-----------|
| **Member** | Browse books/articles, bookmark, download, view profile, track reading |
| **Admin** | All member permissions + manage content + manage users + view analytics |
| **Super Admin** | Full system access, role management, delete users/content |

---

## 🐛 Troubleshooting

### Issue: Database connection error
**Solution**: 
- Ensure MySQL is running in XAMPP
- Check database credentials in `db.php`
- Verify `mutcu_library` database exists

### Issue: Books don't show covers
**Solution**: 
- Check if cover URLs are valid
- System generates placeholder covers automatically
- Ensure `uploads/covers/` directory exists and is writable

### Issue: Admin page shows 404
**Solution**: 
- Verify you're logged in as admin
- Non-admin users are redirected to login
- Check your role in database: `users.role`

### Issue: File upload errors
**Solution**: 
- Check folder permissions on `uploads/` directory
- Verify PHP `upload_max_filesize` setting
- Ensure disk space is available

---

## 📝 Configuration Guide

### config.php
```php
// OpenAI API Key (for future AI features)
define('OPENAI_API_KEY', 'your-api-key-here');
```

### db.php
```php
$host = 'localhost';          // Database host
$dbname = 'mutcu_library';    // Database name
$username = 'root';           // MySQL username
$password = '';               // MySQL password
```

### Customize Super Admins
Edit the `$superAdmins` array in `functions.php`:
```php
$superAdmins = [
    ['name' => 'Your Name', 'email' => 'your@email.com', 'password' => 'secure_password']
];
```

---

## 🔄 Database Migration Notes

The system automatically:
- Creates missing tables on first load
- Adds new columns using `ALTER TABLE ... IF NOT EXISTS`
- Creates indexes for performance
- Sets up relationships between tables

No manual migration needed - it's self-healing!

---

## 📱 Responsive Breakpoints

- **Mobile**: < 640px
- **Tablet**: 640px - 1024px
- **Desktop**: > 1024px

All pages are fully responsive and mobile-friendly.

---

## 🚀 Performance Optimizations

- Database query limits for homepage
- Event logging for analytics
- Book cover caching with placeholder generation
- Indexed database columns for fast queries
- Lazy loading of images in library
- Optimized asset delivery via CDN

---

## 📞 Support & Contribution

### Reporting Issues
- Document the issue with steps to reproduce
- Include error messages or screenshots
- Specify browser and PHP version

### Contributing
1. Create a feature branch
2. Make your changes with clear commit messages
3. Test thoroughly
4. Submit a pull request

---

## 📄 License & Author

**Project**: MUTCU E-Library  
**Version**: 1.0.0  
**Created**: 2024-2026  
**Maintained by**: H-E-Ingwee  

---

## 🎉 Quick Start Commands

```bash
# Start Apache and MySQL in XAMPP
# Visit: http://localhost/Mutcu_Library

# Login with admin credentials:
# Email: I****@gmail.com
# Password: ******

# Or register new account:
# Visit: http://localhost/Mutcu_Library/register.php
```

---

## 📚 Additional Resources

- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [Tailwind CSS](https://tailwindcss.com/docs)
- [Bootstrap 5](https://getbootstrap.com/docs/5.3/)

---

**Last Updated**: March 2026  
**Status**: Active Development  
**Compatibility**: PHP 7.4+, MySQL 5.7+
