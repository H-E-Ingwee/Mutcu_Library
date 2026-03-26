<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
$articles = getArticles();
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>MUTCU E-Library | Articles</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__.'/partials/header.php'; ?>
<main class="flex-grow-1 py-5">
    <div class="container">
        <div class="row mb-5 align-items-end">
            <div class="col-lg-6 mb-4 mb-lg-0">
                <h2 class="text-primary-brand fw-bold mb-2">Christian Articles</h2>
                <div style="height: 4px; width: 60px; background-color: var(--accent-color); border-radius: 2px; margin-bottom: 15px;"></div>
                <p class="text-muted mb-0 fs-5">Read inspiring thoughts and external articles from leading Christian authors.</p>
            </div>
        </div>
        <div class="row g-4" id="articles-grid">
            <?php foreach ($articles as $art): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card article-card p-4 border-0 shadow-sm h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="badge bg-light text-primary-brand border"><i class="bi bi-pen me-1"></i> Article</span>
                            <span class="read-time"><i class="bi bi-clock me-1"></i><?=htmlspecialchars($art['read_time'] ?? '5 min read')?></span>
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
</main>
<?php include __DIR__.'/partials/footer.php'; ?>
<script> const MUTCU = { user: <?=json_encode($currentUser)?>, articles: <?=json_encode($articles)?> };</script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body></html>