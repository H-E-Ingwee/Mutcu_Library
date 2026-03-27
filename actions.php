<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$action = $_POST['action'] ?? '';
$returnUrl = $_POST['return_url'] ?? 'home.php';
$currentUser = currentUser();

// --- Authentication Actions ---
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
    
    // Simple check if email exists
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
        $_SESSION['flash_success'] = "Registration successful! Welcome to MUTCU E-Library.";
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

// --- User Profile Actions ---
if ($action === 'update_profile' && $currentUser) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (!empty($password)) {
        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ?, password = ? WHERE id = ?');
        $stmt->execute([$name, $email, $hashed, $currentUser['id']]);
    } else {
        $stmt = $pdo->prepare('UPDATE users SET name = ?, email = ? WHERE id = ?');
        $stmt->execute([$name, $email, $currentUser['id']]);
    }
    
    $_SESSION['flash_success'] = "Profile updated successfully.";
    header("Location: profile.php");
    exit;
}

if ($action === 'update_goal' && $currentUser) {
    $goal = (int)$_POST['goal'];
    $stmt = $pdo->prepare('UPDATE users SET reading_goal = ? WHERE id = ?');
    $stmt->execute([$goal, $currentUser['id']]);
    $_SESSION['flash_success'] = "Reading goal updated!";
    header("Location: profile.php");
    exit;
}

// --- Admin Actions ---
if ($action === 'add_book' && isAdmin()) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $cover = trim($_POST['cover']); // Can be empty, functions.php will handle dynamic covers
    $drive_link = trim($_POST['drive_link']);

    $stmt = $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt->execute([$title, $author, $category, $description, $cover, $drive_link])) {
        $_SESSION['flash_success'] = "Book added to catalog.";
    } else {
        $_SESSION['flash_error'] = "Failed to add book.";
    }
    header("Location: $returnUrl");
    exit;
}

if ($action === 'edit_book' && isAdmin()) {
    $id = (int)$_POST['book_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $category = trim($_POST['category']);
    $description = trim($_POST['description']);
    $cover = trim($_POST['cover']);
    $drive_link = trim($_POST['drive_link']);

    $stmt = $pdo->prepare('UPDATE books SET title=?, author=?, category=?, description=?, cover=?, drive_link=? WHERE id=?');
    if ($stmt->execute([$title, $author, $category, $description, $cover, $drive_link, $id])) {
        $_SESSION['flash_success'] = "Book updated successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to update book.";
    }
    header("Location: $returnUrl");
    exit;
}

if ($action === 'delete_book' && isAdmin()) {
    $id = (int)$_POST['book_id'];
    $stmt = $pdo->prepare('DELETE FROM books WHERE id = ?');
    if ($stmt->execute([$id])) {
        $_SESSION['flash_success'] = "Book removed from catalog.";
    } else {
        $_SESSION['flash_error'] = "Failed to delete book.";
    }
    header("Location: $returnUrl");
    exit;
}

// Fixed Article Actions
if ($action === 'add_article' && isAdmin()) {
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $abstract = trim($_POST['abstract']);
    $link = trim($_POST['link']);
    $date = trim($_POST['date']);
    $read_time = trim($_POST['read_time']);

    $stmt = $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time) VALUES (?, ?, ?, ?, ?, ?)');
    if ($stmt->execute([$title, $author, $abstract, $link, $date, $read_time])) {
        $_SESSION['flash_success'] = "Article added successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to add article.";
    }
    header("Location: $returnUrl");
    exit;
}

if ($action === 'edit_article' && isAdmin()) {
    $id = (int)$_POST['article_id'];
    $title = trim($_POST['title']);
    $author = trim($_POST['author']);
    $abstract = trim($_POST['abstract']);
    $link = trim($_POST['link']);
    $date = trim($_POST['date']);
    $read_time = trim($_POST['read_time']);

    $stmt = $pdo->prepare('UPDATE articles SET title=?, author=?, abstract=?, link=?, date=?, read_time=? WHERE id=?');
    if ($stmt->execute([$title, $author, $abstract, $link, $date, $read_time, $id])) {
        $_SESSION['flash_success'] = "Article updated successfully.";
    } else {
        $_SESSION['flash_error'] = "Failed to update article.";
    }
    header("Location: $returnUrl");
    exit;
}

if ($action === 'delete_article' && isAdmin()) {
    $id = (int)$_POST['article_id'];
    $stmt = $pdo->prepare('DELETE FROM articles WHERE id = ?');
    if ($stmt->execute([$id])) {
        $_SESSION['flash_success'] = "Article removed.";
    }
    header("Location: $returnUrl");
    exit;
}

header("Location: home.php");
exit;