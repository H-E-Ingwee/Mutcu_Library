<?php
session_start();
require_once __DIR__ . '/db.php';
require_once __DIR__ . '/functions.php';

$action = $_POST['action'] ?? $_GET['action'] ?? '';
$returnUrl = $_POST['return_url'] ?? 'home.php';
$currentUser = currentUser();

// ENFORCE CSRF FOR ALL POST REQUESTS
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verify_csrf();
}

// ONLY ALLOW SPECIFIC GET REQUESTS
if ($_SERVER['REQUEST_METHOD'] !== 'POST' && !in_array($action, ['fetch_books', 'toggle_bookmark', 'export_csv', 'fetch_related'])) {
    header('Location: index.php'); exit;
}

// --- FILE UPLOAD COMPRESSION ---
function handleFileUpload($fileArray) {
    if (isset($fileArray) && $fileArray['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/covers/';
        if (!is_dir($uploadDir)) mkdir($uploadDir, 0755, true);
        
        $tmpPath = $fileArray['tmp_name'];
        $mime = mime_content_type($tmpPath);
        $allowedMimes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        
        if (in_array($mime, $allowedMimes)) {
            switch ($mime) {
                case 'image/jpeg': $sourceImage = imagecreatefromjpeg($tmpPath); break;
                case 'image/png':  $sourceImage = imagecreatefrompng($tmpPath); break;
                case 'image/gif':  $sourceImage = imagecreatefromgif($tmpPath); break;
                case 'image/webp': $sourceImage = imagecreatefromwebp($tmpPath); break;
                default: return null;
            }
            if (!$sourceImage) return null;

            $width = imagesx($sourceImage);
            $height = imagesy($sourceImage);
            $maxWidth = 600;
            
            if ($width > $maxWidth) {
                $newWidth = $maxWidth;
                $newHeight = floor($height * ($maxWidth / $width));
                $finalImage = imagecreatetruecolor($newWidth, $newHeight);
                if ($mime == 'image/png' || $mime == 'image/webp') {
                    imagecolortransparent($finalImage, imagecolorallocatealpha($finalImage, 0, 0, 0, 127));
                    imagealphablending($finalImage, false);
                    imagesavealpha($finalImage, true);
                }
                imagecopyresampled($finalImage, $sourceImage, 0, 0, 0, 0, $newWidth, $newHeight, $width, $height);
                imagedestroy($sourceImage);
            } else {
                $finalImage = $sourceImage;
            }
            
            $filename = uniqid('cover_') . '.webp';
            $destination = $uploadDir . $filename;
            if (imagewebp($finalImage, $destination, 80)) {
                imagedestroy($finalImage);
                return 'uploads/covers/' . $filename;
            }
            imagedestroy($finalImage);
        }
    }
    return null;
}

// ==========================================
// AJAX ENDPOINTS
// ==========================================
if ($action === 'fetch_books') {
    header('Content-Type: application/json');
    try {
        $q = trim($_GET['q'] ?? '');
        $category = trim($_GET['category'] ?? 'All');
        $sort = trim($_GET['sort'] ?? 'newest');
        $page = max(1, (int)($_GET['page'] ?? 1));
        $limit = 12; 
        $offset = ($page - 1) * $limit;
        
        $sql = 'SELECT * FROM books WHERE 1=1';
        $params = [];
        if ($category !== 'All') { $sql .= ' AND category = ?'; $params[] = $category; }
        if ($q !== '') { $sql .= ' AND (title LIKE ? OR author LIKE ?)'; $params[] = "%$q%"; $params[] = "%$q%"; }
        
        $countSql = str_replace('SELECT *', 'SELECT COUNT(*)', $sql);
        $countStmt = $pdo->prepare($countSql);
        $countStmt->execute($params);
        $totalBooks = $countStmt->fetchColumn();
        
        if ($sort === 'popular') $sql .= ' ORDER BY download_count DESC, id DESC';
        elseif ($sort === 'az') $sql .= ' ORDER BY title ASC';
        else $sql .= ' ORDER BY id DESC';
        
        $sql .= ' LIMIT ' . (int)$limit . ' OFFSET ' . (int)$offset;
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        processBooks($books); 
        
        echo json_encode(['books' => $books, 'hasMore' => ($offset + $limit) < $totalBooks]);
    } catch(PDOException $e) { echo json_encode(['books' => [], 'hasMore' => false]); }
    exit;
}

if ($action === 'fetch_related') {
    header('Content-Type: application/json');
    try {
        $id = (int)$_GET['id'];
        $category = trim($_GET['category']);
        $stmt = $pdo->prepare('SELECT * FROM books WHERE category = ? AND id != ? ORDER BY RAND() LIMIT 3');
        $stmt->execute([$category, $id]);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        processBooks($books);
        echo json_encode($books);
    } catch(PDOException $e) { echo json_encode([]); }
    exit;
}

if ($action === 'toggle_bookmark') {
    header('Content-Type: application/json');
    if (!$currentUser) { echo json_encode(['status' => 'error', 'message' => 'Not logged in']); exit; }
    $book_id = (int)($_POST['book_id'] ?? 0);
    $user_id = $currentUser['id'];
    try {
        $stmt = $pdo->prepare('SELECT id FROM user_bookmarks WHERE user_id = ? AND book_id = ?');
        $stmt->execute([$user_id, $book_id]);
        if ($stmt->fetch()) {
            $pdo->prepare('DELETE FROM user_bookmarks WHERE user_id = ? AND book_id = ?')->execute([$user_id, $book_id]);
            echo json_encode(['status' => 'removed']);
        } else {
            $pdo->prepare('INSERT INTO user_bookmarks (user_id, book_id) VALUES (?, ?)')->execute([$user_id, $book_id]);
            echo json_encode(['status' => 'added']);
        }
    } catch (PDOException $e) { echo json_encode(['status' => 'error']); }
    exit;
}

if ($action === 'update_read_status' && $currentUser) {
    header('Content-Type: application/json');
    $pdo->prepare('UPDATE user_bookmarks SET status = ? WHERE user_id = ? AND book_id = ?')->execute([trim($_POST['status']), $currentUser['id'], (int)$_POST['book_id']]);
    echo json_encode(['success' => true]);
    exit;
}

// AI Auto-Abstract
if ($action === 'generate_abstract' && isAdmin()) {
    header('Content-Type: application/json');
    $link = trim($_POST['link'] ?? '');
    if (empty($link)) { echo json_encode(['error' => 'Please provide a valid link first.']); exit; }
    
    $system = "You are an expert academic summarizer for a University Christian Union library.";
    $prompt = "Please write a highly professional, concise 3-sentence abstract/summary for the article found at this link: " . $link . " \n\nIf you cannot access the link directly, generate a relevant summary based entirely on the URL keywords.";
    
    $abstract = callOpenAI($prompt, $system);
    echo json_encode(['abstract' => $abstract]);
    exit;
}

if ($action === 'export_csv' && isAdmin()) {
    $type = $_GET['type'] ?? 'books';
    $filename = "mutcu_{$type}_export_" . date('Y-m-d') . ".csv";
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '"');
    $output = fopen('php://output', 'w');
    
    if ($type === 'books') {
        fputcsv($output, ['ID', 'Title', 'Author', 'Category', 'Drive Link', 'Downloads']);
        $books = getBooks();
        foreach ($books as $b) { fputcsv($output, [$b['id'], $b['title'], $b['author'], $b['category'], $b['drive_link'], $b['download_count'] ?? 0]); }
    } elseif ($type === 'articles') {
        fputcsv($output, ['ID', 'Title', 'Author', 'Date', 'Read Time', 'Views']);
        $articles = getArticles();
        foreach ($articles as $a) { fputcsv($output, [$a['id'], $a['title'], $a['author'], $a['date'], $a['read_time'], $a['view_count'] ?? 0]); }
    } elseif ($type === 'users') {
        fputcsv($output, ['ID', 'Name', 'Email', 'Role', 'Reading Goal', 'Registered Date', 'Last Active']);
        $users = getUsers();
        foreach ($users as $u) { fputcsv($output, [$u['id'], $u['name'], $u['email'], $u['role'], $u['reading_goal'], $u['created_at'], $u['last_active'] ?? 'Never']); }
    }
    fclose($output); exit;
}

