# 🚀 MUTCU E-Library - Hostinger Deployment Guide

Complete step-by-step guide to deploy the MUTCU E-Library to **Hostinger** at **library.mutcu.org**

---

## 📋 Pre-Deployment Checklist

Before you start, ensure you have:
- ✅ Hostinger account with hPanel access
- ✅ Domain added in hPanel (library.mutcu.org)
- ✅ FTP/File Manager access
- ✅ MySQL database access
- ✅ All project files on your laptop
- ✅ Database backup (.sql file) or willingness to recreate tables

---

## 🔧 Step 1: Upload PHP Files to Hostinger

### 1.1 Access File Manager
1. Log in to **hPanel** (Hostinger dashboard)
2. Click **Websites** → **Dashboard** (for library.mutcu.org)
3. Click **Files** button
4. Open **File Manager**

### 1.2 Navigate to public_html
1. You should see a folder tree on the left
2. Click on **public_html** folder
3. This is where your website files go

### 1.3 Upload Project Files
**Option A: Upload Entire Folder (Recommended)**
1. Click **Upload** button in File Manager
2. Select your entire `Mutcu_Library` folder from your laptop
3. Drop or select all files
4. Wait for upload to complete (may take a few minutes)

**Option B: Upload Individual Files**
1. Upload these main files to `public_html/`:
   - `index.php`
   - `home.php`
   - `login.php`
   - `register.php`
   - `admin.php`
   - `library.php`
   - `articles.php`
   - `profile.php`
   - `download.php`
   - `actions.php`
   - `db.php`
   - `functions.php`
   - `config.php`
   - `policy.php`
   - `README.md`

2. Upload these folders to `public_html/`:
   - `partials/` (header.php, footer.php)
   - `assets/` (css/, js/)
   - `uploads/` (empty folder, will be created by app)

### 1.4 Final File Structure in public_html
```
public_html/
├── index.php
├── home.php
├── login.php
├── register.php
├── admin.php
├── library.php
├── articles.php
├── article.php
├── profile.php
├── download.php
├── actions.php
├── db.php
├── functions.php
├── config.php
├── policy.php
├── README.md
├── HOSTINGER-SETUP.md
├── partials/
│   ├── header.php
│   └── footer.php
├── assets/
│   ├── css/
│   │   └── style.css
│   └── js/
│       └── app.js
└── uploads/
    └── covers/ (will auto-create)
```

⚠️ **Important**: Make sure `index.php` is directly in `public_html/`, not in a subfolder!

---

## 🗄️ Step 2: Create MySQL Database on Hostinger

### 2.1 Create Database
1. In hPanel, go to **Websites** → **Dashboard** (library.mutcu.org)
2. Click **Databases** section
3. Click **+ Create Database** or **Management**
4. Fill in the form:
   - **Database Name**: `mutcu_library` (or any name you prefer)
   - **Database User**: Create new user (e.g., `mutcu_user`)
   - **Password**: Generate strong password (recommended)
   - **Collation**: Leave as default (UTF-8)

5. **Save these credentials somewhere safe**:
   ```
   Database Name: mutcu_library
   Database User: mutcu_user
   Database Password: [your_password]
   Database Host: [host shown in hPanel - usually localhost or db.hostinger.com]
   ```

### 2.2 Verify Database Connection
1. In **Databases** section, you should see your new database listed
2. Note down the exact **Database Host** (critical for config.php)

---

## 📊 Step 3: Import Database Tables (Optional)

### Option A: If You Have a .sql Backup File

1. In hPanel → **Databases** section
2. Click on your database name or **Manage**
3. Click **phpMyAdmin** (opens database management tool)
4. Click the **Import** tab
5. Click **Choose File** and select your `.sql` backup
6. Click **Go/Import**
7. Tables will be created automatically

### Option B: Let the App Auto-Create Tables (Easiest)

