<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
if (!$currentUser) {
    header('Location: login.php');
    exit;
}
$bookmarks = getUserBookmarks($currentUser['id']);
$history = getUserReadingHistory($currentUser['id']);
$stats = getStats();
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);

// Calculate reading progress
$downloadedBooks = count(array_unique(array_column($history, 'id'))); // Unique books from history
$goal = $currentUser['reading_goal'] ?? 0;
$progress = $goal > 0 ? min(100, ($downloadedBooks / $goal) * 100) : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MUTCU E-Library | My Library</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <main class="flex-grow-1 py-5">
        <div class="container">
            <?php if ($flash_success): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($flash_success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($flash_error): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($flash_error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>

            <div class="row mb-5 align-items-end">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="text-primary-brand fw-bold mb-2">My Library</h2>
                    <div style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 2px; margin-bottom: 15px;"></div>
                    <p class="text-muted mb-0 fs-5">Track your reading progress, manage bookmarks, and explore your history.</p>
                </div>
                <div class="col-lg-6">
                    <div class="card p-4 bg-light mb-4">
                        <h5 class="fw-bold text-primary-brand mb-3">Reading Goal Progress</h5>
                        <div class="mb-3">
                            <div class="d-flex justify-content-between mb-1">
                                <span>Books Downloaded: <?=$downloadedBooks?></span>
                                <span>Goal: <?=$goal ?: 'Not Set'?></span>
                            </div>
                            <div class="progress" style="height: 20px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?=$progress?>%;" aria-valuenow="<?=$progress?>" aria-valuemin="0" aria-valuemax="100"><?=$goal ? round($progress) . '%' : 'Set Goal'?></div>
                            </div>
                        </div>
                        <form method="post" action="actions.php" class="d-flex gap-2">
                            <input type="hidden" name="action" value="update_goal">
                            <input type="number" name="goal" class="form-control" placeholder="Set annual goal" value="<?=$goal?>" min="1" required>
                            <button type="submit" class="btn btn-accent">Update</button>
                        </form>
                    </div>
                    <div class="card p-4">
                        <h5 class="fw-bold text-primary-brand mb-3">Account Settings</h5>
                        <form method="post" action="actions.php">
                            <input type="hidden" name="action" value="update_profile">
                            <div class="mb-3">
                                <label for="name" class="form-label">Name</label>
                                <input type="text" class="form-control" id="name" name="name" value="<?=htmlspecialchars($currentUser['name'])?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" class="form-control" id="email" name="email" value="<?=htmlspecialchars($currentUser['email'])?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">New Password (leave blank to keep current)</label>
                                <input type="password" class="form-control" id="password" name="password" placeholder="Enter new password">
                            </div>
                            <button type="submit" class="btn btn-accent">Update Profile</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="mb-5">
                <h3 class="text-primary-brand fw-bold mb-4">Saved Books</h3>
                <?php if ($bookmarks): ?>
                    <div class="row g-4" id="bookmarks-grid">
                        <?php foreach ($bookmarks as $book): ?>
                            <div class="col-md-4 col-lg-3 col-sm-6">
                                <div class="card book-card">
                                    <div class="book-cover-container">
                                        <span class="category-badge"><?=htmlspecialchars($book['category'])?></span>
                                        <img src="<?=htmlspecialchars($book['cover'])?>" class="book-cover" alt="<?=htmlspecialchars($book['title'])?>">
                                    </div>
                                    <div class="card-body d-flex flex-column p-4">
                                        <h5 class="card-title fw-bold mb-1" style="font-family:var(--heading-font);color:var(--primary-color);"><?=htmlspecialchars($book['title'])?></h5>
                                        <p class="text-muted small mb-3 border-bottom pb-2">By <?=htmlspecialchars($book['author'])?></p>
                                        <p class="card-text small flex-grow-1 text-secondary mb-4"><?=htmlspecialchars($book['description'])?></p>
                                        <a href="download.php?id=<?=$book['id']?>" target="_blank" class="btn btn-outline-primary w-100 mt-auto rounded-pill fw-bold" style="border-color: var(--primary-color); color: var(--primary-color);">
                                            <i class="bi bi-cloud-arrow-down me-1"></i> Access Book
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-bookmark-x text-muted opacity-50" style="font-size: 5rem;"></i>
                        <h3 class="fw-bold text-primary-brand">No Saved Books Yet</h3>
                        <p class="text-muted fs-5">Start bookmarking books to build your personal library.</p>
                        <a href="library.php" class="btn btn-outline-primary rounded-pill mt-3 px-4">Browse Books</a>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-5">
                <h3 class="text-primary-brand fw-bold mb-4">Recently Accessed</h3>
                <?php if ($history): ?>
                    <div class="row g-4" id="history-grid">
                        <?php foreach ($history as $book): ?>
                            <div class="col-md-4 col-lg-3 col-sm-6">
                                <div class="card book-card">
                                    <div class="book-cover-container">
                                        <span class="category-badge"><?=htmlspecialchars($book['category'])?></span>
                                        <img src="<?=htmlspecialchars($book['cover'])?>" class="book-cover" alt="<?=htmlspecialchars($book['title'])?>">
                                    </div>
                                    <div class="card-body d-flex flex-column p-4">
                                        <h5 class="card-title fw-bold mb-1" style="font-family:var(--heading-font);color:var(--primary-color);"><?=htmlspecialchars($book['title'])?></h5>
                                        <p class="text-muted small mb-3 border-bottom pb-2">By <?=htmlspecialchars($book['author'])?></p>
                                        <p class="card-text small flex-grow-1 text-secondary mb-4"><?=htmlspecialchars($book['description'])?></p>
                                        <a href="download.php?id=<?=$book['id']?>" target="_blank" class="btn btn-outline-primary w-100 mt-auto rounded-pill fw-bold" style="border-color: var(--primary-color); color: var(--primary-color);">
                                            <i class="bi bi-cloud-arrow-down me-1"></i> Access Book
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-5">
                        <i class="bi bi-clock-history text-muted opacity-50" style="font-size: 5rem;"></i>
                        <h3 class="fw-bold text-primary-brand">No Recent Activity</h3>
                        <p class="text-muted fs-5">Your reading history will appear here once you start accessing books.</p>
                        <a href="library.php" class="btn btn-outline-primary rounded-pill mt-3 px-4">Browse Books</a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script> const MUTCU = { user: <?=json_encode($currentUser)?>, bookmarks: <?=json_encode($bookmarks)?>, history: <?=json_encode($history)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>