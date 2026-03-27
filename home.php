<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
$books = getBooks();
$articles = getArticles();
$stats = getStats();
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>MUTCU E-Library | Home</title>
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
    <link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" />
    <link rel="stylesheet" href="assets/css/style.css" />
</head>
<body>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <main class="flex-grow-1">
        
        <!-- Modern Hero Section -->
        <section class="hero-section">
            <div class="container text-center text-white position-relative z-3">
                <span class="badge glass-pill px-4 py-2 rounded-pill shadow-sm fw-bold mb-4 border">
                    <span class="spinner-grow spinner-grow-sm text-accent me-2" role="status" style="width: 0.5rem; height: 0.5rem;"></span>
                    Official Digital Platform Prototype
                </span>
                <h1 class="display-3 fw-bolder mb-4 tracking-tight">Equipping Leaders,<br><span class="text-accent">Deepening Faith</span></h1>
                <p class="lead mb-5 mx-auto fs-5 text-light opacity-75" style="max-width: 700px;">
                    Access curated, Christ-centered educational resources, books, and insights from leading authors to foster your spiritual, academic, and leadership growth.
                </p>
                <div class="hero-search mt-4 shadow-lg rounded-pill">
                    <input type="text" id="hero-search-input" class="form-control" placeholder="Search by title, author, or keyword..." onkeypress="if(event.key === 'Enter') { performSearch('hero-search-input') }" />
                    <button class="btn btn-accent fs-5" onclick="performSearch('hero-search-input')"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </section>

        <!-- Categories Section -->
        <div class="container my-5 py-5 text-center">
            <div class="mb-5">
                <h2 class="text-primary-brand fw-bold mb-2">Explore by Category</h2>
                <div class="mx-auto" style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 4px;"></div>
            </div>
            <div class="row justify-content-center g-4">
                <?php $choices = [
                    'Faith' => ['icon' => 'bi-heart-fill', 'color' => 'text-danger', 'bg' => 'bg-danger'],
                    'Leadership' => ['icon' => 'bi-graph-up-arrow', 'color' => 'text-success', 'bg' => 'bg-success'],
                    'Purpose' => ['icon' => 'bi-compass-fill', 'color' => 'text-primary', 'bg' => 'bg-primary'],
                    'Relationships' => ['icon' => 'bi-people-fill', 'color' => 'text-info', 'bg' => 'bg-info']
                ]; ?>
                <?php foreach ($choices as $category => $data): ?>
                    <div class="col-6 col-md-3">
                        <div class="card p-4 category-card border-0" onclick="location.href='library.php?category=<?=urlencode($category)?>'">
                            <div class="d-inline-flex align-items-center justify-content-center rounded-circle <?= $data['bg'] ?> bg-opacity-10 mx-auto mb-3" style="width: 64px; height: 64px;">
                                <i class="bi <?= $data['icon'] ?> fs-2 <?= $data['color'] ?>"></i>
                            </div>
                            <h5 class="heading-font mb-0 text-dark"><?= $category ?></h5>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Featured Books Section -->
        <div class="bg-white py-5 border-top">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-end mb-5">
                    <div>
                        <h2 class="text-primary-brand fw-bold mb-2">Featured Books</h2>
                        <div style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 4px;"></div>
                    </div>
                    <a class="btn btn-outline-secondary rounded-pill fw-bold px-4 d-none d-sm-block" href="library.php">View All Catalog <i class="bi bi-arrow-right ms-1"></i></a>
                </div>

                <div class="row g-4" id="home-books-grid">
                    <?php $featuredBooks = array_slice($books, 0, 4); ?>
                    <?php foreach ($featuredBooks as $book): ?>
                        <div class="col-md-6 col-lg-3">
                            <div class="card book-card" onclick="openQuickView(this)" data-book-id="<?=$book['id']?>" data-title="<?=htmlspecialchars($book['title'])?>" data-author="<?=htmlspecialchars($book['author'])?>" data-description="<?=htmlspecialchars($book['description'])?>" data-cover="<?=htmlspecialchars($book['cover'])?>" data-category="<?=htmlspecialchars($book['category'])?>" data-drive-link="<?=htmlspecialchars($book['drive_link'])?>" style="cursor: pointer;">
                                <div class="book-cover-container">
                                    <span class="category-badge"><?=htmlspecialchars($book['category'])?></span>
                                    <img src="<?=htmlspecialchars($book['cover'])?>" class="book-cover" alt="<?=htmlspecialchars($book['title'])?>">
                                </div>
                                <div class="card-body d-flex flex-column p-4">
                                    <h5 class="card-title fw-bold mb-1 text-truncate" style="font-family:var(--heading-font);color:var(--primary-color);"><?=htmlspecialchars($book['title'])?></h5>
                                    <p class="text-muted small mb-3 border-bottom pb-3">By <?=htmlspecialchars($book['author'])?></p>
                                    <p class="card-text small flex-grow-1 text-secondary mb-4" style="display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;"><?=htmlspecialchars($book['description'])?></p>
                                    
                                    <div class="d-flex gap-2 mt-auto">
                                        <button onclick="event.stopPropagation(); toggleBookmark(<?=$book['id']?>, this)" class="btn btn-light border rounded-3 px-3">
                                            <i class="bi bi-bookmark text-secondary"></i>
                                        </button>
                                        <a href="download.php?id=<?=$book['id']?>" target="_blank" class="btn btn-primary bg-primary-brand border-0 flex-grow-1 rounded-3 fw-bold">
                                            <i class="bi bi-cloud-arrow-down me-1"></i> Access
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                <div class="text-center mt-4 d-block d-sm-none">
                    <a class="btn btn-accent w-100 rounded-pill fw-bold py-2" href="library.php">View All Catalog <i class="bi bi-arrow-right ms-1"></i></a>
                </div>
            </div>
        </div>

        <!-- Featured Articles -->
        <div class="py-5" style="background-color: #f8fafc;">
            <div class="container py-4">
                <div class="d-flex justify-content-between align-items-end mb-5">
                    <div>
                        <h2 class="text-primary-brand fw-bold mb-2">Latest Insights</h2>
                        <div style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 4px;"></div>
                    </div>
                </div>

                <div class="row g-4" id="home-articles-grid">
                    <?php foreach (array_slice($articles, 0, 3) as $art): ?>
                        <div class="col-md-4">
                            <div class="card article-card p-4 border-0 h-100 rounded-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-3 py-2 fw-bold"><i class="bi bi-pen me-1"></i> Article</span>
                                    <span class="text-muted small fw-bold"><i class="bi bi-clock me-1"></i><?=$art['read_time'] ?? '5 min'?></span>
                                </div>
                                <h4 class="fw-bold text-dark mb-3 heading-font" style="line-height: 1.4;"><?=htmlspecialchars($art['title'])?></h4>
                                <p class="text-muted small mb-3">By <strong class="text-dark"><?=htmlspecialchars($art['author'])?></strong> • <?=htmlspecialchars($art['date'])?></p>
                                <p class="text-secondary flex-grow-1 mb-4"><?=htmlspecialchars($art['abstract'])?></p>
                                <a href="article.php?id=<?=$art['id']?>" class="text-accent fw-bold text-decoration-none mt-auto">Read Full Article <i class="bi bi-arrow-right ms-1"></i></a>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script> const MUTCU = { user: <?=json_encode($currentUser)?>, books: <?=json_encode($books)?>, articles: <?=json_encode($articles)?>, stats: <?=json_encode($stats)?> };</script>
    <script src="assets/js/app.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>