Skip this step! The application automatically creates tables on first load:
- When you visit your site, `db.php` auto-detects missing tables
- Creates them with proper structure
- Sets up super admin accounts
- No manual import needed!

---

## ⚙️ Step 4: Update Database Configuration

### 4.1 Edit db.php via File Manager

1. In **File Manager**, double-click **db.php** to open it
2. Find these lines (around line 2-5):

```php
$host = 'localhost';
$dbname = 'mutcu_library';
$username = 'root';
$password = '';
```

3. **Replace with Hostinger credentials**:

```php
$host = '[YOUR_DB_HOST_FROM_HPANEL]';  // e.g., 'db.hostinger.com' or 'localhost'
$dbname = 'mutcu_library';              // Database name you created
$username = 'mutcu_user';               // Database user you created
$password = '[YOUR_DB_PASSWORD]';       // Password you set
```

**Example (filled in):**
```php
$host = 'db.hostinger.com';
$dbname = 'mutcu_library';
$username = 'mutcu_user';
$password = 'MySecureP@ssw0rd123';
```

4. Click **Save** in File Manager
5. Close the editor

### 4.2 Important: Verify Database Host

⚠️ **Database host is critical!** In Hostinger:
- **Usually**: `localhost` (if MySQL on same server)
- **Sometimes**: `db.hostinger.com` or specific IP
- **Find yours in hPanel**: Websites → Dashboard → Databases → Your Database → Host

Copy the exact host from hPanel and use that!

---

## 🔐 Step 5: Update config.php (Optional)

If you want to use OpenAI API features, edit `config.php`:

1. In **File Manager**, open **config.php**
2. Replace the placeholder:
```php
define('OPENAI_API_KEY', 'your-openai-api-key-here');
```

With your actual key:
```php
define('OPENAI_API_KEY', 'sk-abc123xyz...');
```

3. Save the file

---

## 🧪 Step 6: Test Your Deployment

### 6.1 Visit Your Website
1. Open browser and go to: **https://library.mutcu.org**
2. You should see the homepage
3. Check for any errors or broken styling

### 6.2 Test Core Features

**Test User Registration:**
1. Click "Register" link
2. Fill in a test account (name, email, password)
3. Submit and verify it works
4. You should see success message

**Test Login:**
1. Click "Login"
2. Use credentials you just created
3. Verify you can login and see homepage

**Test Admin Access:**
1. Log out
2. Login with admin credentials:
   - Email: `Ingweplex@gmail.com`
   - Password: `Ingweplex`
3. You should see "Admin" link in navigation
4. Access `/admin.php` to verify admin dashboard loads

**Test Browse Books:**
1. Click "Library" or "Books"
2. Verify books display (initially empty, add them in admin panel)
3. Check if placeholder covers generate correctly

---

## 🔍 Step 7: Troubleshooting Hostinger Deployment

### Issue: "Database connection failed" or "Can't connect to database"

**Solution:**
1. Verify credentials in `db.php` match exactly what hPanel shows
2. Check database **host** - copy it exactly from hPanel
3. Verify username and password are correct
4. Try replacing `localhost` with the full host from hPanel
5. In File Manager, right-click `db.php` → Properties → ensure readable/writable

```php
// Try this:
$host = 'db.hostinger.com:3306';  // Add port if needed
```

### Issue: "No such file or directory" or 404 errors

**Solution:**
1. Verify all files uploaded to `public_html/` (not a subfolder)
2. Check that `index.php` is directly in `public_html/`
3. Try accessing: `https://library.mutcu.org/index.php`
4. If working with `/index.php` but not without, it's a URL handling issue

### Issue: Styles/CSS not loading or website looks broken

**Solution:**
1. This likely means wrong file paths
2. Check browser's Developer Tools (F12) → Network tab
3. Look for 404 errors on CSS/JS files
4. Typical issues:
   - Assets uploaded to wrong location
   - Missing `/uploads/` or `/assets/` folders
   - File paths hard-coded with wrong prefixes

