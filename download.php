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
addBookDownload($id, $user['id'] ?? null);
header('Location: ' . $book['drive_link']);
exit;
