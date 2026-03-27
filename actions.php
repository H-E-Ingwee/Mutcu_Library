<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

// Accept GET requests for AJAX specifically
$action = $_POST['action'] ?? $_GET['action'] ?? '';
$returnUrl = $_POST['return_url'] ?? 'home.php';
$currentUser = currentUser();

// Block arbitrary GET requests that are not AJAX endpoints
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !in_array($action, ['fetch_books', 'toggle_bookmark'])) {
    header('Location: index.php');
    exit;
}

// ==========================================
// AJAX ENDPOINTS (Returns JSON)
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
    processBooks($books); // Ensure symbolic covers apply to searched items
    
    echo json_encode($books);
    exit;
}

if ($action === 'toggle_bookmark') {
    header('Content-Type: application/json');
    if (!$currentUser) {
        echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
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

if ($action === 'login') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([trim($_POST['email'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($_POST['password'], $user['password'])) {
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
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
    $stmt->execute([trim($_POST['email'])]);
    if ($stmt->fetch()) {
        $_SESSION['flash_error'] = "Email is already registered.";
        header("Location: register.php"); exit;
    }
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "member")');
    if ($stmt->execute([trim($_POST['name']), trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT)])) {
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['flash_success'] = "Registration successful!";
        header("Location: home.php");
    }
    exit;
}

if ($action === 'logout') {
    session_destroy();
    header("Location: login.php"); exit;
}

// Admin Actions
if ($action === 'add_book' && isAdmin()) {
    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link) VALUES (?, ?, ?, ?, ?, ?)');
    $stmt->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['description']), trim($_POST['cover']), trim($_POST['drive_link'])]);
    $_SESSION['flash_success'] = "Book added."; header("Location: $returnUrl"); exit;
}

if ($action === 'edit_book' && isAdmin()) {
    $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, category=?, description=?, cover=?, drive_link=? WHERE id=?');
    $stmt->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['description']), trim($_POST['cover']), trim($_POST['drive_link']), (int)$_POST['book_id']]);
    $_SESSION['flash_success'] = "Book updated."; header("Location: $returnUrl"); exit;
}

if ($action === 'delete_book' && isAdmin()) {
    $pdo->prepare('DELETE FROM books WHERE id = ?')->execute([(int)$_POST['book_id']]);
    $_SESSION['flash_success'] = "Book deleted."; header("Location: $returnUrl"); exit;
}

if ($action === 'add_article' && isAdmin()) {
    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['abstract']), trim($_POST['link']), trim($_POST['date']), trim($_POST['read_time'])])) {
        $_SESSION['flash_success'] = "Article published.";
    } else {
        $_SESSION['flash_error'] = "Failed to publish article.";
    }
    header("Location: $returnUrl"); exit;
}

if ($action === 'edit_article' && isAdmin()) {
    $stmt = $pdo->prepare('UPDATE articles SET title=?, author=?, abstract=?, link=?, date=?, read_time=? WHERE id=?');
    $stmt->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['abstract']), trim($_POST['link']), trim($_POST['date']), trim($_POST['read_time']), (int)$_POST['article_id']]);
    $_SESSION['flash_success'] = "Article updated."; header("Location: $returnUrl"); exit;
}

if ($action === 'delete_article' && isAdmin()) {
    $pdo->prepare('DELETE FROM articles WHERE id = ?')->execute([(int)$_POST['article_id']]);
    $_SESSION['flash_success'] = "Article deleted."; header("Location: $returnUrl"); exit;
}

// Profile Actions
if ($action === 'update_profile' && $currentUser) {
    if (!empty($_POST['password'])) {
        $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?')->execute([trim($_POST['name']), trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT), $currentUser['id']]);
    } else {
        $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?')->execute([trim($_POST['name']), trim($_POST['email']), $currentUser['id']]);
    }
    $_SESSION['flash_success'] = "Profile updated."; header("Location: profile.php"); exit;
}

if ($action === 'update_goal' && $currentUser) {
    $pdo->prepare('UPDATE users SET reading_goal = ? WHERE id = ?')->execute([(int)$_POST['goal'], $currentUser['id']]);
    $_SESSION['flash_success'] = "Reading goal updated!"; header("Location: profile.php"); exit;
}

header("Location: index.php");
exit;