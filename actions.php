<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// FIX: Allow GET requests ONLY for our specific AJAX endpoints (fetch_books & toggle_bookmark)
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$returnUrl = $_POST['return_url'] ?? 'home.php';
$currentUser = currentUser();

if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !in_array($action, ['fetch_books', 'toggle_bookmark'])) {
    header('Location: index.php');
    exit;
}

// ==========================================
// AJAX ENDPOINTS (Filters & Bookmarks)
// ==========================================

if ($action === 'fetch_books') {
    header('Content-Type: application/json');
    $q = trim($_GET['q'] ?? '');
    $category = trim($_GET['category'] ?? 'All');
    
    $sql = 'SELECT * FROM books WHERE 1=1';
    $params = [];
    
    if ($category !== 'All') {
        $sql .= ' AND category = ?';
        $params[] = $category;
    }
    if ($q !== '') {
        $sql .= ' AND (title LIKE ? OR author LIKE ?)';
        $params[] = "%$q%";
        $params[] = "%$q%";
    }
    $sql .= ' ORDER BY id DESC';
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Inject dynamic Unsplash covers if missing
    foreach ($books as &$book) {
        if (empty($book['cover']) || !filter_var($book['cover'], FILTER_VALIDATE_URL)) {
            $book['cover'] = getSymbolicCover($book['category'], $book['title']);
        }
    }
    
    echo json_encode($books);
    exit;
}

if ($action === 'toggle_bookmark') {
    header('Content-Type: application/json');
    if (!$currentUser) {
        echo json_encode(['error' => 'Not logged in']);
        exit;
    }
    $book_id = (int)($_GET['book_id'] ?? 0);
    $user_id = $currentUser['id'];
    
    $stmt = $pdo->prepare('SELECT id FROM user_bookmarks WHERE user_id = ? AND book_id = ?');
    $stmt->execute([$user_id, $book_id]);
    if ($stmt->fetch()) {
        $pdo->prepare('DELETE FROM user_bookmarks WHERE user_id = ? AND book_id = ?')->execute([$user_id, $book_id]);
        echo json_encode(['status' => 'removed']);
    } else {
        $pdo->prepare('INSERT INTO user_bookmarks (user_id, book_id) VALUES (?, ?)')->execute([$user_id, $book_id]);
        echo json_encode(['status' => 'added']);
    }
    exit;
}


// ==========================================
// FORM SUBMISSIONS
// ==========================================

// --- Authentication ---
if ($action === 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['flash_success'] = "Welcome back, " . $user['name'] . "!";
        header("Location: $returnUrl");
    } else {
        $_SESSION['flash_error'] = "Invalid email or password.";
        header("Location: login.php");
    }
    exit;
}

if ($action === 'register') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        $_SESSION['flash_error'] = "Email is already registered.";
        header("Location: register.php");
        exit;
    }

    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "member")');
    if ($stmt->execute([$name, $email, $password])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['flash_success'] = "Registration successful! Welcome.";
        header("Location: home.php");
    } else {
        $_SESSION['flash_error'] = "Registration failed. Please try again.";
        header("Location: register.php");
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header("Location: login.php");
    exit;
}

// --- Admin Actions ---
if ($action === 'add_book' && isAdmin()) {
    $title = trim($_POST['title']); $author = trim($_POST['author']); $category = trim($_POST['category']);
    $description = trim($_POST['description']); $cover = trim($_POST['cover']); $drive_link = trim($_POST['drive_link']);
    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([$title, $author, $category, $description, $cover, $drive_link]);
    $_SESSION['flash_success'] = "Book added.";
    header("Location: $returnUrl"); exit;
}

if ($action === 'edit_book' && isAdmin()) {
    $id = (int)$_POST['book_id'];
    $title = trim($_POST['title']); $author = trim($_POST['author']); $category = trim($_POST['category']);
    $description = trim($_POST['description']); $cover = trim($_POST['cover']); $drive_link = trim($_POST['drive_link']);
    $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, category=?, description=?, cover=?, drive_link=? WHERE id=?');
    $stmt->execute([$title, $author, $category, $description, $cover, $drive_link, $id]);
    $_SESSION['flash_success'] = "Book updated.";
    header("Location: $returnUrl"); exit;
}

if ($action === 'delete_book' && isAdmin()) {
    $pdo->prepare('DELETE FROM books WHERE id = ?')->execute([(int)$_POST['book_id']]);
    $_SESSION['flash_success'] = "Book deleted.";
    header("Location: $returnUrl"); exit;
}

// FIX: Ensure Articles are saved correctly
if ($action === 'add_article' && isAdmin()) {
    $title = trim($_POST['title']); $author = trim($_POST['author']); $abstract = trim($_POST['abstract']);
    $link = trim($_POST['link']); $date = trim($_POST['date']); $read_time = trim($_POST['read_time']);
    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt->execute([$title, $author, $abstract, $link, $date, $read_time])) {
        $_SESSION['flash_success'] = "Article published.";
    } else {
        $_SESSION['flash_error'] = "Failed to add article.";
    }
    header("Location: $returnUrl"); exit;
}

if ($action === 'edit_article' && isAdmin()) {
    $id = (int)$_POST['article_id'];
    $title = trim($_POST['title']); $author = trim($_POST['author']); $abstract = trim($_POST['abstract']);
    $link = trim($_POST['link']); $date = trim($_POST['date']); $read_time = trim($_POST['read_time']);
    $stmt = $pdo->prepare('UPDATE articles SET title=?, author=?, abstract=?, link=?, date=?, read_time=? WHERE id=?');
    $stmt->execute([$title, $author, $abstract, $link, $date, $read_time, $id]);
    $_SESSION['flash_success'] = "Article updated.";
    header("Location: $returnUrl"); exit;
}

if ($action === 'delete_article' && isAdmin()) {
    $pdo->prepare('DELETE FROM articles WHERE id = ?')->execute([(int)$_POST['article_id']]);
    $_SESSION['flash_success'] = "Article deleted.";
    header("Location: $returnUrl"); exit;
}

// --- Profile Actions ---
if ($action === 'update_profile' && $currentUser) {
    $name = trim($_POST['name']); $email = trim($_POST['email']); $password = $_POST['password'];
    if (!empty($password)) {
        $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?')->execute([$name, $email, password_hash($password, PASSWORD_DEFAULT), $currentUser['id']]);
    } else {
        $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([$name, $email, $currentUser['id']]);
    }
    $_SESSION['flash_success'] = "Profile updated.";
    header("Location: profile.php"); exit;
}

if ($action === 'update_goal' && $currentUser) {
    $pdo->prepare('UPDATE users SET reading_goal = ? WHERE id = ?')->execute([(int)$_POST['goal'], $currentUser['id']]);
    $_SESSION['flash_success'] = "Reading goal updated!";
    header("Location: profile.php"); exit;
}

header("Location: home.php");
exit;