**Fix paths in HTML/PHP files:**
- Change paths from: `css/style.css`
- To: `/assets/css/style.css` or `./assets/css/style.css`

### Issue: File upload in admin panel fails

**Solution:**
1. Verify `/uploads/covers/` folder exists in `public_html/`
2. Right-click folder → Properties → Make sure it's writable (777 permissions)
3. In hPanel File Manager: Right-click → Permissions → Set to 755 or 777
4. Check Hostinger PHP file upload limits (Settings → PHP settings)

### Issue: Sessions not working / can't stay logged in

**Solution:**
1. Ensure `/uploads/` folder exists (used for session storage)
2. Make sure `functions.php` has `session_start()` at the very top
3. Check browser cookies enabled
4. In hPanel, verify PHP version is 7.4 or higher (Settings → General → PHP version)

### Issue: Admin auto-accounts not created

**Solution:**
1. Delete your database and let app recreate it (easier)
2. Or manually insert super admin in phpMyAdmin:
```sql
INSERT INTO users (name, email, password, role) VALUES 
('Brian Ingwee', 'Ingweplex@gmail.com', '$2y$10$[bcrypt_hash_here]', 'admin');
```

---

## 📝 Configuration Checklist

Before going live, verify:

- [ ] All files uploaded to `/public_html/`
- [ ] `index.php` is directly in `public_html/` (not subfolder)
- [ ] Database created and credentials saved
- [ ] `db.php` updated with correct host, database, user, password
- [ ] `/uploads/` folder exists and is writable
- [ ] `/uploads/covers/` folder created
- [ ] `partials/`, `assets/` folders uploaded
- [ ] PHP version is 7.4 or higher
- [ ] MySQL version is 5.7 or higher
- [ ] Website homepage loads without 500 errors
- [ ] Can register new user
- [ ] Can login with credentials
- [ ] Admin can access admin panel
- [ ] Admin can add books/articles

---

## 🔐 Production Security Checklist

Before launching publicly:

⚠️ **Change Default Admin Passwords**
1. Login as admin (`Ingweplex@gmail.com` / `Ingweplex`)
2. Go to Profile/Settings
3. Change password to something secure
4. Do same for other admin account

⚠️ **Update Super Admin Accounts** (optional)
1. In `functions.php`, update the `$superAdmins` array with your own accounts
2. Or delete both pre-configured accounts after creating your own

⚠️ **Set Permissions Correctly**
```
public_html/        → 755
public_html/*.php   → 644
public_html/uploads → 755
uploads/covers      → 755
```

⚠️ **Enable HTTPS**
1. All Hostinger plans include free SSL
2. In hPanel: Websites → Dashboard → SSL → Auto-enable HTTPS
3. URLs automatically convert to `https://library.mutcu.org`

⚠️ **Regular Backups**
1. In hPanel: Websites → Dashboard → Backups
2. Create manual backup before major changes
3. Schedule automatic daily backups

⚠️ **Monitor Admin Panel**
1. Check dashboard regularly
2. Review user activities in event logs
3. Monitor suspicious download patterns

---

## 📊 Database Management via phpMyAdmin

### Access phpMyAdmin
1. hPanel → Websites → Dashboard → Databases
2. Click **Manage** on your database
3. Click **phpMyAdmin**

### Common Tasks

**View all users:**
```sql
SELECT * FROM users;
```

**Add a book manually:**
```sql
INSERT INTO books (title, author, category, description, cover, drive_link, added_by) 
VALUES ('Book Title', 'Author Name', 'Technology', 'Description here', '', 'https://...', 1);
```

**View user activity events:**
```sql
SELECT * FROM events ORDER BY created_at DESC LIMIT 20;
```

**Make user an admin:**
```sql
UPDATE users SET role = 'admin' WHERE email = 'user@example.com';
```

---

## 🆘 Getting Help from Hostinger Support

