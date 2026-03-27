<?php
require_once __DIR__ . '/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: articles.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM articles WHERE id = ?');
$stmt->execute([$id]);
$article = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$article) {
    header('Location: articles.php');
    exit;
}

$user = currentUser();
$userId = $user ? $user['id'] : null;

// Log the view event for the user's history using the new tracking system
logEvent($userId, 'view', 'article', $id);

// Increment the total view count for the article
try {
    $pdo->prepare('UPDATE articles SET view_count = view_count + 1 WHERE id=?')->execute([$id]);
} catch (PDOException $e) {
    // Silently continue if the database column doesn't exist
}

header('Location: ' . $article['link']);
exit;