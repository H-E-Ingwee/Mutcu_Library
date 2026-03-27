<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// --- DATABASE AUTO-FIXER ---
// Automatically creates missing tables so profile.php and bookmarks don't crash
try {
    global $pdo;
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_bookmarks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        book_id INT NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        UNIQUE KEY unique_bookmark (user_id, book_id)
    )");
    
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NULL,
        event_type VARCHAR(50) NOT NULL,
        target_type VARCHAR(50) NOT NULL,
        target_id INT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )");
    
    // Add reading_goal column if it doesn't exist
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reading_goal INT DEFAULT 0");
} catch (PDOException $e) {
    // Continue silently if tables/columns already exist or permissions are restricted
}

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

// Generate high-quality Unsplash covers based on category if a book is missing a cover
function getSymbolicCover($category, $title) {
    $coverBanks = [
        'Faith' => [
            'https://images.unsplash.com/photo-1490127252417-7c393f993ee4?w=500&q=80',
            'https://images.unsplash.com/photo-1438232992991-995b7058bbb3?w=500&q=80',
            'https://images.unsplash.com/photo-1504052434569-70ad5836ab65?w=500&q=80'
        ],
        'Leadership' => [
            'https://images.unsplash.com/photo-1552664730-d307ca884978?w=500&q=80',
            'https://images.unsplash.com/photo-1519834785169-98be25ec3f84?w=500&q=80',
            'https://images.unsplash.com/photo-1522071820081-009f0129c71c?w=500&q=80'
        ],
        'Purpose' => [
            'https://images.unsplash.com/photo-1502086223501-7ea6ecd79368?w=500&q=80',
            'https://images.unsplash.com/photo-1499728603263-13726abce5fd?w=500&q=80',
            'https://images.unsplash.com/photo-1464982326199-86f32f81b211?w=500&q=80'
        ],
        'Relationships' => [
            'https://images.unsplash.com/photo-1529333166437-7750a6dd5a70?w=500&q=80',
            'https://images.unsplash.com/photo-1511895426328-dc8714191300?w=500&q=80',
            'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?w=500&q=80'
        ]
    ];

    $hash = crc32($title ?: 'default');
    $categoryArray = $coverBanks[$category] ?? $coverBanks['Faith'];
    $index = abs($hash) % count($categoryArray);
    
    return $categoryArray[$index];
}

function processBooks(&$books) {
    foreach ($books as &$book) {
        $cover = trim($book['cover'] ?? '');
        if (empty($cover) || strlen($cover) < 10 || !filter_var($cover, FILTER_VALIDATE_URL)) {
            $book['cover'] = getSymbolicCover($book['category'], $book['title']);
        }
    }
    unset($book);
}

function getBooks() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM books ORDER BY id DESC');
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    processBooks($books);
    return $books;
}

function getArticles() {
    global $pdo;
    return $pdo->query('SELECT * FROM articles ORDER BY id DESC')->fetchAll(PDO::FETCH_ASSOC);
}

function logEvent($userId, $eventType, $targetType, $targetId = null) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO events (user_id, event_type, target_type, target_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $eventType, $targetType, $targetId]);
}

function getUserBookmarks($userId) {
    global $pdo;
    $stmt = $pdo->prepare('SELECT b.* FROM books b JOIN user_bookmarks ub ON b.id = ub.book_id WHERE ub.user_id = ? ORDER BY ub.created_at DESC');
    $stmt->execute([$userId]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    processBooks($books);
    return $books;
}

function getUserReadingHistory($userId) {
    global $pdo;
    $stmt = $pdo->prepare('
        SELECT DISTINCT b.*, e.created_at as accessed_at 
        FROM events e 
        JOIN books b ON e.target_id = b.id 
        WHERE e.user_id = ? AND e.event_type = "download" AND e.target_type = "book" 
        ORDER BY e.created_at DESC LIMIT 10
    ');
    $stmt->execute([$userId]);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    processBooks($books);
    return $books;
}

function getStats() {
    global $pdo;
    return [
        'total_books' => $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn(),
        'total_articles' => $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn(),
        'total_users' => $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn(),
        'total_downloads' => $pdo->query('SELECT COUNT(*) FROM events WHERE event_type = "download"')->fetchColumn()
    ];
}

function getCategoryDistribution() {
    global $pdo;
    return $pdo->query('SELECT category, COUNT(*) as count FROM books GROUP BY category')->fetchAll(PDO::FETCH_ASSOC);
}

function getWeeklyInteractions() {
    global $pdo;
    $stmt = $pdo->query('
        SELECT DATE(created_at) as date, COUNT(*) as count 
        FROM events 
        WHERE created_at >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) 
        GROUP BY DATE(created_at) 
        ORDER BY date ASC
    ');
    $data = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    $weekly = [];
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $weekly[] = $data[$date] ?? 0;
    }
    return $weekly;
}