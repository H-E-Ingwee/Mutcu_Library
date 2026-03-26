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
addArticleView($id, $user['id'] ?? null);
header('Location: ' . $article['link']);
exit;
