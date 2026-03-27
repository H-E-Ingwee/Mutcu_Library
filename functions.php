<?php
// 1. Force Error Reporting for debugging on Hostinger
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if (session_status() === PHP_SESSION_NONE) {
    if (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') {
        session_set_cookie_params(['samesite' => 'Lax', 'secure' => true]);
    }
    session_start();
}

// Load API Configuration
if (file_exists(__DIR__ . '/config.php')) {
    require_once __DIR__ . '/config.php';
}

require_once __DIR__ . '/db.php';

// --- CSRF SECURITY ---
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
function csrf_token() { return $_SESSION['csrf_token']; }
function verify_csrf() {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        $_SESSION['flash_error'] = "Security session expired. Please refresh.";
        header('Location: home.php'); exit;
    }
}

// --- MASTER DATABASE AUTO-FIXER & CONTENT SEEDER ---
try {
    global $pdo;

    // 1. Create Tables if they don't exist
    $pdo->exec("CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(255) NOT NULL, email VARCHAR(255) UNIQUE NOT NULL, password VARCHAR(255) NOT NULL, role ENUM('member', 'admin') DEFAULT 'member', reading_goal INT DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS books (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, category VARCHAR(100) NOT NULL, description TEXT, cover VARCHAR(255), drive_link TEXT NOT NULL, download_count INT DEFAULT 0, is_featured TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS articles (id INT AUTO_INCREMENT PRIMARY KEY, title VARCHAR(255) NOT NULL, author VARCHAR(255) NOT NULL, abstract TEXT, link TEXT NOT NULL, date VARCHAR(50), read_time VARCHAR(50), view_count INT DEFAULT 0, is_featured TINYINT(1) DEFAULT 0, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_bookmarks (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, book_id INT NOT NULL, status VARCHAR(20) DEFAULT 'to_read', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_bookmark (user_id, book_id)) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, event_type VARCHAR(50) NOT NULL, target_type VARCHAR(50) NOT NULL, target_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;");

    // --- SUPER ADMIN AUTO-SETUP ---
    $superAdmins = [
        ['name' => 'Brian Ingwee', 'email' => 'Ingweplex@gmail.com', 'password' => 'Ingweplex'],
        ['name' => 'Natasha Amani', 'email' => 'MutcuSec@gmail.com', 'password' => 'MutcuSec@2026']
    ];
    foreach ($superAdmins as $sa) {
        $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
        $stmt->execute([$sa['email']]);
        if (!$stmt->fetch()) {
            $insertStmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "admin")');
            $insertStmt->execute([$sa['name'], $sa['email'], password_hash($sa['password'], PASSWORD_DEFAULT)]);
        }
    }

    // --- INITIAL CONTENT SEEDER (35 BOOKS) ---
    $bookCount = $pdo->query("SELECT COUNT(*) FROM books")->fetchColumn();
    if ($bookCount < 10) {
        $pdo->exec("DELETE FROM books"); // Clear old data to ensure clean IDs
        $initialBooks = [
            ['title' => 'A Promise Kept', 'author' => 'Robin Lee Hatcher', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=1trZUZ2RbKoHEJeXNvkBAJP6o-AUBqL86', 'desc' => 'A story of faith, marriage, and keeping ones word.', 'feat' => 1],
            ['title' => 'The Assignment Vol 1: The Dream & The Destiny', 'author' => 'Mike Murdock', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1PfPPsRm8bI3jHlZuuJWgtv2n3hG-fAe9', 'desc' => 'Understanding your divine assignment and the path to your destiny.', 'feat' => 1],
            ['title' => 'Becoming a Prayer Warrior', 'author' => 'Elizabeth Alves', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=17-gJ2MuyyPUTDip28eoHCVCEbxrE05DE', 'desc' => 'A comprehensive guide to effective, fervent prayer.', 'feat' => 1],
            ['title' => 'Sex Is Not the Problem (Lust Is)', 'author' => 'Joshua Harris', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=1JUVJ65VVo-av0kImNuLS2tEzmiMfrTkt', 'desc' => 'Practical wisdom for maintaining purity and overcoming lust.', 'feat' => 1],
            ['title' => 'The Great Digital Commission', 'author' => 'Unknown', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1-wyJ2CXNf5WSsuxXHCiCRGGML7E7CQ2r', 'desc' => 'Leveraging digital platforms for the spread of the Gospel.', 'feat' => 0],
            ['title' => 'In Pursuit of Purpose', 'author' => 'Myles Munroe', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1PtkJc-VPXEZoThONNX1_jeAEEHl0ZdDa', 'desc' => 'Discovering the secret to a meaningful and fulfilled life.', 'feat' => 1],
            ['title' => 'Understanding Spiritual Gifts', 'author' => 'Kay Arthur', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1hHcLy6zIS09j9UkPcO4zo1G2TleqjJUs', 'desc' => 'An inductive study on discovering and using your spiritual gifts.', 'feat' => 0],
            ['title' => 'The Sex Trap', 'author' => 'Mike Murdock', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=1hLoVFzVm_2S8DyXZ4aPpclsSXCKINuFb', 'desc' => 'Wisdom for navigating the complexities of relationships and purity.', 'feat' => 0],
            ['title' => 'The Purpose-Driven Life', 'author' => 'Rick Warren', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1irc7DKqg5857nzimBchQxfZV4UVdSN5r', 'desc' => 'What on earth am I here for? A 40-day spiritual journey.', 'feat' => 1],
            ['title' => 'The Normal Christian Life', 'author' => 'Watchman Nee', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1tBSBDiIKCZBunQeViywNNH3MLRIBWscH', 'desc' => 'An exposition on the basics of Christian living and spiritual growth.', 'feat' => 0],
            ['title' => 'Running with the Giants', 'author' => 'John C. Maxwell', 'cat' => 'Leadership', 'link' => 'https://drive.google.com/open?id=1KQeQ14XfhRKxccPFtt3tFNWumH8S1yTr', 'desc' => 'Leadership lessons from the great heroes of faith in the Bible.', 'feat' => 1],
            ['title' => 'Relationships 101', 'author' => 'John C. Maxwell', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=1uEpHqp1vgZHmOF-z46S3WpGRE6CsC5LC', 'desc' => 'What every leader needs to know about building healthy relationships.', 'feat' => 0],
            ['title' => 'Everyone Communicates, Few Connect', 'author' => 'John C. Maxwell', 'cat' => 'Leadership', 'link' => 'https://drive.google.com/open?id=1jgG4HdCK7g9VfIuvXrlosJA3mDYEhpmv', 'desc' => 'Mastering the art of connecting with others for greater influence.', 'feat' => 0],
            ['title' => 'Grace Is Greater', 'author' => 'Kyle Idleman', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1bSyheukdeNtLaFHaMtLyLeJ7dlS1Q17P', 'desc' => 'Discovering that Gods grace is greater than your past and mistakes.', 'feat' => 1],
            ['title' => 'Chasing Contentment', 'author' => 'Erik Raymond', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=17y7tVeUv_R00L4wPsgWgQM_Hn2WjFNn0', 'desc' => 'Finding true peace and contentment in the Gospel.', 'feat' => 0],
            ['title' => 'The Power of Christian Contentment', 'author' => 'Andrew Davis', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1rLoQI6rynfwmJeJvw4OLpntjc9bmJPr-', 'desc' => 'How to maintain a satisfied soul in a world of constant wanting.', 'feat' => 0],
            ['title' => 'Jesus CEO', 'author' => 'Laurie Beth Jones', 'cat' => 'Leadership', 'link' => 'https://drive.google.com/open?id=13sPBTYTLifJyzVUX1hF1CzHpjhDfJnpo', 'desc' => 'Using ancient wisdom for visionary and effective leadership.', 'feat' => 0],
            ['title' => 'Relationship VS Fellowship', 'author' => 'Unknown', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=11hJddeFmd-0tkOnoCJiubQwTlOcACZSY', 'desc' => 'Understanding the difference between our standing and our walk with God.', 'feat' => 0],
            ['title' => 'GloboChrist', 'author' => 'Carl Raschke', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1eQYlpnkDybgqkAwmrSMvShM7zapH7A88', 'desc' => 'The Great Commission in a postmodern and globalized world.', 'feat' => 0],
            ['title' => 'Exploring The Riches Of Redemption', 'author' => 'Unknown', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1W7oP8YDaJSvTbD0a2TXe0qSkvhALzFv-', 'desc' => 'Diving deep into the benefits and realities of our redemption in Christ.', 'feat' => 0],
            ['title' => 'All of Grace', 'author' => 'Charles Spurgeon', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1j7xOz9OWT902QpwoQb3ceG9Uy4K1KEZG', 'desc' => 'An earnest word with those who are seeking salvation by the Lord Jesus Christ.', 'feat' => 0],
            ['title' => 'Anxious for Nothing', 'author' => 'Max Lucado', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1N1cUI6qcjFg61aC5YQUXCbNsdXjJcORo', 'desc' => 'Gods cure for the cares of your soul based on Philippians 4.', 'feat' => 0],
            ['title' => 'The Power of Your Potential', 'author' => 'John C. Maxwell', 'cat' => 'Leadership', 'link' => 'https://drive.google.com/open?id=1cYPYSPQWzzexqy0My5H0sMUU41YJvlpt', 'desc' => 'How to break through your limits and reach your full potential.', 'feat' => 0],
            ['title' => 'Living the Cross Centered Life', 'author' => 'C.J. Mahaney', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1bFQQO91wsJy7aeKj0vXAUjOnaehdjQjZ', 'desc' => 'Keeping the main thing—the Gospel—the main thing in your daily life.', 'feat' => 0],
            ['title' => 'The Proverbs 31 Woman', 'author' => 'Mike Murdock', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=17QjHHomc4FnRzDtoxeFeOXZtmFksxO8F', 'desc' => 'A study on the characteristics of the virtuous woman.', 'feat' => 0],
            ['title' => 'Finding Your Purpose in Life', 'author' => 'Mike Murdock', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1G5bVIeo6Y3ZC4Yv1MOzcgWbeV69mdgeG', 'desc' => 'Simple keys to identifying and fulfilling your divine purpose.', 'feat' => 0],
            ['title' => 'How Successful People Think', 'author' => 'John C. Maxwell', 'cat' => 'Leadership', 'link' => 'https://drive.google.com/open?id=1_iLyCpr3eD8djvQcXa7SjTjhVcm89CwS', 'desc' => 'Changing your thinking to change your life and leadership.', 'feat' => 0],
            ['title' => 'Grace: The DNA of God', 'author' => 'Tony Cooke', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1doWsSI_73OgqO_KhvuBFWkKqrux3G9EH', 'desc' => 'Understanding the core nature of Gods amazing grace.', 'feat' => 0],
            ['title' => 'Good Leaders Ask Great Questions', 'author' => 'John C. Maxwell', 'cat' => 'Leadership', 'link' => 'https://drive.google.com/open?id=1w-4dpI0KG5hXzE3un2VsP2K5s1WyR3SN', 'desc' => 'Your foundation for successful leadership starts with asking.', 'feat' => 0],
            ['title' => 'New Creation Realities', 'author' => 'E.W. Kenyon', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=195E9vlgYKAlshyEACYkYaRcVspvYbKbA', 'desc' => 'A revelation of the believers identification with Christ.', 'feat' => 0],
            ['title' => 'Redemption Accomplished and Applied', 'author' => 'John Murray', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1cMdkZPqM2yJffQIvuXqOjq7NyKCmwwgG', 'desc' => 'A theological look at the work of Christ and its application to us.', 'feat' => 0],
            ['title' => 'Healing the Scars of Emotional Abuse', 'author' => 'Gregory L. Jantz', 'cat' => 'Relationships', 'link' => 'https://drive.google.com/open?id=1jEVJwANEwjPDbb2_2QkR2srs1c9QBLgQ', 'desc' => 'Finding hope and healing from the wounds of the past.', 'feat' => 0],
            ['title' => 'The New Man Seminar Workbook', 'author' => 'Unknown', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1JH0Zu_Y6Y81OTfsHiiQEe4OSkCWLYEVJ', 'desc' => 'A study guide on the nature and lifestyle of the new creation in Christ.', 'feat' => 0],
            ['title' => 'The Reality Of Sonship', 'author' => 'Curry R. Blake', 'cat' => 'Faith', 'link' => 'https://drive.google.com/open?id=1zHY7DhV82-VC1mn2-9U5vXCBZReWIN2L', 'desc' => 'Living in the full authority and reality of being a child of God.', 'feat' => 0],
            ['title' => 'What is the Great Commission?', 'author' => 'R.C. Sproul', 'cat' => 'Purpose', 'link' => 'https://drive.google.com/open?id=1-PROrcHteH4Qq2QGlSojuT9C8YL7Q1sJ', 'desc' => 'A brief overview of the mandate given by Christ to the Church.', 'feat' => 0]
        ];

        $insertBook = $pdo->prepare("INSERT INTO books (title, author, category, description, drive_link, is_featured) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($initialBooks as $b) {
            $insertBook->execute([$b['title'], $b['author'], $b['cat'], $b['desc'], $b['link'], $b['feat']]);
        }
    }

} catch (PDOException $e) { 
    error_log("Schema Error: " . $e->getMessage()); 
}

// --- CORE HELPER FUNCTIONS ---
function currentUser() {
    global $pdo;
    if (!isset($_SESSION['user_id'])) return null;
    $stmt = $pdo->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function isAdmin() {
    $user = currentUser();
    return $user && $user['role'] === 'admin';
}

// Helper to check if a specific book is already in a user's library
function isBookmarked($userId, $bookId) {
    global $pdo;
    if (!$userId) return false;
    $stmt = $pdo->prepare('SELECT id FROM user_bookmarks WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$userId, $bookId]);
    return (bool)$stmt->fetch();
}

function getSymbolicCover($category, $title) {
    $shortTitle = mb_strimwidth($title, 0, 30, "...");
    return "https://placehold.co/400x600/060B26/FF9800?font=Montserrat&text=".urlencode($shortTitle);
}

function processBooks(&$books) {
    $user = currentUser();
    $userId = $user ? $user['id'] : null;

    foreach ($books as &$book) {
        $cover = trim($book['cover'] ?? '');
        $isValid = false;
        if (!empty($cover) && !str_contains($cover, 'placehold')) {
            if (filter_var($cover, FILTER_VALIDATE_URL) || file_exists(__DIR__ . '/' . $cover)) {
                $isValid = true;
            }
        }
        if (!$isValid) $book['cover'] = getSymbolicCover($book['category'], $book['title']);
        
        // Attach bookmark status to the book object for the UI
        $book['is_saved'] = isBookmarked($userId, $book['id']);
    }
    unset($book);
}

function getBooks($limit = null) {
    global $pdo;
    try {
        $sql = 'SELECT * FROM books ORDER BY id DESC';
        if ($limit) $sql .= ' LIMIT ' . (int)$limit;
        $stmt = $pdo->query($sql);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        processBooks($books);
        return $books;
    } catch (PDOException $e) { return []; }
}

function getArticles($limit = null) {
    global $pdo;
    try { 
        $sql = 'SELECT * FROM articles ORDER BY id DESC';
        if ($limit) $sql .= ' LIMIT ' . (int)$limit;
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); 
    } catch (PDOException $e) { return []; }
}

function getFeaturedBooks($limit = 4) {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM books ORDER BY is_featured DESC, id DESC LIMIT " . (int)$limit);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        processBooks($books);
        return $books;
    } catch (PDOException $e) { return []; }
}

function getFeaturedArticles($limit = 3) {
    global $pdo;
    try {
        $stmt = $pdo->query("SELECT * FROM articles ORDER BY is_featured DESC, date DESC LIMIT " . (int)$limit);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { return []; }
}

function getUsers() {
    global $pdo;
    try {
        $stmt = $pdo->query('SELECT u.*, (SELECT MAX(created_at) FROM events WHERE user_id = u.id) as last_active FROM users u ORDER BY u.created_at DESC');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) { return []; }
}

function logEvent($userId, $eventType, $targetType, $targetId = null) {
    global $pdo;
    try {
        $stmt = $pdo->prepare('INSERT INTO events (user_id, event_type, target_type, target_id) VALUES (?, ?, ?, ?)');
        $stmt->execute([$userId, $eventType, $targetType, $targetId]);
    } catch (PDOException $e) { }
}

function getUserBookmarks($userId) {
    global $pdo;
    try {
        // Query refined to ensure exact matching between bookmarks and existing books
        $stmt = $pdo->prepare('
            SELECT b.*, ub.status as read_status 
            FROM books b 
            INNER JOIN user_bookmarks ub ON b.id = ub.book_id 
            WHERE ub.user_id = ? 
            ORDER BY ub.created_at DESC
        ');
        $stmt->execute([$userId]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        processBooks($books);
        return $books;
    } catch (PDOException $e) { return []; }
}

function getUserReadingHistory($userId) {
    global $pdo;
    try {
        $stmt = $pdo->prepare('SELECT DISTINCT b.*, e.created_at as accessed_at FROM events e JOIN books b ON e.target_id = b.id WHERE e.user_id = ? AND e.event_type = "download" AND e.target_type = "book" ORDER BY e.created_at DESC LIMIT 10');
        $stmt->execute([$userId]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        processBooks($books);
        return $books;
    } catch (PDOException $e) { return []; }
}

function getStats() {
    global $pdo;
    try {
        return [
            'total_books' => $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
            'total_articles' => $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
            'total_users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
            'total_downloads' => $pdo->query('SELECT COUNT(*) FROM events WHERE event_type = "download"')->fetchColumn()
        ];
    } catch (PDOException $e) {
        return ['total_books' => 0, 'total_articles' => 0, 'total_users' => 0, 'total_downloads' => 0];
    }
}

function getCategoryDistribution() {
    global $pdo;
    try { return $pdo->query('SELECT category, COUNT(*) as count FROM books GROUP BY category')->fetchAll(PDO::FETCH_ASSOC); } 
    catch (PDOException $e) { return []; }
}

function getWeeklyInteractions() {
    global $pdo;
    try {
        $stmt = $pdo->query('SELECT DATE(created_at) as date, COUNT(*) as count FROM events WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 6 DAY) GROUP BY DATE(created_at) ORDER BY date ASC');
        $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
        $weekly = ['labels' => [], 'data' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $weekly['labels'][] = date('D', strtotime($date));
            $weekly['data'][] = (int)($data[$date] ?? 0);
        }
        return $weekly;
    } catch(PDOException $e) { return ['labels' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], 'data' => array_fill(0, 7, 0)]; }
}

function callOpenAI($prompt, $systemInstruction = "You are a helpful assistant.") {
    if (!defined('OPENAI_API_KEY') || empty(OPENAI_API_KEY)) return "Error: API Key Missing";
    $url = 'https://api.openai.com/v1/chat/completions';
    $data = ['model' => 'gpt-3.5-turbo', 'messages' => [['role' => 'system', 'content' => $systemInstruction], ['role' => 'user', 'content' => $prompt]], 'temperature' => 0.7];
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json', 'Authorization: Bearer ' . OPENAI_API_KEY]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); 
    $response = curl_exec($ch);
    $result = json_decode($response, true);
    return $result['choices'][0]['message']['content'] ?? "AI Error";
}