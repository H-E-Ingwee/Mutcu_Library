<?php
require_once __DIR__ . '/db.php';

session_start();

function currentUser() {
    return isset($_SESSION['user']) ? $_SESSION['user'] : null;
}

function isAdmin() {
    $u = currentUser();
    return $u && isset($u['role']) && $u['role'] === 'admin';
}

function getBooks() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM books ORDER BY id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getArticles() {
    global $pdo;
    $stmt = $pdo->query('SELECT * FROM articles ORDER BY id DESC');
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function createBook($title, $author, $category, $description, $cover, $drive_link) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link, added_by) VALUES (?, ?, ?, ?, ?, ?, ?);');
    $stmt->execute([$title, $author, $category, $description, $cover, $drive_link, currentUser()['id'] ?? null]);
}

function createArticle($title, $author, $abstract, $link, $date, $read_time) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time, added_by) VALUES (?, ?, ?, ?, ?, ?, ?);');
    $stmt->execute([$title, $author, $abstract, $link, $date, $read_time, currentUser()['id'] ?? null]);
}

function deleteBook($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM books WHERE id=?');
    $stmt->execute([$id]);
}

function deleteArticle($id) {
    global $pdo;
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id=?');
    $stmt->execute([$id]);
}

function trackEvent($userId, $eventType, $targetType, $targetId) {
    global $pdo;
    $stmt = $pdo->prepare('INSERT INTO events (user_id, event_type, target_type, target_id) VALUES (?, ?, ?, ?)');
    $stmt->execute([$userId, $eventType, $targetType, $targetId]);
}

function addBookView($id, $userId = null) {
    global $pdo;
    $pdo->prepare('UPDATE books SET view_count = view_count + 1 WHERE id=?')->execute([$id]);
    trackEvent($userId, 'view', 'book', $id);
}

function addArticleView($id, $userId = null) {
    global $pdo;
    $pdo->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id=?')->execute([$id]);
    trackEvent($userId, 'view', 'article', $id);
}

function addBookDownload($id, $userId = null) {
    global $pdo;
    $pdo->prepare('UPDATE books SET download_count = download_count + 1 WHERE id=?')->execute([$id]);
    trackEvent($userId, 'download', 'book', $id);
}

function getStats() {
    global $pdo;
    $stats = [];
    $stats['total_books'] = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
    $stats['total_articles'] = $pdo->query('SELECT COUNT(*) FROM articles')->fetchColumn();
    $stats['total_users'] = $pdo->query('SELECT COUNT(*) FROM users')->fetchColumn();
    $stats['total_downloads'] = $pdo->query('SELECT SUM(download_count) FROM books')->fetchColumn();
    return $stats;
}

function ensureSampleData() {
    global $pdo;
    $count = $pdo->query('SELECT COUNT(*) FROM books')->fetchColumn();
    if ($count > 0) return;

    $sampleBooks = [
        ['Spiritual Leadership','J. Oswald Sanders','Leadership','Principles of excellence for every believer focusing on spiritual growth.','https://images.unsplash.com/photo-1544947950-fa07a98d237f?auto=format&fit=crop&q=80&w=400','#mock-drive-link-1'],
        ['Knowing God','J.I. Packer','Faith','A classic work on the nature and character of God, deepening theological roots.','https://images.unsplash.com/photo-1589829085413-56de8ae18c73?auto=format&fit=crop&q=80&w=400','#mock-drive-link-2'],
        ['The Purpose Driven Life','Rick Warren','Purpose','What on earth am I here for? Discovering your calling and spiritual path.','https://images.unsplash.com/photo-1491841550275-ad7854e35ca6?auto=format&fit=crop&q=80&w=400','#mock-drive-link-3'],
        ['The Meaning of Marriage','Timothy Keller','Relationships','Facing the complexities of commitment with the profound wisdom of God.','https://images.unsplash.com/photo-1532012197267-da84d127e765?auto=format&fit=crop&q=80&w=400','#mock-drive-link-4'],
    ];

    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link) VALUES (?,?,?,?,?,?)');
    foreach ($sampleBooks as $b) { $stmt->execute($b); }

    $sampleArticles = [
        ['The Joy of the Lord is Your Strength','John Piper','An exposition on Nehemiah 8 and how finding joy in God is the fuel for Christian living.','#article-link','Oct 12, 2025','5 min read'],
        ['Christian Leadership in the 21st Century','Albert Mohler','Challenges and opportunities for young leaders navigating a secularizing culture.','#article-link','Nov 05, 2025','8 min read']
    ];
    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time) VALUES (?,?,?,?,?,?)');
    foreach ($sampleArticles as $a) { $stmt->execute($a); }
}

ensureSampleData();