If you encounter issues:

1. **Contact Hostinger Support** via hPanel → Help/Support
2. Provide:
   - Database name and host
   - PHP version
   - MySQL version
   - Error messages from logs
   - Your domain (library.mutcu.org)

3. **Check hPanel Logs**:
   - Websites → Dashboard → Logs → Error logs
   - May show PHP errors or database connection issues

---

## ✅ Post-Deployment Tasks

### 1. Test Email Features (if applicable)
- Ensure registration emails send (configure SMTP in hPanel if needed)
- Check Settings → Email for configuration

### 2. Set Up Analytics
- Admin dashboard tracks user activities automatically
- Check admin panel → Analytics for stats

### 3. Add Initial Content
1. Login as admin
2. Add your first books and articles
3. Set categories and descriptions
4. Upload cover images

### 4. Configure Search (if implemented)
- Ensure search functionality works
- Test filter by category
- Test search by title/author

### 5. Monitor Performance
- Check hPanel → Websites → Analytics
- Monitor resource usage
- Watch for slow page loads

---

## 🚀 Going Live Checklist

- [ ] Website accessible at: `https://library.mutcu.org`
- [ ] HTTPS enabled and working
- [ ] All pages load without errors
- [ ] Admin dashboard fully functional
- [ ] User registration/login working
- [ ] Books and articles display correctly
- [ ] Admin accounts secured with strong passwords
- [ ] Database backups configured
- [ ] Contact information updated
- [ ] Error logging enabled
- [ ] Mobile responsiveness verified

---

## 📱 Mobile & Browser Testing

Test on:
- ✅ Chrome (Desktop & Mobile)
- ✅ Firefox (Desktop & Mobile)
- ✅ Safari (Desktop & Mobile)
- ✅ Edge (Desktop & Mobile)

Test features:
- ✅ Responsive layout
- ✅ Touch-friendly buttons
- ✅ Forms submit correctly
- ✅ Images load properly
- ✅ Navigation works on mobile

---

## 📞 Quick Reference

### Important URLs
- **Website**: https://library.mutcu.org
- **Admin Panel**: https://library.mutcu.org/admin.php
- **hPanel Dashboard**: https://hpanel.hostinger.com
- **phpMyAdmin**: Via hPanel → Databases → Manage

### Important Files (Edit on Hostinger)
- **Database Config**: `/public_html/db.php`
- **API Config**: `/public_html/config.php`
- **Main Entry**: `/public_html/index.php`

### Admin Credentials (Change After First Login!)
- **Email**: Ingweplex@gmail.com
- **Password**: Ingweplex

### Hostinger Info You'll Need
- **DB Host**: [from hPanel]
- **DB Name**: mutcu_library
- **DB User**: [you created this]
- **DB Password**: [you set this]

---

## 📚 Additional Resources

- [Hostinger Knowledge Base](https://support.hostinger.com/)
- [PHP Documentation](https://www.php.net/docs.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)
- [SSH/File Manager Guide](https://support.hostinger.com/en/articles/4101597-how-to-manage-files-using-file-manager)

---

## 🎯 Next Steps After Setup

1. **Populate Content**
   - Add books via admin panel
   - Add articles with links
   - Upload cover images

2. **Customize Branding**
   - Update colors in Tailwind config
   - Change logo/header text
   - Customize policy pages

3. **Configure Email** (optional)
   - Set up SMTP for confirmations
   - Configure from address

4. **Set Up SSL Certificate**
   - Already included with Hostinger
   - Auto-enable in hPanel

5. **Monitor & Maintain**
   - Check admin dashboard weekly
   - Review error logs
   - Update passwords regularly

---

**Setup Notes**: This guide is specific to MUTCU E-Library v1.0 deploying to Hostinger with domain library.mutcu.org

**Questions?** Refer to the main [README.md](README.md) for general project information or contact Hostinger support.

**Last Updated**: March 2026