// ==========================================
// FORM ACTIONS
// ==========================================
if ($action === 'login') {
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?'); $stmt->execute([trim($_POST['email'])]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user && password_verify($_POST['password'], $user['password'])) {
        $_SESSION['user_id'] = $user['id']; $_SESSION['flash_success'] = "Welcome back!"; header("Location: $returnUrl");
    } else {
        $_SESSION['flash_error'] = "Invalid credentials."; header("Location: login.php");
    } exit;
}

if ($action === 'register') {
    $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ?'); $stmt->execute([trim($_POST['email'])]);
    if ($stmt->fetch()) { $_SESSION['flash_error'] = "Email exists."; header("Location: register.php"); exit; }
    $stmt = $pdo->prepare('INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, "member")');
    if ($stmt->execute([trim($_POST['name']), trim($_POST['email']), password_hash($_POST['password'], PASSWORD_DEFAULT)])) {
        $_SESSION['user_id'] = $pdo->lastInsertId(); $_SESSION['flash_success'] = "Registration successful!"; header("Location: home.php");
    } exit;
}

if ($action === 'logout') { session_destroy(); header("Location: login.php"); exit; }

// Admin CRUD
if ($action === 'add_book' && isAdmin()) {
    $coverPath = handleFileUpload($_FILES['cover_file'] ?? null) ?? '';
    $pdo->prepare('INSERT INTO books (title, author, category, description, cover, drive_link) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['description']), $coverPath, trim($_POST['drive_link'])]);
    $_SESSION['flash_success'] = "Book added."; header("Location: $returnUrl"); exit;
}

if ($action === 'edit_book' && isAdmin()) {
    $coverPath = handleFileUpload($_FILES['cover_file'] ?? null);
    if (!$coverPath) $coverPath = $_POST['existing_cover'] ?? '';
    $pdo->prepare('UPDATE books SET title=?, author=?, category=?, description=?, cover=?, drive_link=? WHERE id=?')
        ->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['category']), trim($_POST['description']), $coverPath, trim($_POST['drive_link']), (int)$_POST['book_id']]);
    $_SESSION['flash_success'] = "Book updated."; header("Location: $returnUrl"); exit;
}

