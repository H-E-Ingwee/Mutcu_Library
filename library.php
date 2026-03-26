<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
$books = getBooks();
$category = $_GET['category'] ?? 'All';
$q = trim($_GET['q'] ?? '');

if ($category !== 'All') {
    $books = array_filter($books, fn($b) => strtolower($b['category']) === strtolower($category));
}
if ($q !== '') {
    $books = array_filter($books, fn($b) => str_contains(strtolower($b['title']), strtolower($q)) || str_contains(strtolower($b['author']), strtolower($q)));
}
?>
<!DOCTYPE html>
<html lang="en"><head>
    <meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>MUTCU E-Library | Books</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head><body>
    <?php include __DIR__.'/partials/header.php'; ?>
    <main class="flex-grow-1 py-5">
        <div class="container">
            <div class="row mb-5 align-items-end">
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <h2 class="text-primary-brand fw-bold mb-2">E-Library Catalog</h2>
                    <div style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 2px; margin-bottom: 15px;"></div>
                    <p class="text-muted mb-0 fs-5">Browse or search for resources securely hosted on Google Drive.</p>
                </div>
                <div class="col-lg-6">
                    <form action="library.php" method="get" class="input-group shadow-sm rounded-pill overflow-hidden border bg-white">
                        <span class="input-group-text bg-white border-0 ps-4"><i class="bi bi-search text-muted"></i></span>
                        <input type="text" name="q" value="<?=htmlspecialchars($q)?>" class="form-control form-control-lg border-0 shadow-none" placeholder="Search books by title or author...">
                        <input type="hidden" name="category" value="<?=htmlspecialchars($category)?>" />
                        <button class="btn btn-accent" type="submit">Find</button>
                    </form>
                </div>
            </div>

            <div class="row mb-5">
                <div class="col-12 d-flex flex-wrap gap-2">
                    <?php foreach (['All','Faith','Leadership','Purpose','Relationships'] as $cat): ?>
                        <a href="library.php?category=<?=urlencode($cat)?>" class="filter-btn <?=($category=== $cat ? 'active':'')?>"><?=htmlspecialchars($cat)?> Books</a>
                    <?php endforeach; ?>
                </div>
            </div>
            <div id="grid-loader" class="loader-container d-none"><div class="spinner-border" role="status"><span class="visually-hidden">Loading...</span></div></div>
            <div class="row g-4" id="book-grid">
                <?php if (!$books): ?>
                    <div class="col-12 text-center py-5">
                        <i class="bi bi-journal-x text-muted opacity-50" style="font-size: 5rem;"></i>
                        <h3 class="fw-bold text-primary-brand">No books found</h3>
                        <p class="text-muted fs-5">Try adjusting your search keywords or filter criteria.</p>
                        <a href="library.php" class="btn btn-outline-primary rounded-pill mt-3 px-4">Clear Search</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($books as $book): ?>
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
                                    <div class="d-flex gap-2 mb-3">
                                        <form method="post" action="actions.php" class="flex-fill">
                                            <input type="hidden" name="action" value="toggle_bookmark">
                                            <input type="hidden" name="book_id" value="<?=$book['id']?>">
                                            <input type="hidden" name="return_url" value="library.php">
                                            <button type="submit" class="btn btn-outline-secondary w-100 rounded-pill fw-bold">
                                                <i class="bi bi-bookmark me-1"></i> Bookmark
                                            </button>
                                        </form>
                                    </div>
                                    <a href="download.php?id=<?=$book['id']?>" target="_blank" class="btn btn-outline-primary w-100 mt-auto rounded-pill fw-bold" style="border-color: var(--primary-color); color: var(--primary-color);">
                                        <i class="bi bi-cloud-arrow-down me-1"></i> Access Book
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </main>
    <?php include __DIR__.'/partials/footer.php'; ?>
    <script> const MUTCU = { user: <?=json_encode($currentUser)?>, books: <?=json_encode(getBooks())?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>