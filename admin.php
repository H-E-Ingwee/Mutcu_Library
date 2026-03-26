<?php
require_once __DIR__ . '/functions.php';
$currentUser = currentUser();
if (!$currentUser || !isAdmin()) {
    header('Location: login.php');
    exit;
}
$books = getBooks();
$articles = getArticles();
$stats = getStats();
$eventsStmt = $pdo->query('SELECT event_type, target_type, COUNT(*) as count FROM events GROUP BY event_type, target_type');
$events = $eventsStmt->fetchAll(PDO::FETCH_ASSOC);
$flash_success = $_SESSION['flash_success'] ?? null;
$flash_error = $_SESSION['flash_error'] ?? null;
unset($_SESSION['flash_success'], $_SESSION['flash_error']);
?>
<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>MUTCU E-Library | Admin</title><link rel="preconnect" href="https://fonts.googleapis.com"><link rel="preconnect" href="https://fonts.gstatic.com" crossorigin><link href="https://fonts.googleapis.com/css2?family=Lato:wght@400;700&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet"><link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet"><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css"><link rel="stylesheet" href="assets/css/style.css"></head><body>
<?php include __DIR__.'/partials/header.php'; ?>
<main class="flex-grow-1 py-5">
    <div class="container">        <?php if ($flash_success): ?><div class="alert alert-success alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($flash_success); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
        <?php if ($flash_error): ?><div class="alert alert-danger alert-dismissible fade show" role="alert"><?php echo htmlspecialchars($flash_error); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 pb-3 border-bottom">
            <div>
                <h2 class="text-primary-brand fw-bold mb-1"><i class="bi bi-speedometer2 me-2"></i>Admin Dashboard</h2>
                <p class="text-muted mb-0">Manage library inventory, articles, and analyze system metrics.</p>
            </div>
        </div>
        <div class="row g-4 mb-4">
            <div class="col-md-3">
                <div class="card admin-stat-card bg-primary-brand text-white shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="text-white-50 text-uppercase fw-bold mb-0">Total Books</h6><i class="bi bi-journals fs-4 text-white-50"></i></div>
                    <h2 class="fw-bold mb-0"><?= $stats['total_books'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-stat-card bg-white shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="text-muted text-uppercase fw-bold mb-0">Total Articles</h6><i class="bi bi-file-text fs-4 text-muted"></i></div>
                    <h2 class="fw-bold text-dark mb-0"><?= $stats['total_articles'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-stat-card shadow-sm h-100 p-3" style="background-color: var(--highlight-color); color: white;">
                    <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="text-white-50 text-uppercase fw-bold mb-0">Total Users</h6><i class="bi bi-people fs-4 text-white-50"></i></div>
                    <h2 class="fw-bold mb-0"><?= $stats['total_users'] ?></h2>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card admin-stat-card bg-white shadow-sm h-100 p-3">
                    <div class="d-flex justify-content-between align-items-center mb-2"><h6 class="text-muted text-uppercase fw-bold mb-0">Downloads</h6><i class="bi bi-cloud-arrow-down fs-4 text-success"></i></div>
                    <h2 class="fw-bold text-dark mb-0"><?= $stats['total_downloads'] ?: 0 ?></h2>
                </div>
            </div>
        </div>

        <ul class="nav nav-tabs mb-4" id="adminTabs" role="tablist">
            <li class="nav-item"><button class="nav-link active" id="analytics-tab" data-bs-toggle="tab" data-bs-target="#analytics" type="button" role="tab">Analytics</button></li>
            <li class="nav-item"><button class="nav-link" id="books-tab" data-bs-toggle="tab" data-bs-target="#manage-books" type="button" role="tab">Manage Books</button></li>
            <li class="nav-item"><button class="nav-link" id="articles-tab" data-bs-toggle="tab" data-bs-target="#manage-articles" type="button" role="tab">Manage Articles</button></li>
        </ul>

        <div class="tab-content" id="adminTabsContent">
            <div class="tab-pane fade show active" id="analytics" role="tabpanel">
                <div class="row g-4">
                    <div class="col-md-8"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-header bg-white py-3 border-bottom"><h5 class="mb-0 fw-bold text-primary-brand">User Interactions Over Time</h5></div><div class="card-body"><div class="chart-container"><canvas id="interactionsChart"></canvas></div></div></div></div>
                    <div class="col-md-4"><div class="card shadow-sm border-0 rounded-4 h-100"><div class="card-header bg-white py-3 border-bottom"><h5 class="mb-0 fw-bold text-primary-brand">Activity Totals</h5></div><div class="card-body"><ul class="list-group">
                        <?php foreach ($events as $event): ?><li class="list-group-item d-flex justify-content-between align-items-center"><?=htmlspecialchars(ucfirst($event['event_type']) . ' '. ucfirst($event['target_type']))?><span class="badge bg-primary rounded-pill"><?= $event['count'] ?></span></li><?php endforeach; ?>
                    </ul></div></div></div>
                </div>
            </div>

            <div class="tab-pane fade" id="manage-books" role="tabpanel">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold text-primary-brand">Book Catalog</h5>
                        <button class="btn btn-sm btn-accent rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addBookModal"><i class="bi bi-plus-circle-fill me-1"></i> Add Book</button>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-custom align-middle mb-0">
                            <thead><tr><th>ID</th><th>Cover</th><th>Title & Author</th><th>Category</th><th>Link</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($books as $book): ?>
                                <tr>
                                    <td>#<?=$book['id']?></td>
                                    <td><img src="<?=htmlspecialchars($book['cover'])?>" style="width:40px;height:55px;object-fit:cover;border-radius:4px;"></td>
                                    <td><strong><?=htmlspecialchars($book['title'])?></strong><br><small class="text-muted"><?=htmlspecialchars($book['author'])?></small></td>
                                    <td><span class="badge bg-light text-dark border"><?=htmlspecialchars($book['category'])?></span></td>
                                    <td><a href="download.php?id=<?=$book['id']?>" target="_blank" class="btn btn-sm btn-light border rounded-pill px-3 text-primary">Drive</a></td>
                                    <td class="text-end"><form method="post" action="actions.php" class="d-inline"><input type="hidden" name="action" value="delete_book"><input type="hidden" name="book_id" value="<?=$book['id']?>"><input type="hidden" name="return_url" value="admin.php"><button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete"><i class="bi bi-trash"></i></button></form></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="tab-pane fade" id="manage-articles" role="tabpanel">
                <div class="card shadow-sm border-0 rounded-4 overflow-hidden">
                    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                        <h5 class="mb-0 fw-bold text-primary-brand">Articles Catalog</h5>
                        <button class="btn btn-sm btn-accent rounded-pill px-3 shadow-sm" data-bs-toggle="modal" data-bs-target="#addArticleModal"><i class="bi bi-plus-circle-fill me-1"></i> Add Article</button>
                    </div>
                    <div class="card-body p-0 table-responsive">
                        <table class="table table-hover table-custom align-middle mb-0">
                            <thead><tr><th>ID</th><th>Title & Author</th><th>Date</th><th>Link</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                                <?php foreach ($articles as $art): ?>
                                <tr>
                                    <td>#<?=$art['id']?></td>
                                    <td><strong><?=htmlspecialchars($art['title'])?></strong><br><small class="text-muted">By <?=htmlspecialchars($art['author'])?></small></td>
                                    <td><?=htmlspecialchars($art['date'])?></td>
                                    <td><a href="article.php?id=<?=$art['id']?>" target="_blank" class="text-primary">External Link</a></td>
                                    <td class="text-end"><form method="post" action="actions.php" class="d-inline"><input type="hidden" name="action" value="delete_article"><input type="hidden" name="article_id" value="<?=$art['id']?>"><input type="hidden" name="return_url" value="admin.php"><button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Delete"><i class="bi bi-trash"></i></button></form></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

    </div>
</main>

<!-- modals -->
<div class="modal fade" id="addBookModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><div class="modal-header bg-primary-brand text-white border-0 rounded-top-4 py-3"><h5 class="modal-title heading-font"><i class="bi bi-journal-plus me-2"></i>Add New Book</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="post" action="actions.php"><div class="modal-body p-4"><input type="hidden" name="action" value="add_book"><input type="hidden" name="return_url" value="admin.php">
    <div class="form-floating mb-3"><input type="text" class="form-control" name="title" required><label>Book Title</label></div>
    <div class="form-floating mb-3"><input type="text" class="form-control" name="author" required><label>Author</label></div>
    <div class="form-floating mb-3"><select class="form-select" name="category" required><option value="" disabled selected>Select a category...</option><option>Faith</option><option>Leadership</option><option>Purpose</option><option>Relationships</option></select><label>Category</label></div>
    <div class="form-floating mb-3"><input type="url" class="form-control" name="drive_link" placeholder="https://drive.google.com/..." required><label>Google Drive Link</label></div>
    <div class="form-floating mb-3"><textarea class="form-control" name="description" style="height:100px;" required></textarea><label>Description</label></div>
</div><div class="modal-footer border-0 pt-0 pb-4 px-4"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-accent rounded-pill px-4">Save to Database</button></div></form></div></div></div>

<div class="modal fade" id="addArticleModal" tabindex="-1"><div class="modal-dialog modal-dialog-centered"><div class="modal-content border-0 shadow-lg rounded-4"><div class="modal-header bg-primary-brand text-white border-0 rounded-top-4 py-3"><h5 class="modal-title heading-font"><i class="bi bi-file-earmark-plus me-2"></i>Add External Article</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><form method="post" action="actions.php"><div class="modal-body p-4"><input type="hidden" name="action" value="add_article"><input type="hidden" name="return_url" value="admin.php">
    <div class="form-floating mb-3"><input type="text" class="form-control" name="title" required><label>Article Title</label></div>
    <div class="form-floating mb-3"><input type="text" class="form-control" name="author" required><label>Author</label></div>
    <div class="form-floating mb-3"><input type="url" class="form-control" name="link" required><label>External URL Link</label></div>
    <div class="form-floating mb-3"><textarea class="form-control" name="abstract" style="height:100px;" required></textarea><label>Abstract</label></div>
    <div class="form-floating mb-3"><input type="text" class="form-control" name="date" value="<?=date('M d, Y')?>" required><label>Date Added</label></div>
    <div class="form-floating mb-3"><input type="text" class="form-control" name="read_time" value="5 min read" required><label>Read Time</label></div>
</div><div class="modal-footer border-0 pt-0 pb-4 px-4"><button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-accent rounded-pill px-4">Save Article</button></div></form></div></div></div>

<?php include __DIR__.'/partials/footer.php'; ?>
<script> const MUTCU = { user: <?=json_encode($currentUser)?>, books: <?=json_encode($books)?>, articles: <?=json_encode($articles)?>, stats: <?=json_encode($stats)?> };</script>
<script src="assets/js/app.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){ const ctx1 = document.getElementById('interactionsChart'), ctx2 = document.getElementById('categoryChart');
        const days = ['Mon','Tue','Wed','Thu','Fri','Sat','Sun'];
        new Chart(document.getElementById('interactionsChart'),{type:'line',data:{labels:days,datasets:[{label:'Visits',data:[120,190,150,220,180,290,350],borderColor:'#04003d',backgroundColor:'rgba(4,0,61,0.12)',fill:true,tension:0.4},{label:'Downloads',data:[40,60,45,80,55,110,140],borderColor:'#ff9700',backgroundColor:'transparent',borderDash:[5,5],tension:0.4}]},options:{responsive:true,maintainAspectRatio:false}});
        new Chart(document.getElementById('categoryChart'),{type:'doughnut',data:{labels:['Faith','Leadership','Purpose','Relationships'],datasets:[{data:[45,25,15,15],backgroundColor:['#dc3545','#ff9700','#17a2b8','#20c997'],borderWidth:0}]},options:{responsive:true,maintainAspectRatio:false,plugins:{legend:{position:'bottom'}}}});
    })();
</script>
</body></html>