if ($action === 'delete_book' && isAdmin()) {
    $pdo->prepare('DELETE FROM books WHERE id = ?')->execute([(int)$_POST['book_id']]);
    $_SESSION['flash_success'] = "Book deleted."; header("Location: $returnUrl"); exit;
}

if ($action === 'add_article' && isAdmin()) {
    $pdo->prepare('INSERT INTO articles (title, author, abstract, link, date, read_time) VALUES (?, ?, ?, ?, ?, ?)')
        ->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['abstract']), trim($_POST['link']), trim($_POST['date']), trim($_POST['read_time'])]);
    $_SESSION['flash_success'] = "Article published."; header("Location: $returnUrl"); exit;
}

if ($action === 'edit_article' && isAdmin()) {
    $pdo->prepare('UPDATE articles SET title=?, author=?, abstract=?, link=?, date=?, read_time=? WHERE id=?')
        ->execute([trim($_POST['title']), trim($_POST['author']), trim($_POST['abstract']), trim($_POST['link']), trim($_POST['date']), trim($_POST['read_time']), (int)$_POST['article_id']]);
    $_SESSION['flash_success'] = "Article updated."; header("Location: $returnUrl"); exit;
}

if ($action === 'delete_article' && isAdmin()) {
    $pdo->prepare('DELETE FROM articles WHERE id = ?')->execute([(int)$_POST['article_id']]);
    $_SESSION['flash_success'] = "Article deleted."; header("Location: $returnUrl"); exit;
}

if ($action === 'edit_user' && isAdmin()) {
    $id = (int)$_POST['user_id'];
    $role = trim($_POST['role'] ?? 'member');
    
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?'); $stmt->execute([$id]);
    $targetEmail = strtolower($stmt->fetchColumn());
    
    if (in_array($targetEmail, ['ingweplex@gmail.com', 'mutcusec@gmail.com']) && $role !== 'admin') {
        $_SESSION['flash_error'] = "Super Admins cannot be demoted."; header("Location: $returnUrl"); exit;
    }
    if ($id === $currentUser['id'] && $role !== 'admin') {
        $_SESSION['flash_error'] = "You cannot remove your own privileges."; header("Location: $returnUrl"); exit;
    }

    if (!empty($_POST['password'])) {
        $pdo->prepare('UPDATE users SET name=?, email=?, role=?, password=? WHERE id=?')->execute([trim($_POST['name']), trim($_POST['email']), $role, password_hash($_POST['password'], PASSWORD_DEFAULT), $id]);
    } else {
        $pdo->prepare('UPDATE users SET name=?, email=?, role=? WHERE id=?')->execute([trim($_POST['name']), trim($_POST['email']), $role, $id]);
    }
    $_SESSION['flash_success'] = "User updated."; header("Location: $returnUrl"); exit;
}

if ($action === 'delete_user' && isAdmin()) {
    $id = (int)$_POST['user_id'];
    $stmt = $pdo->prepare('SELECT email FROM users WHERE id = ?'); $stmt->execute([$id]);
    $targetEmail = strtolower($stmt->fetchColumn());
    
    if (in_array($targetEmail, ['ingweplex@gmail.com', 'mutcusec@gmail.com'])) {
        $_SESSION['flash_error'] = "Super Admins cannot be deleted.";
    } elseif ($id === $currentUser['id']) {
        $_SESSION['flash_error'] = "You cannot delete your own account.";
    } else {
        $pdo->prepare('DELETE FROM users WHERE id = ?')->execute([$id]);
        $_SESSION['flash_success'] = "User deleted.";
    }
    header("Location: $returnUrl"); exit;
}

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