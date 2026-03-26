<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
$books = getBooks();
$articles = getArticles();
$stats = getStats();
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
        <section class="hero-section">
            <div class="container text-center text-white">
                <span class="badge bg-warning text-dark mb-4 px-4 py-2 rounded-pill shadow-sm fw-bold border border-white">
                    <i class="bi bi-stars"></i> Official Digital Platform Prototype
                </span>
                <h1 class="display-3 fw-bolder mb-3">Equipping Leaders,<br><span style="color: var(--accent-color);">Deepening Faith</span></h1>
                <p class="lead mb-5 mx-auto fs-5" style="max-width: 750px; color: #e9ecef;">
                    Access curated, Christ-centered educational resources, books, and insights from leading authors to foster your spiritual, academic, and leadership growth legally and securely.
                </p>
                <div class="hero-search mt-4">
                    <input type="text" id="hero-search-input" class="form-control form-control-lg" placeholder="Search by title, author, or keyword..." onkeypress="if(event.key === 'Enter') { performSearch('hero-search-input') }" />
                    <button class="btn btn-accent fs-5" onclick="performSearch('hero-search-input')"><i class="bi bi-search"></i></button>
                </div>
            </div>
        </section>

        <div class="container my-5 py-4 text-center">
            <div class="mb-5">
                <h2 class="text-primary-brand fw-bold mb-2">Explore by Category</h2>
                <div class="mx-auto" style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 2px;"></div>
            </div>
            <div class="row justify-content-center g-4">
                <?php $choices = ['Faith' => 'bi-heart text-danger','Leadership'=>'bi-person-up','Purpose'=>'bi-compass text-info','Relationships'=>'bi-people text-success']; ?>
                <?php foreach ($choices as $category => $iconClass): ?>
                    <div class="col-6 col-md-3">
                        <div class="card p-4 category-card" onclick="location.href='library.php?category=<?=urlencode($category)?>'">
                            <i class="bi <?=$iconClass?> fs-1 mb-3"></i>
                            <h5 class="heading-font mb-0"><?=$category?></h5>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>

        <div class="bg-white py-5">
            <div class="container">
                <div class="d-flex justify-content-between align-items-end mb-4">
                    <div>
                        <h3 class="text-primary-brand fw-bold mb-2">Featured Articles</h3>
                        <div style="height: 3px; width: 40px; background-color: var(--accent-color); border-radius: 2px;"></div>
                    </div>
                    <a class="btn btn-outline-primary rounded-pill" href="articles.php">View All Articles</a>
                </div>

                <div class="row g-4" id="home-articles-grid">
                    <?php foreach (array_slice($articles, 0, 3) as $art): ?>
                        <div class="col-md-4">
                            <div class="card article-card p-4 border-0 shadow-sm h-100">
                                <div class="d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge bg-light text-primary-brand border"><i class="bi bi-pen me-1"></i> Article</span>
                                    <span class="read-time"><i class="bi bi-clock me-1"></i><?=$art['read_time'] ?? '5 min'?></span>
                                </div>
                                <h4 class="fw-bold text-dark mb-2 heading-font"><?=htmlspecialchars($art['title'])?></h4>
                                <p class="text-muted small mb-3">By <strong><?=htmlspecialchars($art['author'])?></strong> • <?=htmlspecialchars($art['date'])?></p>
                                <p class="text-secondary flex-grow-1"><?=htmlspecialchars($art['abstract'])?></p>
                                <a href="article.php?id=<?=$art['id']?>" class="btn btn-accent rounded-pill mt-3 align-self-start px-4">Read Article <i class="bi bi-arrow-right ms-1"></i></a>
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
