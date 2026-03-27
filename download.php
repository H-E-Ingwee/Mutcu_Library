<?php
require_once __DIR__ . '/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: library.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM books WHERE id = ?');
$stmt->execute([$id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    header('Location: library.php');
    exit;
}

$user = currentUser();
$userId = $user ? $user['id'] : null;

// Log the download event for the user's history using the new tracking system
logEvent($userId, 'download', 'book', $id);

// Increment the total download count for the book
try {
    $pdo->prepare('UPDATE books SET download_count = download_count + 1 WHERE id=?')->execute([$id]);
} catch (PDOException $e) {
    // Silently continue if the database column doesn't exist
}

header('Location: ' . $book['drive_link']);
exit;