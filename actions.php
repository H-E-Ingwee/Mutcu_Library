<?php
require_once __DIR__ . '/functions.php';

$redirect = $_POST['return_url'] ?? $_GET['return_url'] ?? 'home.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'register') {
        $name = trim($_POST['name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($name && $email && $password && filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?');
            $stmt->execute([$email]);
            if (!$stmt->fetch()) {
                $hash = password_hash($password, PASSWORD_DEFAULT);
                $pdo->prepare('INSERT INTO users (name,email,password) VALUES (?,?,?)')->execute([$name,$email,$hash]);
                $_SESSION['flash_success'] = 'Registration successful. Login with your credentials.';
            } else {
                $_SESSION['flash_error'] = 'Email already registered.';
            }
        } else {
            $_SESSION['flash_error'] = 'Fill all fields with valid data.';
        }
    }

    if ($action === 'login') {
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user'] = $user;
            $_SESSION['flash_success'] = 'Welcome back, ' . $user['name'];
            if ($user['role'] === 'admin') {
                $redirect = 'admin.php';
            }
        } else {
            $_SESSION['flash_error'] = 'Invalid credentials.';
        }
    }

    if ($action === 'logout') {
        session_destroy();
        session_start();
        $_SESSION['flash_success'] = 'Logged out successfully.';
    }

    if ($action === 'add_book' && isAdmin()) {
        createBook(trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['description']), trim($_POST['cover']), trim($_POST['drive_link']));
        $_SESSION['flash_success'] = 'Book added successfully.';
    }

    if ($action === 'edit_book' && isAdmin()) {
        $id = (int) $_POST['book_id'];
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $category = trim($_POST['category']);
        $description = trim($_POST['description']);
        $cover = trim($_POST['cover']);
        $drive_link = trim($_POST['drive_link']);
        $stmt = $pdo->prepare('UPDATE books SET title = ?, author = ?, category = ?, description = ?, cover = ?, drive_link = ? WHERE id = ?');
        $stmt->execute([$title, $author, $category, $description, $cover, $drive_link, $id]);
        $_SESSION['flash_success'] = 'Book updated successfully.';
    }

    if ($action === 'edit_article' && isAdmin()) {
        $id = (int) $_POST['article_id'];
        $title = trim($_POST['title']);
        $author = trim($_POST['author']);
        $abstract = trim($_POST['abstract']);
        $link = trim($_POST['link']);
        $date = trim($_POST['date']);
        $read_time = trim($_POST['read_time']);
        $stmt = $pdo->prepare('UPDATE articles SET title = ?, author = ?, abstract = ?, link = ?, date = ?, read_time = ? WHERE id = ?');
        $stmt->execute([$title, $author, $abstract, $link, $date, $read_time, $id]);
        $_SESSION['flash_success'] = 'Article updated successfully.';
    }

    if ($action === 'delete_book' && isAdmin()) {
        deleteBook((int) $_POST['book_id']);
        $_SESSION['flash_success'] = 'Book removed.';
    }

    if ($action === 'delete_article' && isAdmin()) {
        deleteArticle((int) $_POST['article_id']);
        $_SESSION['flash_success'] = 'Article removed.';
    }

    if ($action === 'evaluate_books' && isAdmin()) {
        $stmt = $pdo->query('SELECT id FROM books WHERE doctrine_score IS NULL');
        $bookIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
        $evaluated = 0;
        foreach ($bookIds as $id) {
            if (evaluateBookDoctrinal($id)) {
                $evaluated++;
            }
            // Sleep to avoid rate limit
            sleep(1);
        }
        $_SESSION['flash_success'] = "Evaluated $evaluated books doctrinally.";
    }
}

header('Location: ' . $redirect);
exit;
