<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/db.php';

// --- DATABASE AUTO-FIXER ---
try {
    global $pdo;
    $pdo->exec("CREATE TABLE IF NOT EXISTS user_bookmarks (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, book_id INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY unique_bookmark (user_id, book_id))");
    $pdo->exec("CREATE TABLE IF NOT EXISTS events (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NULL, event_type VARCHAR(50) NOT NULL, target_type VARCHAR(50) NOT NULL, target_id INT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)");
    $pdo->exec("ALTER TABLE users ADD COLUMN IF NOT EXISTS reading_goal INT DEFAULT 0");
    // NEW: Ensure columns exist for advanced sorting and tracking
    $pdo->exec("ALTER TABLE books ADD COLUMN download_count INT DEFAULT 0");
    $pdo->exec("ALTER TABLE user_bookmarks ADD COLUMN status VARCHAR(20) DEFAULT 'to_read'");
} catch (PDOException $e) { }

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

function getSymbolicCover($category, $title) {
    $shortTitle = mb_strimwidth($title, 0, 30, "...");
    $urlEncodedTitle = urlencode($shortTitle);
    return "https://placehold.co/400x600/060B26/FF9800?font=Montserrat&text={$urlEncodedTitle}";
}

function processBooks(&$books) {
    foreach ($books as &$book) {
        $cover = trim($book['cover'] ?? '');
        $isValid = false;
        
        if (!empty($cover)) {
            if (filter_var($cover, FILTER_VALIDATE_URL)) {
                $isValid = true;
            } elseif (file_exists(__DIR__ . '/' . $cover)) {
                $isValid = true;
            }
        }
        if (!$isValid) {
            $book['cover'] = getSymbolicCover($book['category'], $book['title']);
        }
    }
    unset($book);
}

function getBooks($limit = null) {
    global $pdo;
    try {
        $sql = 'SELECT * FROM books ORDER BY id DESC';
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
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
        if ($limit) {
            $sql .= ' LIMIT ' . (int)$limit;
        }
        return $pdo->query($sql)->fetchAll(PDO::FETCH_ASSOC); 
    } 
    catch (PDOException $e) { return []; }
}

// NEW: Fetch all users for the Admin Panel
function getUsers() {
    global $pdo;
    try {
        // Fetches users and joins with the events table to find their most recent activity date
        $stmt = $pdo->query('
            SELECT u.*, 
                   (SELECT MAX(created_at) FROM events WHERE user_id = u.id) as last_active 
            FROM users u 
            ORDER BY u.created_at DESC
        ');
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
        $stmt = $pdo->prepare('SELECT b.* FROM books b JOIN user_bookmarks ub ON b.id = ub.book_id WHERE ub.user_id = ? ORDER BY ub.created_at DESC');
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
        
        // FIX: Generate dynamic, real rolling 7-day labels instead of hardcoded Mon-Sun
        $weekly = ['labels' => [], 'data' => []];
        for ($i = 6; $i >= 0; $i--) {
            $date = date('Y-m-d', strtotime("-$i days"));
            $weekly['labels'][] = date('D', strtotime($date)); // Output exact day e.g., 'Wed'
            $weekly['data'][] = (int)($data[$date] ?? 0);
        }
        return $weekly;
    } catch(PDOException $e) { 
        return ['labels' => ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'], 'data' => array_fill(0, 7, 0)]; 
    }
}